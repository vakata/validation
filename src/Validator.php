<?php

namespace vakata\validation;

/**
 * A validation class, supporting arrays and nested arrays of data.
 */
class Validator
{
    protected $key = '';
    protected $opt = false;
    protected $validations = [];

    protected function validate($key, $validator, $data)
    {
        $errors = [];
        $keyParts = explode('.', $key);
        $temp = $data;
        foreach ($keyParts as $index => $keyPart) {
            if ($keyPart === '*') {
                if (!$validator['optional']) {
                    if (!is_array($temp)) {
                        $temp = [];
                    }
                    if (!count($temp)) {
                        $temp[] = null;
                    }
                }
                if (!$validator['optional'] || is_array($temp) && count($temp)) {
                    foreach ($temp as $k => $v) {
                        $newKey = array_merge(array_slice($keyParts, 0, $index), [$k], array_slice($keyParts, $index + 1));
                        $errors = array_merge($errors, $this->validate(implode('.', $newKey), $validator, $data));
                    }
                }
                break;
            }
            $temp = is_array($temp) && isset($temp[$keyPart]) ? $temp[$keyPart] : null;
        }
        if (strpos($key, '*') === false) {
            if ($validator['optional'] && $temp === null) {
                return [];
            }
            if (!call_user_func($validator['callable'], $temp, $data)) {
                $errors[] = [
                    'key' => $key,
                    'message' => $validator['message'],
                    'value' => $temp
                ];
            }
        }
        return $errors;
    }

    /**
     * Run the validator on the passed data
     * @method run
     * @param  array|string $data the data to validate
     * @return array              the errors encountered when validating or an empty array if successful
     */
    public function run($data)
    {
        $data = is_array($data) ? $data : [ '' => $data ];
        $errors = [];
        foreach ($this->validations as $key => $validators) {
            foreach ($validators as $validator) {
                $errors = array_merge($errors, $this->validate($key, $validator, $data));
            }
        }
        return $errors;
    }
    /**
     * Add a required key to validate.
     * @method required
     * @param  string   $key     the key name
     * @param  string   $message optional message to error with if the key is not present when running the validator
     * @return self
     */
    public function required($key, $message = '')
    {
        $this->key = $key;
        $this->opt = false;
        $this->callback(function ($value, $data) { return $value !== null; }, $message);
        return $this;
    }
    /**
     * Add an optional key to validate - the validations that follow will only run if the key is present.
     * @method optional
     * @param  string   $key the key name to look for
     * @return self
     */
    public function optional($key)
    {
        $this->key = $key;
        $this->opt = true;
        return $this;
    }
    /**
     * Add a validation rule in the form of a callable, it will receive the current key's value and the whole data.
     * @method callback
     * @param  callable $handler the callable should return `true` if validation is OK and `false` otherwise
     * @param  string   $message optional message to include in the report if the validation fails
     * @return self
     */
    public function callback(callable $handler, $message = '')
    {
        if (!isset($this->validations[$this->key])) {
            $this->validations[$this->key] = [];
        }
        $this->validations[$this->key][] = [
            'callable' => $handler,
            'message'  => $message,
            'optional' => $this->opt
        ];
        return $this;
    }
    /**
     * Add a validation using a regular expression
     * @method regex
     * @param  string $regex   the regex to validate against
     * @param  string $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function regex($regex, $message = '')
    {
        return $this->callback(function ($value, $data) use ($regex) {
            return preg_match($regex, $value);
        }, $message);
    }
    /**
     * Add a numeric validation
     * @method numeric
     * @param  string  $message optional message to include in the report if the validation fails
     * @return self
     */
    public function numeric($message = '')
    {
        return $this->callback(function ($value, $data) {
            return is_numeric($value);
        }, $message);
    }
    /**
     * Add an allowed chars validation
     * @method chars
     * @param  string  $chars string of allowed chars
     * @param  string  $message optional message to include in the report if the validation fails
     * @return self
     */
    public function chars($chars, $message = '')
    {
        if ($chars === null) {
            $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }
        return $this->regex('(^['.preg_quote($chars).']*$)', $message);
    }
    /**
     * Add a latin chars validation
     * @method latin
     * @param  bool    $allowWhitespace should white space characters be allowed
     * @param  string  $message optional message to include in the report if the validation fails
     * @return self
     */
    public function latin($allowWhitespace = true, $message = '')
    {
        return $this->regex(
            $allowWhitespace ? '(^[a-z\s]*$)i' : '(^[a-z]*$)i',
            $message
        );
    }
    /**
     * Add an alphabetical chars validation
     * @method alpha
     * @param  bool    $allowWhitespace should white space characters be allowed
     * @param  string  $message optional message to include in the report if the validation fails
     * @return self
     */
    public function alpha($allowWhitespace = true, $message = '')
    {
        return $this->regex(
            $allowWhitespace ? '(^[\p{L}\s]*$)ui' : '(^[\p{L}]*$)ui',
            $message
        );
    }
    /**
     * Add an uppercase alphabetical chars validation
     * @method upper
     * @param  bool    $allowWhitespace should white space characters be allowed
     * @param  string  $message optional message to include in the report if the validation fails
     * @return self
     */
    public function upper($allowWhitespace = true, $message = '')
    {
        return $this->regex(
            $allowWhitespace ? '(^[\p{Lu}\s]*$)ui' : '(^[\p{Lu}]*$)ui',
            $message
        );
    }
    /**
     * Add a lowercase alphabetical chars validation
     * @method upper
     * @param  bool    $allowWhitespace should white space characters be allowed
     * @param  string  $message optional message to include in the report if the validation fails
     * @return self
     */
    public function lower($allowWhitespace = true, $message = '')
    {
        return $this->regex(
            $allowWhitespace ? '(^[\p{Ll}\s]*$)ui' : '(^[\p{Ll}]*$)ui',
            $message
        );
    }
    /**
     * Add a alphanumeric validation
     * @method alpha
     * @param  bool    $allowWhitespace should white space characters be allowed
     * @param  string  $message optional message to include in the report if the validation fails
     * @return self
     */
    public function alphanumeric($allowWhitespace = true, $message = '')
    {
        return $this->regex(
            $allowWhitespace ? '(^[\p{L}0-9\s]*$)ui' : '(^[\p{L}0-9]*$)ui',
            $message
        );
    }
    /**
     * Add a not empty validation (fails on empty string)
     * @method notEmpty
     * @param  string  $message optional message to include in the report if the validation fails
     * @return self
     */
    public function notEmpty($message = '')
    {
        return $this->callback(function ($value, $data) {
            return is_string($value) ? strlen($value) > 0 : !!$value;
        }, $message);
    }
    /**
     * Add a mail validation
     * @method mail
     * @param  string $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function mail($message = '')
    {
        return $this->callback(function ($value, $data) {
            return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
        }, $message);
    }
    /**
     * Add a float validation
     * @method mail
     * @param  string $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function float($message = '')
    {
        return $this->callback(function ($value, $data) {
            return filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
        }, $message);
    }
    /**
     * Add an integer validation
     * @method int
     * @param  string $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function int($message = '')
    {
        return $this->callback(function ($value, $data) {
            return filter_var($value, FILTER_VALIDATE_INT) !== false;
        }, $message);
    }
    /**
     * Add a min integer validation
     * @method min
     * @param  integer $min    the minimum that the value should be equal to or greater than
     * @param  string $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function min($min, $message = '')
    {
        return $this->callback(function ($value, $data) {
            return filter_var($value, FILTER_VALIDATE_INT) !== false && (int)$value >= $min;
        }, $message);
    }
    /**
     * Add a max integer validation
     * @method max
     * @param  integer $max     the minimum that the value should be equal to or less than
     * @param  string  $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function max($max, $message = '')
    {
        return $this->callback(function ($value, $data) {
            return filter_var($value, FILTER_VALIDATE_INT) !== false && (int)$value <= $max;
        }, $message);
    }
    /**
     * Add a range integer validation
     * @method between
     * @param  integer $min     the minimum that the value should be equal to or greater than
     * @param  integer $max     the maximum that the value should be equal to or less than
     * @param  string  $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function between($min, $max, $message = '')
    {
        return $this->callback(function ($value, $data) {
            return filter_var($value, FILTER_VALIDATE_INT) !== false && (int)$value >= $min && (int)$value <= $max;
        }, $message);
    }
    /**
     * Add an equals validation
     * @method equals
     * @param  integer $target  the value that the input should be equal to
     * @param  string  $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function equals($target, $message = '')
    {
        return $this->callback(function ($value, $data) use ($target) {
            return $value == $target;
        }, $message);
    }
    /**
     * Add an exact length validation
     * @method length
     * @param  integer $length  the desired input length
     * @param  string  $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function length($length, $message = '')
    {
        return $this->callback(function ($value, $data) use ($length) {
            return mb_strlen((string)$value, 'utf-8') == $length;
        }, $message);
    }
    /**
     * Add a minimum length validation
     * @method minLength
     * @param  integer $length  the minimum desired input length
     * @param  string  $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function minLength($length, $message = '')
    {
        return $this->callback(function ($value, $data) use ($length) {
            return mb_strlen((string)$value, 'utf-8') >= $length;
        }, $message);
    }
    /**
     * Add a maximum length validation
     * @method maxLength
     * @param  integer $length  the maximum desired input length
     * @param  string  $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function maxLength($length, $message = '')
    {
        return $this->callback(function ($value, $data) use ($length) {
            return mb_strlen((string)$value, 'utf-8') <= $length;
        }, $message);
    }
    /**
     * Add an in array validation
     * @method inArray
     * @param  array   $target  array of allowed values
     * @param  string  $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function inArray(array $target, $message = '')
    {
        return $this->callback(function ($value, $data) use ($target) {
            return in_array($value, $target);
        }, $message);
    }

    protected function parseDate($value, $format = null) {
        if ($value instanceof \DateTime) {
            $value = $value->getTimestamp();
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
        $value = date_create_from_format($format, $value);
        return $value === false ? false : $value->getTimestamp();
    }
    /**
     * Add a date validation
     * @method date
     * @param  array   $format  the optional format to conform to (otherwise any strtotime compatible input is valid)
     * @param  string  $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function date($format = null, $message = '')
    {
        return $this->callback(function ($value, $data) use ($format) {
            return $this->parseDate($value, $format) !== false;
        }, $message);
    }
    /**
     * Add a min date validation
     * @method minDate
     * @param  string|DateTime|int $min    the minimum that the value should be equal to or greater than
     * @param  string              $format the optional date format to conform to
     * @param  string              $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function minDate($min, $format = null, $message = '')
    {
        $min = $this->parseDate($min, $format);
        return $this->callback(function ($value, $data) use ($min, $format) {
            $value = $this->parseDate($value, $format);
            return $min !== false && $value !== false && $value >= $min;
        }, $message);
    }
    /**
     * Add a max date validation
     * @method minDate
     * @param  string|DateTime|int $max    the minimum that the value should be equal to or greater than
     * @param  string              $format the optional date format to conform to
     * @param  string              $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function maxDate($max, $format = null, $message = '')
    {
        $max = $this->parseDate($max, $format);
        return $this->callback(function ($value, $data) use ($max, $format) {
            $value = $this->parseDate($value, $format);
            return $max !== false && $value !== false && $value <= $max;
        }, $message);
    }
    /**
     * Add a range date validation
     * @method between
     * @param  string|DateTime|int $min     the minimum that the value should be equal to or greater than
     * @param  string|DateTime|int $max     the minimum that the value should be equal to or less than
     * @param  string              $format the optional date format to conform to
     * @param  string              $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function betweenDate($min, $max, $format = null, $message = '')
    {
        $min = $this->parseDate($min, $format);
        $max = $this->parseDate($max, $format);
        return $this->callback(function ($value, $data) use ($min, $max, $format) {
            $value = $this->parseDate($value, $format);
            return $min !== false && $max !== false && $value !== false && $value >= $min && $value <= $max;
        }, $message);
    }
    /**
     * Add an age validation (which could be relative to a given date)
     * @method age
     * @param  int                 $age     the minimum age on a date
     * @param  string|DateTime|int $rel     the date to compare to (defaults to today)
     * @param  string              $format  the optional date format to conform to
     * @param  string              $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function age($age, $rel = null, $format = null, $message = '')
    {
        $rel = $rel ? $this->parseDate($rel, $format) : time();
        return $this->callback(function ($value, $data) use ($age, $rel, $format) {
            $value = $this->parseDate($value, $format);
            return $value !== false && $rel !== false && strtotime('+' . (int)$age . ' years', $value) <= $rel;
        }, $message);
    }
    /**
     * Add a JSON validation
     * @method between
     * @param  string  $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function json($message = '')
    {
        return $this->callback(function ($value, $data) {
            return json_decode($value, true) !== null;
        }, $message);
    }
    /**
     * Add an IP address validation
     * @method mail
     * @param  string $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function ip($message = '')
    {
        return $this->callback(function ($value, $data) {
            return filter_var($value, FILTER_VALIDATE_IP) !== false;
        }, $message);
    }
}
