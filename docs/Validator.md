# vakata\validation\Validator
A validation class, supporting arrays and nested arrays of data.

## Methods

| Name | Description |
|------|-------------|
|[run](#vakata\validation\validatorrun)|Run the validator on the passed data|
|[required](#vakata\validation\validatorrequired)|Add a required key to validate.|
|[optional](#vakata\validation\validatoroptional)|Add an optional key to validate - the validations that follow will only run if the key is present.|
|[callback](#vakata\validation\validatorcallback)|Add a validation rule in the form of a callable, it will receive the current key's value and the whole data.|
|[numeric](#vakata\validation\validatornumeric)|Add a numeric validation|
|[regex](#vakata\validation\validatorregex)|Add a validation using a regular expression|
|[mail](#vakata\validation\validatormail)|Add a valid mail validation|

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
) : self    
```

|  | Type | Description |
|-----|-----|-----|
| `$key` | `string` | the key name |
| `$message` | `string` | optional message to error with if the key is not present when running the validator |
|  |  |  |
| `return` | `self` |  |

---


### vakata\validation\Validator::optional
Add an optional key to validate - the validations that follow will only run if the key is present.  


```php
public function optional (  
    string $key  
) : self    
```

|  | Type | Description |
|-----|-----|-----|
| `$key` | `string` | the key name to look for |
|  |  |  |
| `return` | `self` |  |

---


### vakata\validation\Validator::callback
Add a validation rule in the form of a callable, it will receive the current key's value and the whole data.  


```php
public function callback (  
    callable $handler,  
    string $message  
) : self    
```

|  | Type | Description |
|-----|-----|-----|
| `$handler` | `callable` | the callable should return `true` if validation is OK and `false` otherwise |
| `$message` | `string` | optional message to include in the report if the validation fails |
|  |  |  |
| `return` | `self` |  |

---


### vakata\validation\Validator::numeric
Add a numeric validation  


```php
public function numeric (  
    string $message  
) : self    
```

|  | Type | Description |
|-----|-----|-----|
| `$message` | `string` | optional message to include in the report if the validation fails |
|  |  |  |
| `return` | `self` |  |

---


### vakata\validation\Validator::regex
Add a validation using a regular expression  


```php
public function regex (  
    string $regex,  
    string $message  
) : self    
```

|  | Type | Description |
|-----|-----|-----|
| `$regex` | `string` | the regex to validate against |
| `$message` | `string` | an optional message to include in the report if the validation fails |
|  |  |  |
| `return` | `self` |  |

---


### vakata\validation\Validator::mail
Add a valid mail validation  


```php
public function mail (  
    string $message  
) : self    
```

|  | Type | Description |
|-----|-----|-----|
| `$message` | `string` | an optional message to include in the report if the validation fails |
|  |  |  |
| `return` | `self` |  |

---

