# vakata\validation\Validator
A validation class, supporting arrays and nested arrays of data.

## Methods

| Name | Description |
|------|-------------|
|[run](#vakata\validation\validatorrun)|Run the validator on the passed data|
|[required](#vakata\validation\validatorrequired)|Add a required key to validate.|
|[optional](#vakata\validation\validatoroptional)|Add an optional key to validate - the validations that follow will only run if the key is present.|
|[callback](#vakata\validation\validatorcallback)|Add a validation rule in the form of a callable, it will receive the current key's value and the whole data.|
|[regex](#vakata\validation\validatorregex)|Add a validation using a regular expression|
|[numeric](#vakata\validation\validatornumeric)|Add a numeric validation|
|[chars](#vakata\validation\validatorchars)|Add an allowed chars validation|
|[latin](#vakata\validation\validatorlatin)|Add a latin chars validation|
|[alpha](#vakata\validation\validatoralpha)|Add an alphabetical chars validation|
|[upper](#vakata\validation\validatorupper)|Add an uppercase alphabetical chars validation|
|[lower](#vakata\validation\validatorlower)|Add a lowercase alphabetical chars validation|
|[alphanumeric](#vakata\validation\validatoralphanumeric)|Add a alphanumeric validation|
|[notEmpty](#vakata\validation\validatornotempty)|Add a not empty validation (fails on empty string)|
|[mail](#vakata\validation\validatormail)|Add a mail validation|
|[float](#vakata\validation\validatorfloat)|Add a float validation|
|[int](#vakata\validation\validatorint)|Add an integer validation|
|[min](#vakata\validation\validatormin)|Add a min integer validation|
|[max](#vakata\validation\validatormax)|Add a max integer validation|
|[between](#vakata\validation\validatorbetween)|Add a range integer validation|
|[equals](#vakata\validation\validatorequals)|Add an equals validation|
|[length](#vakata\validation\validatorlength)|Add an exact length validation|
|[minLength](#vakata\validation\validatorminlength)|Add a minimum length validation|
|[maxLength](#vakata\validation\validatormaxlength)|Add a maximum length validation|
|[inArray](#vakata\validation\validatorinarray)|Add an in array validation|
|[date](#vakata\validation\validatordate)|Add a date validation|
|[minDate](#vakata\validation\validatormindate)|Add a min date validation|
|[maxDate](#vakata\validation\validatormaxdate)|Add a max date validation|
|[betweenDate](#vakata\validation\validatorbetweendate)|Add a range date validation|
|[age](#vakata\validation\validatorage)|Add an age validation (which could be relative to a given date)|
|[json](#vakata\validation\validatorjson)|Add a JSON validation|
|[ip](#vakata\validation\validatorip)|Add an IP address validation|
|[url](#vakata\validation\validatorurl)|Add an URL validation|
|[mod10](#vakata\validation\validatormod10)|Add a mod10 validation|
|[imei](#vakata\validation\validatorimei)|Add a imei validation|
|[creditcard](#vakata\validation\validatorcreditcard)|Add credit card validation|
|[iban](#vakata\validation\validatoriban)|Add an IBAN validation|
|[uuid](#vakata\validation\validatoruuid)|Add an UUID validation|
|[mac](#vakata\validation\validatormac)|Add a MAC validation|
|[bgEGN](#vakata\validation\validatorbgegn)|Add a Bulgarian EGN validation|
|[bgLNC](#vakata\validation\validatorbglnc)|Add a Bulgarian LNC validation|
|[bgIDN](#vakata\validation\validatorbgidn)|Add a Bulgarian identification number validation (EGN or LNC)|
|[bgMaleEGN](#vakata\validation\validatorbgmaleegn)|Add a Bulgarian male EGN validation|
|[bgFemaleEGN](#vakata\validation\validatorbgfemaleegn)|Add a Bulgarian female EGN validation|
|[bgBulstat](#vakata\validation\validatorbgbulstat)|Add a Bulgarian BULSTAT validation|
|[bgName](#vakata\validation\validatorbgname)|Add a Bulgarian name validation|

---



### vakata\validation\Validator::run
Run the validator on the passed data  


```php
public function run (  
    array|string $data  
) : array    
```

|  | Type | Description |
|-----|-----|-----|
| `$data` | `array`, `string` | the data to validate |
|  |  |  |
| `return` | `array` | the errors encountered when validating or an empty array if successful |

---


### vakata\validation\Validator::required
Add a required key to validate.  


```php
public function required (  
    string $key,  
    string $message  
) : static    
```

|  | Type | Description |
|-----|-----|-----|
| `$key` | `string` | the key name |
| `$message` | `string` | optional message to error with if the key is not present when running the validator |
|  |  |  |
| `return` | `static` |  |

---


### vakata\validation\Validator::optional
Add an optional key to validate - the validations that follow will only run if the key is present.  


```php
public function optional (  
    string $key  
) : static    
```

|  | Type | Description |
|-----|-----|-----|
| `$key` | `string` | the key name to look for |
|  |  |  |
| `return` | `static` |  |

---


### vakata\validation\Validator::callback
Add a validation rule in the form of a callable, it will receive the current key's value and the whole data.  


```php
public function callback (  
    callable $handler,  
    string $message  
) : static    
```

|  | Type | Description |
|-----|-----|-----|
| `$handler` | `callable` | the callable should return `true` if validation is OK and `false` otherwise |
| `$message` | `string` | optional message to include in the report if the validation fails |
|  |  |  |
| `return` | `static` |  |

---


### vakata\validation\Validator::regex
Add a validation using a regular expression  


```php
public function regex (  
    string $regex,  
    string $message  
) : static    
```

|  | Type | Description |
|-----|-----|-----|
| `$regex` | `string` | the regex to validate against |
| `$message` | `string` | an optional message to include in the report if the validation fails |
|  |  |  |
| `return` | `static` |  |

---


### vakata\validation\Validator::numeric
Add a numeric validation  


```php
public function numeric (  
    string $message  
) : static    
```

|  | Type | Description |
|-----|-----|-----|
| `$message` | `string` | optional message to include in the report if the validation fails |
|  |  |  |
| `return` | `static` |  |

---


### vakata\validation\Validator::chars
Add an allowed chars validation  


```php
public function chars (  
    string $chars,  
    string $message  
) : static    
```

|  | Type | Description |
|-----|-----|-----|
| `$chars` | `string` | string of allowed chars |
| `$message` | `string` | optional message to include in the report if the validation fails |
|  |  |  |
| `return` | `static` |  |

---


### vakata\validation\Validator::latin
Add a latin chars validation  


```php
public function latin (  
    bool $allowWhitespace,  
    string $message  
) : static    
```

|  | Type | Description |
|-----|-----|-----|
| `$allowWhitespace` | `bool` | should white space characters be allowed |
| `$message` | `string` | optional message to include in the report if the validation fails |
|  |  |  |
| `return` | `static` |  |

---


### vakata\validation\Validator::alpha
Add an alphabetical chars validation  


```php
public function alpha (  
    bool $allowWhitespace,  
    string $message  
) : static    
```

|  | Type | Description |
|-----|-----|-----|
| `$allowWhitespace` | `bool` | should white space characters be allowed |
| `$message` | `string` | optional message to include in the report if the validation fails |
|  |  |  |
| `return` | `static` |  |

---


### vakata\validation\Validator::upper
Add an uppercase alphabetical chars validation  


```php
public function upper (  
    bool $allowWhitespace,  
    string $message  
) : static    
```

|  | Type | Description |
|-----|-----|-----|
| `$allowWhitespace` | `bool` | should white space characters be allowed |
| `$message` | `string` | optional message to include in the report if the validation fails |
|  |  |  |
| `return` | `static` |  |

---


### vakata\validation\Validator::lower
Add a lowercase alphabetical chars validation  


```php
public function lower (  
    bool $allowWhitespace,  
    string $message  
) : static    
```

|  | Type | Description |
|-----|-----|-----|
| `$allowWhitespace` | `bool` | should white space characters be allowed |
| `$message` | `string` | optional message to include in the report if the validation fails |
|  |  |  |
| `return` | `static` |  |

---


### vakata\validation\Validator::alphanumeric
Add a alphanumeric validation  


```php
public function alphanumeric (  
    bool $allowWhitespace,  
    string $message  
) : static    
```

|  | Type | Description |
|-----|-----|-----|
| `$allowWhitespace` | `bool` | should white space characters be allowed |
| `$message` | `string` | optional message to include in the report if the validation fails |
|  |  |  |
| `return` | `static` |  |

---


### vakata\validation\Validator::notEmpty
Add a not empty validation (fails on empty string)  


```php
public function notEmpty (  
    string $message  
) : static    
```

|  | Type | Description |
|-----|-----|-----|
| `$message` | `string` | optional message to include in the report if the validation fails |
|  |  |  |
| `return` | `static` |  |

---


### vakata\validation\Validator::mail
Add a mail validation  


```php
public function mail (  
    string $message  
) : static    
```

|  | Type | Description |
|-----|-----|-----|
| `$message` | `string` | an optional message to include in the report if the validation fails |
|  |  |  |
| `return` | `static` |  |

---


### vakata\validation\Validator::float
Add a float validation  


```php
public function float (  
    string $message  
) : static    
```

|  | Type | Description |
|-----|-----|-----|
| `$message` | `string` | an optional message to include in the report if the validation fails |
|  |  |  |
| `return` | `static` |  |

---


### vakata\validation\Validator::int
Add an integer validation  


```php
public function int (  
    string $message  
) : static    
```

|  | Type | Description |
|-----|-----|-----|
| `$message` | `string` | an optional message to include in the report if the validation fails |
|  |  |  |
| `return` | `static` |  |

---


### vakata\validation\Validator::min
Add a min integer validation  


```php
public function min (  
    mixed $min,  
    string $message  
) : static    
```

|  | Type | Description |
|-----|-----|-----|
| `$min` | `mixed` | the minimum that the value should be equal to or greater than |
| `$message` | `string` | an optional message to include in the report if the validation fails |
|  |  |  |
| `return` | `static` |  |

---


### vakata\validation\Validator::max
Add a max integer validation  


```php
public function max (  
    mixed $max,  
    string $message  
) : static    
```

|  | Type | Description |
|-----|-----|-----|
| `$max` | `mixed` | the minimum that the value should be equal to or less than |
| `$message` | `string` | an optional message to include in the report if the validation fails |
|  |  |  |
| `return` | `static` |  |

---


### vakata\validation\Validator::between
Add a range integer validation  


```php
public function between (  
    integer $min,  
    integer $max,  
    string $message  
) : static    
```

|  | Type | Description |
|-----|-----|-----|
| `$min` | `integer` | the minimum that the value should be equal to or greater than |
| `$max` | `integer` | the maximum that the value should be equal to or less than |
| `$message` | `string` | an optional message to include in the report if the validation fails |
|  |  |  |
| `return` | `static` |  |

---


### vakata\validation\Validator::equals
Add an equals validation  


```php
public function equals (  
    integer $target,  
    string $message  
) : static    
```

|  | Type | Description |
|-----|-----|-----|
| `$target` | `integer` | the value that the input should be equal to |
| `$message` | `string` | an optional message to include in the report if the validation fails |
|  |  |  |
| `return` | `static` |  |

---


### vakata\validation\Validator::length
Add an exact length validation  


```php
public function length (  
    integer $length,  
    string $message  
) : static    
```

|  | Type | Description |
|-----|-----|-----|
| `$length` | `integer` | the desired input length |
| `$message` | `string` | an optional message to include in the report if the validation fails |
|  |  |  |
| `return` | `static` |  |

---


### vakata\validation\Validator::minLength
Add a minimum length validation  


```php
public function minLength (  
    integer $length,  
    string $message  
) : static    
```

|  | Type | Description |
|-----|-----|-----|
| `$length` | `integer` | the minimum desired input length |
| `$message` | `string` | an optional message to include in the report if the validation fails |
|  |  |  |
| `return` | `static` |  |

---


### vakata\validation\Validator::maxLength
Add a maximum length validation  


```php
public function maxLength (  
    integer $length,  
    string $message  
) : static    
```

|  | Type | Description |
|-----|-----|-----|
| `$length` | `integer` | the maximum desired input length |
| `$message` | `string` | an optional message to include in the report if the validation fails |
|  |  |  |
| `return` | `static` |  |

---


### vakata\validation\Validator::inArray
Add an in array validation  


```php
public function inArray (  
    array $target,  
    string $message  
) : static    
```

|  | Type | Description |
|-----|-----|-----|
| `$target` | `array` | array of allowed values |
| `$message` | `string` | an optional message to include in the report if the validation fails |
|  |  |  |
| `return` | `static` |  |

---


### vakata\validation\Validator::date
Add a date validation  


```php
public function date (  
    array $format,  
    string $message  
) : static    
```

|  | Type | Description |
|-----|-----|-----|
| `$format` | `array` | the optional format to conform to (otherwise any strtotime compatible input is valid) |
| `$message` | `string` | an optional message to include in the report if the validation fails |
|  |  |  |
| `return` | `static` |  |

---


### vakata\validation\Validator::minDate
Add a min date validation  


```php
public function minDate (  
    string|\DateTime|int $min,  
    string $format,  
    string $message  
) : static    
```

|  | Type | Description |
|-----|-----|-----|
| `$min` | `string`, `\DateTime`, `int` | the minimum that the value should be equal to or greater than |
| `$format` | `string` | the optional date format to conform to |
| `$message` | `string` | an optional message to include in the report if the validation fails |
|  |  |  |
| `return` | `static` |  |

---


### vakata\validation\Validator::maxDate
Add a max date validation  


```php
public function maxDate (  
    string|\DateTime|int $max,  
    string $format,  
    string $message  
) : static    
```

|  | Type | Description |
|-----|-----|-----|
| `$max` | `string`, `\DateTime`, `int` | the minimum that the value should be equal to or greater than |
| `$format` | `string` | the optional date format to conform to |
| `$message` | `string` | an optional message to include in the report if the validation fails |
|  |  |  |
| `return` | `static` |  |

---


### vakata\validation\Validator::betweenDate
Add a range date validation  


```php
public function betweenDate (  
    string|\DateTime|int $min,  
    string|\DateTime|int $max,  
    string $format,  
    string $message  
) : static    
```

|  | Type | Description |
|-----|-----|-----|
| `$min` | `string`, `\DateTime`, `int` | the minimum that the value should be equal to or greater than |
| `$max` | `string`, `\DateTime`, `int` | the minimum that the value should be equal to or less than |
| `$format` | `string` | the optional date format to conform to |
| `$message` | `string` | an optional message to include in the report if the validation fails |
|  |  |  |
| `return` | `static` |  |

---


### vakata\validation\Validator::age
Add an age validation (which could be relative to a given date)  


```php
public function age (  
    int $age,  
    string|\DateTime|int $rel,  
    string $format,  
    string $message  
) : static    
```

|  | Type | Description |
|-----|-----|-----|
| `$age` | `int` | the minimum age on a date |
| `$rel` | `string`, `\DateTime`, `int` | the date to compare to (defaults to today) |
| `$format` | `string` | the optional date format to conform to |
| `$message` | `string` | an optional message to include in the report if the validation fails |
|  |  |  |
| `return` | `static` |  |

---


### vakata\validation\Validator::json
Add a JSON validation  


```php
public function json (  
    string $message  
) : static    
```

|  | Type | Description |
|-----|-----|-----|
| `$message` | `string` | an optional message to include in the report if the validation fails |
|  |  |  |
| `return` | `static` |  |

---


### vakata\validation\Validator::ip
Add an IP address validation  


```php
public function ip (  
    string $message  
) : static    
```

|  | Type | Description |
|-----|-----|-----|
| `$message` | `string` | an optional message to include in the report if the validation fails |
|  |  |  |
| `return` | `static` |  |

---


### vakata\validation\Validator::url
Add an URL validation  


```php
public function url (  
    array|null $protocols,  
    string $message  
) : static    
```

|  | Type | Description |
|-----|-----|-----|
| `$protocols` | `array`, `null` | array of allowed protocols (defaults to ['http','https']) |
| `$message` | `string` | an optional message to include in the report if the validation fails |
|  |  |  |
| `return` | `static` |  |

---


### vakata\validation\Validator::mod10
Add a mod10 validation  


```php
public function mod10 (  
    string $message  
) : static    
```

|  | Type | Description |
|-----|-----|-----|
| `$message` | `string` | an optional message to include in the report if the validation fails |
|  |  |  |
| `return` | `static` |  |

---


### vakata\validation\Validator::imei
Add a imei validation  


```php
public function imei (  
    string $message  
) : static    
```

|  | Type | Description |
|-----|-----|-----|
| `$message` | `string` | an optional message to include in the report if the validation fails |
|  |  |  |
| `return` | `static` |  |

---


### vakata\validation\Validator::creditcard
Add credit card validation  


```php
public function creditcard (  
    array|null $types,  
    string $message  
) : static    
```

|  | Type | Description |
|-----|-----|-----|
| `$types` | `array`, `null` | optional array of allowed cards (visa, mastercard, americanexpress, dinersclub, discover, jcb) |
| `$message` | `string` | an optional message to include in the report if the validation fails |
|  |  |  |
| `return` | `static` |  |

---


### vakata\validation\Validator::iban
Add an IBAN validation  


```php
public function iban (  
    string $message  
) : static    
```

|  | Type | Description |
|-----|-----|-----|
| `$message` | `string` | an optional message to include in the report if the validation fails |
|  |  |  |
| `return` | `static` |  |

---


### vakata\validation\Validator::uuid
Add an UUID validation  


```php
public function uuid (  
    string $message  
) : static    
```

|  | Type | Description |
|-----|-----|-----|
| `$message` | `string` | an optional message to include in the report if the validation fails |
|  |  |  |
| `return` | `static` |  |

---


### vakata\validation\Validator::mac
Add a MAC validation  


```php
public function mac (  
    string $message  
) : static    
```

|  | Type | Description |
|-----|-----|-----|
| `$message` | `string` | an optional message to include in the report if the validation fails |
|  |  |  |
| `return` | `static` |  |

---


### vakata\validation\Validator::bgEGN
Add a Bulgarian EGN validation  


```php
public function bgEGN (  
    string $message  
) : static    
```

|  | Type | Description |
|-----|-----|-----|
| `$message` | `string` | an optional message to include in the report if the validation fails |
|  |  |  |
| `return` | `static` |  |

---


### vakata\validation\Validator::bgLNC
Add a Bulgarian LNC validation  


```php
public function bgLNC (  
    string $message  
) : static    
```

|  | Type | Description |
|-----|-----|-----|
| `$message` | `string` | an optional message to include in the report if the validation fails |
|  |  |  |
| `return` | `static` |  |

---


### vakata\validation\Validator::bgIDN
Add a Bulgarian identification number validation (EGN or LNC)  


```php
public function bgIDN (  
    string $message  
) : static    
```

|  | Type | Description |
|-----|-----|-----|
| `$message` | `string` | an optional message to include in the report if the validation fails |
|  |  |  |
| `return` | `static` |  |

---


### vakata\validation\Validator::bgMaleEGN
Add a Bulgarian male EGN validation  


```php
public function bgMaleEGN (  
    string $message  
) : static    
```

|  | Type | Description |
|-----|-----|-----|
| `$message` | `string` | an optional message to include in the report if the validation fails |
|  |  |  |
| `return` | `static` |  |

---


### vakata\validation\Validator::bgFemaleEGN
Add a Bulgarian female EGN validation  


```php
public function bgFemaleEGN (  
    string $message  
) : static    
```

|  | Type | Description |
|-----|-----|-----|
| `$message` | `string` | an optional message to include in the report if the validation fails |
|  |  |  |
| `return` | `static` |  |

---


### vakata\validation\Validator::bgBulstat
Add a Bulgarian BULSTAT validation  


```php
public function bgBulstat (  
    string $message  
) : static    
```

|  | Type | Description |
|-----|-----|-----|
| `$message` | `string` | an optional message to include in the report if the validation fails |
|  |  |  |
| `return` | `static` |  |

---


### vakata\validation\Validator::bgName
Add a Bulgarian name validation  


```php
public function bgName (  
    string $message  
) : static    
```

|  | Type | Description |
|-----|-----|-----|
| `$message` | `string` | an optional message to include in the report if the validation fails |
|  |  |  |
| `return` | `static` |  |

---

