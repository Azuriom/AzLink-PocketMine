<?php

namespace Azuriom\AzLink\PocketMine\Utils;

use ThreadedArray;

class Convertor {
    public static function threadArrayToArray(ThreadedArray $value): array{
        return (array) $value;
    }
}