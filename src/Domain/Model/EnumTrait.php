<?php

namespace Linkedcode\Slim\Domain\Model;

trait EnumTrait
{
    public static function fromString(string $value): static
    {
        $cases = self::cases();

        foreach ($cases as $case) {
            if (strtoupper($case->name) === strtoupper($value)) {
                return static::from($case->value);
            }
        }

        return static::from(0);
    }

    public static function names(): array
    {
        $names = [];
        $cases = self::cases();

        foreach ($cases as $case) {
            $names[] = $case->name;
        }

        return $names;
    }
}
