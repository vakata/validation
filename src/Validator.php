<?php

namespace vakata\validation;

use Closure;
use JsonSerializable;

/**
 * A validation class, supporting arrays and nested arrays of data.
 */
class Validator implements JsonSerializable
{
    protected string $key = '';
    protected bool $opt = false;
    protected Closure|Validator|null $cond = null;
    /** @var array<string,list<Rule>> */
    protected array $validations = [];
    /** @var list<string> $checked */
    protected array $checked = [];

    public function __clone()
    {
        if ($this->cond) {
            $this->cond = clone $this->cond;
        }
        foreach ($this->validations as $k => $rules) {
            foreach ($rules as $kk => $rule) {
                $this->validations[$k][$kk] = clone $rule;
            }
        }
    }
    public function add(string $key, string $validation, ...$params): self
    {
        $tmp1 = $this->key;
        $tmp2 = $this->opt;
        $this->key = $key;
        $this->opt = false;
        if (!method_exists($this, $validation)) {
            throw new \Exception('Invalid method');
        }
        if ($validation !== 'optional') {
            if ($validation === 'required') {
                array_unshift($params, $key);
            }
            $this->{$validation}(...$params);
        }
        $this->key = $tmp1;
        $this->opt = $tmp2;
        return $this;
    }
    public function addOptional(string $key, string $validation, ...$params): self
    {
        $tmp1 = $this->key;
        $tmp2 = $this->opt;
        $this->key = $key;
        $this->opt = true;
        if (!method_exists($this, $validation)) {
            throw new \Exception('Invalid method');
        }
        if ($validation !== 'optional' && $validation !== 'required') {
            $this->{$validation}(...$params);
        }
        $this->key = $tmp1;
        $this->opt = $tmp2;
        return $this;
    }
    public function addRule(Rule $rule): self
    {
        $key = $rule->getKey();
        if (!isset($this->validations[$key])) {
            $this->validations[$key] = [];
            // copy defaults to new item
            if ($key !== '' && isset($this->validations[''])) {
                foreach ($this->validations[''] as $r) {
                    $n = (clone $r)->setKey($key);
                    if ($r->hasValidator() || $rule->hasValidator()) {
                        $v = new Validator();
                        if ($r->hasValidator()) {
                            foreach ($r->getValidator()->rules() as $rr) {
                                $v->addRule(clone $rr);
                            }
                        }
                        if ($rule->hasValidator()) {
                            foreach ($rule->getValidator()->rules() as $rr) {
                                $v->addRule(clone $rr);
                            }
                        }
                        $n->setValidator($v);
                    } else {
                        $n->setValidator(null);
                    }
                    $this->validations[$key][] = $n;
                }
            }
        }
        $this->validations[$key][] = $rule;
        // copy new default to existing items
        if ($key === '') {
            foreach ($this->validations as $k => $v) {
                if ($k === '') {
                    continue;
                }
                $this->validations[$k][] = (clone $rule)->setKey($k);
            }
        }
        return $this;
    }

    protected function validate($key, $validator, $data, $context): array
    {
        $errors = [];
        $keyParts = explode('.', $key);
        $temp = $data;
        foreach ($keyParts as $index => $keyPart) {
            if ($keyPart === '*') {
                if (!$validator->isOptional()) {
                    if (!is_array($temp)) {
                        $temp = [];
                    }
                    if (!count($temp)) {
                        $temp[] = null;
                    }
                }
                if (!$validator->isOptional() || (is_array($temp) && count($temp))) {
                    foreach ($temp as $k => $v) {
                        $newKey = array_merge(
                            array_slice($keyParts, 0, $index),
                            [$k],
                            array_slice($keyParts, $index + 1)
                        );
                        $errors = array_merge(
                            $errors,
                            $this->validate(
                                implode('.', $newKey),
                                $validator,
                                $data,
                                $context
                            )
                        );
                    }
                }
                break;
            }
            $temp = is_array($temp) && isset($temp[$keyPart]) ? $temp[$keyPart] : null;
        }
        if (strpos($key, '*') === false) {
            $this->checked[] = $key;
            if ($validator->isOptional() && ($temp === null || $temp === '')) {
                return [];
            }
            if (!$validator->execute($key, $temp, $data, $context)) {
                $errors[] = [
                    'key' => $key,
                    'message' => $validator->getMessage(),
                    'rule' => $validator->getRule(),
                    'data' => $validator->getData(),
                    'value' => $temp
                ];
            }
        }
        return $errors;
    }

    /**
     * Run the validator on the passed data
     * @param  array $data the data to validate
     * @param  mixed $context optional context
     * @return array              the errors encountered when validating or an empty array if successful
     */
    public function run(array $data, $context = null): array
    {
        $this->checked = [];
        $errors = [];
        foreach ($this->validations as $key => $validators) {
            if ($key === '') {
                continue;
            }
            foreach ($validators as $validator) {
                if ($validator->isEnabled()) {
                    $errors = array_merge($errors, $this->validate($key, $validator, $data, $context));
                }
            }
        }
        if (isset($this->validations['']) && count($this->validations[''])) {
            $this->checked = array_unique($this->checked);
            $flat = function (array $data, string $prefix = '') use (&$flat) {
                foreach ($data as $k => $v) {
                    if (!is_array($v)) {
                        yield $prefix . $k;
                    } else {
                        yield from $flat($v, $prefix . $k . '.');
                    }
                }
            };
            foreach ($flat($data) as $key) {
                if (!in_array($key, $this->checked)) {
                    // apply default validations to not checked
                    foreach ($this->validations[''] as $rule) {
                        if ($rule->isEnabled()) {
                            $errors = array_merge($errors, $this->validate($key, $rule, $data, $context));
                        }
                    }
                }
            }
        }
        return $errors;
    }
    public function rules(?string $key = null): array
    {
        if ($key !== null) {
            return $this->validations[$key] ?? [];
        }
        $rules = [];
        foreach ($this->validations as $k => $v) {
            $rules = array_merge($rules, $v);
        }
        return $rules;
    }

    public function condition(Closure|Validator|null $cond = null): self
    {
        $this->cond = is_callable($cond) ? Closure::fromCallable($cond) : $cond;
        return $this;
    }

    public function key(string $key): self
    {
        $this->key = $key;
        return $this;
    }
    public function default(): self
    {
        return $this->key('');
    }
    public function remove(?string $key = null, ?string $rule = null): self
    {
        if (!isset($key)) {
            $key = $this->key;
        }
        if (isset($this->validations[$key])) {
            if ($rule === null) {
                unset($this->validations[$key]);
            } else {
                foreach ($this->validations[$key] as $k => $validation) {
                    if ($validation->getRule() === $rule) {
                        unset($this->validations[$key][$k]);
                    }
                }
            }
        }
        return $this;
    }
    /**
     * Add a required key to validate.
     * @param  ?string   $key     the key name
     * @param  string   $message optional message to error with if the key is not present when running the validator
     * @return self
     */
    public function required(?string $key = null, string $message = ''): self
    {
        if (!isset($key)) {
            $key = $this->key;
        }
        $this->key = $key;
        $this->opt = false;
        $this->callback(function ($value, $data) {
            return $value !== null && $value !== '';
        }, $message, 'required');
        return $this;
    }
    /**
     * Add an optional key to validate - the validations that follow will only run if the key is present.
     * @param  ?string   $key the key name to look for
     * @return self
     */
    public function optional(?string $key = null): self
    {
        if (!isset($key)) {
            $key = $this->key;
        }
        $this->key = $key;
        $this->opt = true;
        return $this;
    }
    /**
     * Add a validation rule in the form of a callable, it will receive the current key's value and the whole data.
     * @param  callable $handler   the callable should return `true` if validation is OK and `false` otherwise
     * @param  string   $message   optional message to include in the report if the validation fails
     * @param  string   $rule      optional the rule name (defaults to callback)
     * @param  array    $data      optional the rule params (defaults to an empy array)
     * @return self
     */
    public function callback(callable $handler, $message = '', $rule = 'callback', array $data = []): self
    {
        return $this->addRule(new Rule(
            $this->key,
            $handler,
            $message,
            $rule,
            $data,
            $this->opt,
            is_callable($this->cond) ? $this->cond : null,
            ($this->cond instanceof Validator) ? $this->cond : null
        ));
    }
    /**
     * Add a validation using a regular expression
     * @param  string $regex   the regex to validate against
     * @param  string $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function regex($regex, $message = '', $name = 'regex', array $data = []): self
    {
        if ($name === 'regex') {
            $data = [$regex];
        }
        return $this->callback(function ($value, $data) use ($regex) {
            return preg_match($regex, $value);
        }, $message, $name, $data);
    }
    /**
     * Add a validation using a negative regular expression
     * @param  string $regex   the regex to validate against
     * @param  string $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function notRegex($regex, $message = '', $name = 'notRegex', array $data = []): self
    {
        if ($name === 'notRegex') {
            $data = [$regex];
        }
        return $this->callback(function ($value, $data) use ($regex) {
            return !preg_match($regex, $value);
        }, $message, $name, $data);
    }
    public function notLatin($message = ''): self
    {
        return $this->notRegex('([a-z]+)i', $message);
    }
    public function notNumeric($message = ''): self
    {
        return $this->notRegex('(\d+)i', $message);
    }
    public function notChars($chars, $message = ''): self
    {
        return $this->notRegex('(['.preg_quote($chars).']+)', $message);
    }
    public function notInArray(array $target, $message = ''): self
    {
        return $this->callback(function ($value, $data) use ($target) {
            return !in_array($value, $target);
        }, $message, 'notInArray', [$target]);
    }
    /**
     * Add a numeric validation
     * @param  string  $message optional message to include in the report if the validation fails
     * @return self
     */
    public function numeric($message = ''): self
    {
        return $this->callback(function ($value, $data) {
            return is_numeric($value);
        }, $message, 'numeric');
    }
    /**
     * Add an allowed chars validation
     * @param  ?string  $chars string of allowed chars
     * @param  string  $message optional message to include in the report if the validation fails
     * @return self
     */
    public function chars(?string $chars = null, string $message = ''): self
    {
        if ($chars === null) {
            $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }
        return $this->regex('(^['.preg_quote($chars).']*$)', $message, 'chars', [$chars]);
    }
    /**
     * Add a latin chars validation
     * @param  bool    $allowWhitespace should white space characters be allowed
     * @param  string  $message optional message to include in the report if the validation fails
     * @return self
     */
    public function latin($allowWhitespace = true, $message = ''): self
    {
        return $this->regex(
            $allowWhitespace ? '(^[a-z\s]*$)i' : '(^[a-z]*$)i',
            $message,
            'latin',
            [$allowWhitespace]
        );
    }
    /**
     * Add an alphabetical chars validation
     * @param  bool    $allowWhitespace should white space characters be allowed
     * @param  string  $message optional message to include in the report if the validation fails
     * @return self
     */
    public function alpha($allowWhitespace = true, $message = ''): self
    {
        return $this->regex(
            $allowWhitespace ? '(^[\p{L}\s]*$)ui' : '(^[\p{L}]*$)ui',
            $message,
            'alpha',
            [$allowWhitespace]
        );
    }
    /**
     * Add an uppercase alphabetical chars validation
     * @param  bool    $allowWhitespace should white space characters be allowed
     * @param  string  $message optional message to include in the report if the validation fails
     * @return self
     */
    public function upper($allowWhitespace = true, $message = ''): self
    {
        return $this->regex(
            $allowWhitespace ? '(^[\p{Lu}\s]*$)ui' : '(^[\p{Lu}]*$)ui',
            $message,
            'upper',
            [$allowWhitespace]
        );
    }
    /**
     * Add a lowercase alphabetical chars validation
     * @param  bool    $allowWhitespace should white space characters be allowed
     * @param  string  $message optional message to include in the report if the validation fails
     * @return self
     */
    public function lower($allowWhitespace = true, $message = ''): self
    {
        return $this->regex(
            $allowWhitespace ? '(^[\p{Ll}\s]*$)ui' : '(^[\p{Ll}]*$)ui',
            $message,
            'lower',
            [$allowWhitespace]
        );
    }
    /**
     * Add a alphanumeric validation
     * @param  bool    $allowWhitespace should white space characters be allowed
     * @param  string  $message optional message to include in the report if the validation fails
     * @return self
     */
    public function alphanumeric($allowWhitespace = true, $message = ''): self
    {
        return $this->regex(
            $allowWhitespace ? '(^[\p{L}0-9\s]*$)ui' : '(^[\p{L}0-9]*$)ui',
            $message,
            'alphanumeric',
            [$allowWhitespace]
        );
    }
    public function empty(): self
    {
        return $this->equals('');
    }
    /**
     * Add a not empty validation (fails on empty string)
     * @param  string  $message optional message to include in the report if the validation fails
     * @return self
     */
    public function notEmpty($message = ''): self
    {
        return $this->callback(function ($value, $data) {
            return is_string($value) ? strlen($value) > 0 : !!$value;
        }, $message, 'notEmpty');
    }
    /**
     * Add a mail validation
     * @param  string $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function mail($message = ''): self
    {
        return $this->callback(function ($value, $data) {
            return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
        }, $message, 'mail');
    }
    /**
     * Add a float validation
     * @param  string $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function float($message = ''): self
    {
        return $this->callback(function ($value, $data) {
            return filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
        }, $message, 'float');
    }
    /**
     * Add an integer validation
     * @param  string $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function int($message = ''): self
    {
        return $this->callback(function ($value, $data) {
            return filter_var($value, FILTER_VALIDATE_INT) !== false;
        }, $message, 'int');
    }
    /**
     * Add a min integer validation
     * @param  mixed  $min    the minimum that the value should be equal to or greater than
     * @param  string $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function min($min, $message = ''): self
    {
        return $this->callback(function ($value, $data) use ($min) {
            return $value >= $min;
        }, $message, 'min', [$min]);
    }
    /**
     * Add a max integer validation
     * @param  mixed   $max     the minimum that the value should be equal to or less than
     * @param  string  $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function max($max, $message = ''): self
    {
        return $this->callback(function ($value, $data) use ($max) {
            return $value <= $max;
        }, $message, 'max', [$max]);
    }
    /**
     * Add a range integer validation
     * @param  integer $min     the minimum that the value should be equal to or greater than
     * @param  integer $max     the maximum that the value should be equal to or less than
     * @param  string  $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function between($min, $max, $message = ''): self
    {
        return $this->callback(function ($value, $data) use ($min, $max) {
            return $value >= $min && $value <= $max;
        }, $message, 'between', [$min, $max]);
    }
    /**
     * Add an equals validation
     * @param  scalar $target  the value that the input should be equal to
     * @param  string  $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function equals($target, $message = ''): self
    {
        return $this->callback(function ($value, $data) use ($target) {
            return $value == $target;
        }, $message, 'equals', [$target]);
    }
    /**
     * Add an exact length validation
     * @param  integer $length  the desired input length
     * @param  string  $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function length($length, $message = ''): self
    {
        return $this->callback(function ($value, $data) use ($length) {
            return mb_strlen((string)$value, 'utf-8') == $length;
        }, $message, 'length', [$length]);
    }
    /**
     * Add a minimum length validation
     * @param  integer $length  the minimum desired input length
     * @param  string  $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function minLength($length, $message = ''): self
    {
        return $this->callback(function ($value, $data) use ($length) {
            return mb_strlen((string)$value, 'utf-8') >= $length;
        }, $message, 'minLength', [$length]);
    }
    /**
     * Add a maximum length validation
     * @param  integer $length  the maximum desired input length
     * @param  string  $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function maxLength($length, $message = ''): self
    {
        return $this->callback(function ($value, $data) use ($length) {
            return mb_strlen((string)$value, 'utf-8') <= $length;
        }, $message, 'maxLength', [$length]);
    }
    /**
     * Add an in array validation
     * @param  array   $target  array of allowed values
     * @param  string  $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function inArray(array $target, $message = ''): self
    {
        return $this->callback(function ($value, $data) use ($target) {
            return in_array($value, $target);
        }, $message, 'inArray', [$target]);
    }

    protected function parseDate($value, $format = null)
    {
        if ($value instanceof \DateTime) {
            return $value->getTimestamp();
        }
        if ($format === null) {
            return is_string($value) ? strtotime($value) : (is_int($value) ? $value : false);
        }
        $formats = [
            'c' => 'Y-m-d\TH:i:sP',
            'r' => 'D, d M Y H:i:s O',
        ];
        if (isset($formats[$format])) {
            $format = $formats[$format];
        }
        $value = date_create_from_format($format, (string)$value);
        $debug = date_get_last_errors();
        if ($value === false || ($debug !== false && ($debug['warning_count'] !== 0 || $debug['error_count'] !== 0))) {
            return false;
        }
        return $value->getTimestamp();
    }
    /**
     * Add a date validation
     * @param string|null $format the optional format to conform to (otherwise any strtotime compatible input is valid)
     * @param string  $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function date($format = null, $message = ''): self
    {
        return $this->callback(function ($value, $data) use ($format) {
            return $this->parseDate($value, $format) !== false;
        }, $message, 'date', [$format]);
    }
    /**
     * Add a min date validation
     * @param  string|\DateTime|int $min    the minimum that the value should be equal to or greater than
     * @param  string              $format the optional date format to conform to
     * @param  string              $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function minDate($min, $format = null, $message = ''): self
    {
        $min = $this->parseDate($min, $format);
        return $this->callback(function ($value, $data) use ($min, $format) {
            $value = $this->parseDate($value, $format);
            return $min !== false && $value !== false && $value >= $min;
        }, $message, 'minDate', [$min, $format]);
    }
    /**
     * Add a max date validation
     * @param  string|\DateTime|int $max    the minimum that the value should be equal to or greater than
     * @param  string              $format the optional date format to conform to
     * @param  string              $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function maxDate($max, $format = null, $message = ''): self
    {
        $max = $this->parseDate($max, $format);
        return $this->callback(function ($value, $data) use ($max, $format) {
            $value = $this->parseDate($value, $format);
            return $max !== false && $value !== false && $value <= $max;
        }, $message, 'maxDate', [$max, $format]);
    }
    /**
     * Add a range date validation
     * @param  string|\DateTime|int $min     the minimum that the value should be equal to or greater than
     * @param  string|\DateTime|int $max     the minimum that the value should be equal to or less than
     * @param  string              $format the optional date format to conform to
     * @param  string              $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function betweenDate($min, $max, $format = null, $message = ''): self
    {
        $min = $this->parseDate($min, $format);
        $max = $this->parseDate($max, $format);
        return $this->callback(function ($value, $data) use ($min, $max, $format) {
            $value = $this->parseDate($value, $format);
            return $min !== false && $max !== false && $value !== false && $value >= $min && $value <= $max;
        }, $message, 'betweenDate', [$min, $max, $format]);
    }
    /**
     * Add an age validation (which could be relative to a given date)
     * @param  int                 $age     the minimum age on a date
     * @param  string|\DateTime|int $rel     the date to compare to (defaults to today)
     * @param  string              $format  the optional date format to conform to
     * @param  string              $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function age($age, $rel = null, $format = null, $message = ''): self
    {
        $rel = $rel ? $this->parseDate($rel, $format) : time();
        return $this->callback(function ($value, $data) use ($age, $rel, $format) {
            $value = $this->parseDate($value, $format);
            return $value !== false && $rel !== false && strtotime('+' . (int)$age . ' years', $value) <= $rel;
        }, $message, 'age', [$age, $rel, $format]);
    }
    /**
     * Add a JSON validation
     * @param  string  $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function json($message = ''): self
    {
        return $this->callback(function ($value, $data) {
            return json_decode($value, true) !== null;
        }, $message, 'json');
    }
    /**
     * Add an IP address validation
     * @param  string $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function ip($message = ''): self
    {
        return $this->callback(function ($value, $data) {
            return filter_var($value, FILTER_VALIDATE_IP) !== false;
        }, $message, 'ip');
    }
    /**
     * Add an URL validation
     * @param  array|null $protocols array of allowed protocols (defaults to ['http','https'])
     * @param  string     $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function url($protocols = null, $message = ''): self
    {
        if (!is_array($protocols)) {
            $protocols = [ 'http', 'https' ];
        }
        return $this->callback(function ($value, $data) use ($protocols) {
            return filter_var($value, FILTER_VALIDATE_URL) !== false &&
                   in_array(parse_url($value, PHP_URL_SCHEME), $protocols);
        }, $message, 'url', [$protocols]);
    }
    protected function luhn($value)
    {
        if (!ctype_digit($value)) {
            return false;
        }
        $value = array_reverse(str_split($value));
        foreach ($value as $k => $digit) {
            $digit = (int)$digit;
            $value[$k] = $digit ? (($digit * ($k % 2 ? 2 : 1)) % 9 ?: 9) : 0;
        }
        return array_sum($value) % 10 === 0;
    }
    /**
     * Add a mod10 validation
     * @param  string     $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function mod10($message = ''): self
    {
        return $this->callback(function ($value, $data) {
            $value = preg_replace('(\D)', '', $value);
            return $this->luhn($value);
        }, $message, 'mod10');
    }
    /**
     * Add a imei validation
     * @param  string     $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function imei($message = ''): self
    {
        return $this->callback(function ($value, $data) {
            $value = preg_replace('(\D)', '', $value);
            return $this->luhn($value);
        }, $message, 'imei');
    }
    /**
     * Add credit card validation
     * @param  array|null $types   optional array of allowed cards
     *                             (visa,mastercard,americanexpress,dinersclub,discover,jcb)
     * @param  string     $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function creditcard(?array $types = null, $message = ''): self
    {
        $cards = [
            'visa' => '(^4[0-9]{12}(?:[0-9]{3})?$)',
            'mastercard' => '(^5[1-5][0-9]{14}$)',
            'americanexpress' => '(^3[47][0-9]{13}$)',
            'dinersclub' => '(^3(?:0[0-5]|[68][0-9])[0-9]{11}$)',
            'discover' => '(^6(?:011|5[0-9]{2})[0-9]{12}$)',
            'jcb' => '(^(?:2131|1800|35\d{3})\d{11}$)'
        ];
        $allowed = $cards;
        if (is_array($types)) {
            $allowed = [];
            foreach ($types as $type) {
                if (!isset($cards[$type])) {
                    $allowed[$type] = $cards[$type];
                }
            }
        }
        return $this->callback(function ($value, $data) use ($allowed) {
            $value = preg_replace('(\D)', '', $value);
            if (!$this->luhn($value)) {
                return false;
            }
            foreach ($allowed as $card) {
                if (preg_match($card, $value)) {
                    return true;
                }
            }
            return false;
        }, $message, 'creditcard', [$types]);
    }
    /**
     * Add an IBAN validation
     * @param  string     $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function iban($message = ''): self
    {
        return $this->callback(function ($value, $data) {
            $value = str_replace([' ','-'], '', strtolower($value));
            if (!preg_match('(^[a-z0-9]{5,}$)', $value)) {
                return false;
            }
            $chars = [
                'a'=>'10','b'=>'11','c'=>'12','d'=>'13','e'=>'14','f'=>'15','g'=>'16','h'=>'17','i'=>'18','j'=>'19',
                'k'=>'20','l'=>'21','m'=>'22','n'=>'23','o'=>'24','p'=>'25','q'=>'26','r'=>'27','s'=>'28','t'=>'29',
                'u'=>'30','v'=>'31','w'=>'32','x'=>'33','y'=>'34','z'=>'35'
            ];
            $iban = substr($value, 4) . substr($value, 0, 4);
            $iban = str_replace(array_keys($chars), array_values($chars), $iban);

            $mod = "";
            do {
                $new  = $mod . substr($iban, 0, 5);
                $iban = substr($iban, 5);
                $mod  = (int)$new % 97;
            } while (strlen($iban));

            return $mod === 1;
        }, $message, 'iban');
    }
    /**
     * Add an UUID validation
     * @param  string     $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function uuid($message = ''): self
    {
        return $this->regex(
            '(^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$)i',
            $message,
            'uuid'
        );
    }
    /**
     * Add a MAC validation
     * @param  string     $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function mac($message = ''): self
    {
        return $this->regex('(^(([0-9a-fA-F]{2}-){5}|([0-9a-fA-F]{2}:){5})[0-9a-fA-F]{2}$)', $message, 'mac');
    }
    protected function egn($value)
    {
        if (!ctype_digit($value) || strlen($value) !== 10) {
            return false;
        }
        $year  = (int)substr($value, 0, 2);
        $month = (int)substr($value, 2, 2);
        $day   = (int)substr($value, 4, 2);
        if ($month > 40) {
            $month -= 40;
            $year  += 2000;
        } elseif ($month > 20) {
            $month -= 20;
            $year  += 1800;
        } else {
            $year  += 1900;
        }
        if (!checkdate($month, $day, $year)) {
            return false;
        }

        $value = str_split($value);
        $check = array_pop($value);
        $weights = [ 2, 4, 8, 5, 10, 9, 7, 3, 6 ];
        foreach ($value as $k => $v) {
            $value[$k] = (int)$v * $weights[$k];
        }
        return (array_sum($value) % 11) % 10 === (int)$check;
    }
    protected function lnc($value)
    {
        if (!ctype_digit($value) || strlen($value) !== 10) {
            return false;
        }
        $value = str_split($value);
        $check = array_pop($value);
        $weights = [ 21, 19, 17, 13, 11, 9, 7, 3, 1 ];
        foreach ($value as $k => $v) {
            $value[$k] = (int)$v * $weights[$k];
        }
        return array_sum($value) % 10 === (int)$check;
    }
    /**
     * Add a Bulgarian EGN validation
     * @param  string     $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function bgEGN($message = ''): self
    {
        return $this->callback(function ($value, $data) {
            return $this->egn($value);
        }, $message, 'bgEGN');
    }
    /**
     * Add a Bulgarian LNC validation
     * @param  string     $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function bgLNC($message = ''): self
    {
        return $this->callback(function ($value, $data) {
            return $this->lnc($value);
        }, $message, 'bgLNC');
    }
    /**
     * Add a Bulgarian identification number validation (EGN or LNC)
     * @param  string     $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function bgIDN($message = ''): self
    {
        return $this->callback(function ($value, $data) {
            return $this->egn($value) || $this->lnc($value);
        }, $message, 'bgIDN');
    }
    /**
     * Add a Bulgarian male EGN validation
     * @param  string     $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function bgMaleEGN($message = ''): self
    {
        return $this->callback(function ($value, $data) {
            return $this->egn($value) && (int)substr($value, 8, 1) % 2 === 0;
        }, $message, 'bgMaleEGN');
    }
    /**
     * Add a Bulgarian female EGN validation
     * @param  string     $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function bgFemaleEGN($message = ''): self
    {
        return $this->callback(function ($value, $data) {
            return $this->egn($value) && (int)substr($value, 8, 1) % 2 === 1;
        }, $message, 'bgFemaleEGN');
    }
    /**
     * Add a Bulgarian BULSTAT validation
     * @param  string     $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function bgBulstat($message = ''): self
    {
        return $this->callback(function ($value, $data) {
            $value = preg_replace('(^BG)', '', $value);
            if (!ctype_digit($value) || !in_array(strlen($value), [ 9, 13 ])) {
                return false;
            }
            $value = str_split($value);
            foreach ($value as $k => $v) {
                $value[$k] = (int)$v;
            }
            $sum = 0;
            for ($i = 0; $i < 8; $i++) {
                $sum += $value[$i] * ($i + 1);
            }
            $mod = $sum % 11;
            if ($mod === 10) {
                $sum = 0;
                for ($i = 0; $i < 8; $i++) {
                    $sum += $value[$i] * ($i + 3);
                }
                $mod = ($sum % 11) % 10;
            }
            if ((int)$value[8] !== $mod) {
                return false;
            }
            if (isset($value[9])) {
                $sum = $value[8] * 2 + $value[9] * 7 + $value[10] * 3 + $value[11] * 5;
                $mod = $sum % 11;
                if ($mod === 10) {
                    $sum = $value[8] * 4 + $value[9] * 9 + $value[10] * 5 + $value[11] * 7;
                    $mod = ($sum % 11) % 10;
                }
                if ((int)$value[12] !== $mod) {
                    return false;
                }
            }
            return true;
        }, $message, 'bgBulstat');
    }
    /**
     * Add a Bulgarian name validation
     * @param  string     $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function bgName($message = ''): self
    {
        return $this->regex('(^([А-Я][a-я]*( |-| - ))+([А-Я][a-я]*)$)u', $message, 'bgName');
    }
    /**
     * Add a Bulgarian IDCard validation
     * @param  string     $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function bgIDCard($message = ''): self
    {
        return $this->regex(
            '((^[A-Z]{2}\d{7}$)|(^\d{9}$))',
            $message,
            'bgIDCard'
        );
    }
    /**
     * Add a min validation related to another field in the validator (the current field should be greater or equal)
     * @param  mixed  $min    the name of the related field to use for the minimum value
     * @param  string $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function minRelation($min, $message = ''): self
    {
        return $this->callback(function ($value, $data) use ($min) {
            return isset($data[$min]) && $value >= $data[$min];
        }, $message, 'minRelation', [$min, null]);
    }
    /**
     * Add a max validation related to another field in the validator (the current field should be greater or equal)
     * @param  mixed  $max     the name of the related field to use for the minimum value
     * @param  string $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function maxRelation($max, $message = ''): self
    {
        return $this->callback(function ($value, $data) use ($max) {
            return isset($data[$max]) && $value <= $data[$max];
        }, $message, 'maxRelation', [$max, null]);
    }
    /**
     * Add a min validation related to another field in the validator (the current field should be greater or equal)
     * @param  mixed  $min    the name of the related field to use for the minimum value
     * @param  mixed  $format optional date format
     * @param  string $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function minDateRelation($min, $format = null, $message = ''): self
    {
        return $this->callback(function ($value, $data) use ($min, $format) {
            if (!isset($data[$min])) {
                return false;
            }
            return $this->parseDate($value, $format) >= $this->parseDate($data[$min], $format);
        }, $message, 'minDateRelation', [$min, $format, null]);
    }
    /**
     * Add a max validation related to another field in the validator (the current field should be greater or equal)
     * @param  mixed  $max     the name of the related field to use for the minimum value
     * @param  string $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function maxDateRelation($max, $format = null, $message = ''): self
    {
        return $this->callback(function ($value, $data) use ($max, $format) {
            if (!isset($data[$max])) {
                return false;
            }
            return $this->parseDate($value, $format) <= $this->parseDate($data[$max], $format);
        }, $message, 'maxDateRelation', [$max, $format, null]);
    }
    /**
     * Add an equals validation related to another field
     * @param  integer $target  the field whose value the current field should match
     * @param  string  $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function equalsRelation($target, $message = ''): self
    {
        return $this->callback(function ($value, $data) use ($target) {
            return isset($data[$target]) && $value == $data[$target];
        }, $message, 'equalsRelation', [$target, null]);
    }

    public function jsonSerialize(): mixed
    {
        return array_map(function ($v) {
            return array_map(function ($vv) {
                return [
                    'rule' => $vv->getRule(),
                    'data' => $vv->getData(),
                    'message' => $vv->getMessage(),
                    'when' => $vv->getValidator()
                ];
            }, $v);
        }, $this->validations);
    }
}
