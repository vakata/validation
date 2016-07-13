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
                        $newKey = array_merge(
                            array_slice($keyParts, 0, $index),
                            [$k],
                            array_slice($keyParts, $index + 1)
                        );
                        $errors = array_merge($errors, $this->validate(implode('.', $newKey), $validator, $data));
                    }
                }
                break;
            }
            $temp = is_array($temp) && isset($temp[$keyPart]) ? $temp[$keyPart] : null;
        }
        if (strpos($key, '*') === false) {
            if ($validator['optional'] && ($temp === null || $temp === '')) {
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
        $this->callback(function ($value, $data) {
            return $value !== null && $value !== '';
        }, $message);
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
     * @param  mixed  $min    the minimum that the value should be equal to or greater than
     * @param  string $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function min($min, $message = '')
    {
        return $this->callback(function ($value, $data) use ($min) {
            return $value >= $min;
        }, $message);
    }
    /**
     * Add a max integer validation
     * @method max
     * @param  mixed   $max     the minimum that the value should be equal to or less than
     * @param  string  $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function max($max, $message = '')
    {
        return $this->callback(function ($value, $data) use ($max) {
            return $value <= $max;
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
        return $this->callback(function ($value, $data) use ($min, $max) {
            return $value >= $min && $value <= $max;
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

    protected function parseDate($value, $format = null)
    {
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
        $debug = date_get_last_errors();
        if ($debug['warning_count'] !== 0 || $debug['error_count'] !== 0) {
            $value = false;
        }
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
    /**
     * Add an URL validation
     * @method url
     * @param  array|null $protocols array of allowed protocols (defaults to ['http','https'])
     * @param  string     $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function url($protocols = null, $message = '')
    {
        if (!is_array($protocols)) {
            $protocols = [ 'http', 'https' ];
        }
        return $this->callback(function ($value, $data) use ($protocols) {
            return filter_var($value, FILTER_VALIDATE_URL) !== false &&
                   in_array(parse_url($value, PHP_URL_SCHEME), $protocols);
        }, $message);
    }
    protected function luhn($value)
    {
        if (!ctype_digit($value)) {
            return false;
        }
        $value = array_reverse(str_split($value));
        foreach ($value as $k => $digit) {
            $value[$k] = $digit ? (($digit * ($k % 2 ? 2 : 1)) % 9 ?: 9) : 0;
        }
        return array_sum($value) % 10 === 0;
    }
    /**
     * Add a mod10 validation
     * @method mod10
     * @param  string     $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function mod10($message = '')
    {
        return $this->callback(function ($value, $data) {
            $value = preg_replace('(\D)', '', $value);
            return $this->luhn($value);
        }, $message);
    }
    /**
     * Add a imei validation
     * @method imei
     * @param  string     $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function imei($message = '')
    {
        return $this->callback(function ($value, $data) {
            $value = preg_replace('(\D)', '', $value);
            return $this->luhn($value);
        }, $message);
    }
    /**
     * Add credit card validation
     * @method creditcard
     * @param  array|null $types   optional array of allowed cards (visa, mastercard, americanexpress, dinersclub, discover, jcb)
     * @param  string     $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function creditcard(array $types = null, $message = '')
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
        }, $message);
    }
    /**
     * Add an IBAN validation
     * @method iban
     * @param  string     $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function iban($message = '')
    {
        return $this->callback(function ($value, $data) {
            $value = str_replace([' ','-'], '', strtolower($value));
            if (!preg_match('(^[a-z0-9]{5,}$)', $value)) {
                return false;
            }
            $chars = [
                'a'=>10,'b'=>11,'c'=>12,'d'=>13,'e'=>14,'f'=>15,'g'=>16,'h'=>17,'i'=>18,'j'=>19,'k'=>20,'l'=>21,'m'=>22,
                'n'=>23,'o'=>24,'p'=>25,'q'=>26,'r'=>27,'s'=>28,'t'=>29,'u'=>30,'v'=>31,'w'=>32,'x'=>33,'y'=>34,'z'=>35
            ];
            $iban = substr($value, 4) . substr($value, 0, 4);
            $iban = str_replace(array_keys($chars), array_values($chars), $iban);

            $mod = "";
            do {
                $new  = $mod . substr($iban, 0, 5);
                $iban = substr($iban, 5);
                $mod  = $new % 97;
            } while (strlen($iban));
            
            return $mod === 1;
        }, $message);
    }
    /**
     * Add an UUID validation
     * @method uuid
     * @param  string     $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function uuid($message = '')
    {
        return $this->regex('(^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$)i', $message);
    }
    /**
     * Add a MAC validation
     * @method mac
     * @param  string     $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function mac($message = '')
    {
        return $this->regex('(^(([0-9a-fA-F]{2}-){5}|([0-9a-fA-F]{2}:){5})[0-9a-fA-F]{2}$)', $message);
    }
    protected function egn($value)
    {
        if (!ctype_digit($value) || strlen($value) !== 10) {
            return false;
        }
        $year  = substr($value, 0, 2);
        $month = substr($value, 2, 2);
        $day   = substr($value, 4, 2);
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
            $value[$k] = $v * $weights[$k];
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
            $value[$k] = $v * $weights[$k];
        }
        return array_sum($value) % 10 === (int)$check;
    }
    /**
     * Add a Bulgarian EGN validation
     * @method bgEGN
     * @param  string     $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function bgEGN($message = '')
    {
        return $this->callback(function ($value, $data) {
            return $this->egn($value);
        }, $message);
    }
    /**
     * Add a Bulgarian LNC validation
     * @method bgEGN
     * @param  string     $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function bgLNC($message = '')
    {
        return $this->callback(function ($value, $data) {
            return $this->lnc($value);
        }, $message);
    }
    /**
     * Add a Bulgarian identification number validation (EGN or LNC)
     * @method bgIDN
     * @param  string     $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function bgIDN($message = '')
    {
        return $this->callback(function ($value, $data) {
            return $this->egn($value) || $this->lnc($value);
        }, $message);
    }
    /**
     * Add a Bulgarian male EGN validation
     * @method bgMaleEGN
     * @param  string     $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function bgMaleEGN($message = '')
    {
        return $this->callback(function ($value, $data) {
            return $this->egn($value) && substr($value, 8, 1) % 2 === 0;
        }, $message);
    }
    /**
     * Add a Bulgarian female EGN validation
     * @method bgFemaleEGN
     * @param  string     $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function bgFemaleEGN($message = '')
    {
        return $this->callback(function ($value, $data) {
            return $this->egn($value) && substr($value, 8, 1) % 2 === 1;
        }, $message);
    }
    /**
     * Add a Bulgarian BULSTAT validation
     * @method bgBulstat
     * @param  string     $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function bgBulstat($message = '')
    {
        return $this->callback(function ($value, $data) {
            $value = preg_replace('(^BG)', '', $value);
            if (!ctype_digit($value) || !in_array(strlen($value), [ 9, 13 ])) {
                return false;
            }
            $value = str_split($value);
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
        }, $message);
    }
    /**
     * Add a Bulgarian name validation
     * @method bgName
     * @param  string     $message an optional message to include in the report if the validation fails
     * @return self
     */
    public function bgName($message = '')
    {
        return $this->regex('(^([А-Я][a-я]*( |-| - ))+([А-Я][a-я]*)$)u', $message);
    }
}
