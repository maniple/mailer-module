<?php

namespace MailerModule;

use PhpEnum\Enum as BaseEnum;

abstract class Enum extends BaseEnum
{
    /**
     * @param mixed $value
     * @return Enum|null
     */
    public static function createOrNull($value)
    {
        if (null === $value) {
            return null;
        }
        if (!$value instanceof static) {
            return new static($value);
        }
        return $value;
    }
}
