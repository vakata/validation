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
     * Add a alphabetical validation
     * @method alpha
     * @param  string  $chars optional string of allowed chars, defaults to `null` meaning a-z
     * @param  string  $message optional message to include in the report if the validation fails
     * @return self
     */
    public function alpha($chars = null, $message = '')
    {
        if ($chars === null) {
            $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }
        return $this->regex('(^['.preg_quote($chars).']*$)', $message);
    }
    /**
     * Add a alphanumeric validation
     * @method alpha
     * @param  string  $message optional message to include in the report if the validation fails
     * @return self
     */
    public function alphanumeric($message = '')
    {
        return $this->alpha('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ123456789', $message);
    }
    /**
     * Add a not empty validation (fails on empty string)
     * @method notEmpty
     * @param  string  $message optional message to include in the report if the validation fails
     * @return self
     */
    public function notEmpty($message = '')
    {
        return $this->regex('(^.+$)', $message);
    }
    /**
     * Add a valid mail validation
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
}