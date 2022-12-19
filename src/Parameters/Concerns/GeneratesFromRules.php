<?php

namespace Mtrajano\LaravelSwagger\Parameters\Concerns;

use Illuminate\Support\Str;

trait GeneratesFromRules
{
    protected function splitRules($rules)
    {
        if (is_string($rules)) {
            return explode('|', $rules);
        } else {
            return $rules;
        }
    }

    protected function getParamType(array $paramRules): string
    {
        if (in_array('integer', $paramRules)) {
            return 'integer';
        } elseif (in_array('numeric', $paramRules)) {
            return 'number';
        } elseif (in_array('boolean', $paramRules)) {
            return 'boolean';
        } elseif (in_array('array', $paramRules)) {
            return 'array';
        } else {
            //date, ip, email, etc..
            return 'string';
        }
    }

    protected function isParamRequired(array $paramRules): bool
    {
        return in_array('required', $paramRules);
    }

    protected function isArrayParameter($param): bool
    {
        return Str::contains($param, '*');
    }

    protected function getArrayKey($param)
    {
        return current(explode('.', $param));
    }

    protected function getEnumValues(array $paramRules): array
    {
        $in   = $this->getInParameter($paramRules);
        $enum = $this->getEnumParameter($paramRules);

        if (!$in && !$enum) {
            return [];
        }

        if ($enum) {
            return array_column($enum::cases(), 'value');
        } else {
            [$param, $vals] = explode(':', $in);
        }

        return explode(',', $vals);
    }

    private function getInParameter(array $paramRules)
    {
        foreach ($paramRules as $rule) {
            if ((is_string($rule) || method_exists($rule, '__toString')) && Str::startsWith((string)$rule, 'in:')) {
                return $rule;
            }
        }

        return false;
    }

    private function getEnumParameter(array $paramRules)
    {
        foreach ($paramRules as $rule) {
            if (is_object($rule) && $rule instanceof \Illuminate\Validation\Rules\Enum) {
                $reflection = new \ReflectionClass($rule);
                $property   = $reflection->getProperty('type');
                $property->setAccessible(true);
                return $property->getValue($rule);
            }
        }

        return false;
    }
}