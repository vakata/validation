<?php

namespace vakata\validation;

/**
 * A validation class, supporting arrays and nested arrays of data.
 */
class Rule
{
    protected $key;
    protected $handler;
    protected $message;
    protected $rule;
    protected $data;
    protected $optional;
    protected $enabled;
    protected $condition;
    protected $validator;

    public function __construct(
        string $key,
        callable $handler,
        string $message = '',
        string $rule = 'callback',
        array $data = [],
        bool $optional = true,
        callable $condition = null,
        Validator $validator = null
    ) {
        $this->key = $key;
        $this->handler = $handler;
        $this->message = $message;
        $this->rule = $rule;
        $this->data = $data;
        $this->optional = $optional;
        $this->enabled = true;
        $this->condition = $condition;
        $this->validator = $validator;
    }
    public function getKey(): string
    {
        return $this->key;
    }
    public function setKey(string $key): self
    {
        $this->key = $key;
        return $this;
    }
    public function getMessage(): string
    {
        return $this->message;
    }
    public function getRule(): string
    {
        return $this->rule;
    }
    public function getData(): array
    {
        return $this->data;
    }
    public function isRequired(): bool
    {
        return !$this->optional;
    }
    public function isOptional(): bool
    {
        return $this->optional;
    }
    public function isEnabled(): bool
    {
        return $this->enabled;
    }
    public function isDisabled(): bool
    {
        return !$this->enabled;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }
    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }
    public function required(): self
    {
        $this->optional = false;
        return $this;
    }
    public function optional(): self
    {
        $this->optional = true;
        return $this;
    }
    public function disable(): self
    {
        $this->enabled = false;
        return $this;
    }
    public function enable(): self
    {
        $this->enabled = true;
        return $this;
    }

    public function hasCondition(): bool
    {
        return $this->condition !== null;
    }
    public function setCondition(callable $condition = null): self
    {
        $this->condition !== $condition;
        return $this;
    }

    public function hasValidator(): bool
    {
        return $this->validator !== null;
    }
    public function getValidator(): ?Validator
    {
        return $this->validator;
    }
    public function setValidator(?Validator $validator = null): self
    {
        $this->validator = $validator;
        return $this;
    }

    public function execute($key, $value, $data, $context = null)
    {
        if ($this->hasCondition() && !call_user_func($this->condition, $value, $data, $context)) {
            return true;
        }
        if ($this->hasValidator()) {
            $v = new Validator();
            foreach ($this->getValidator()->rules() as $r) {
                $rule = (clone $r);
                if ($rule->getKey()[0] === '.') {
                    $parts = explode('.', $key);
                    $parts[count($parts) - 1] = substr($rule->getKey(), 1);
                    $rule->setKey(implode('.', $parts));
                }
                $v->addRule($rule);
            }
            if (count($v->run($data, $context))) {
                return true;
            }
        }
        return !$this->hasCondition() || call_user_func($this->condition, $value, $data, $context) ?
            call_user_func($this->handler, $value, $data, $context) :
            true;
    }
}
