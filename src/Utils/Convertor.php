<?php

namespace Azuriom\AzLink\PocketMine\Utils;



use pmmp\thread\ThreadSafeArray;

class Convertor {
    public static function threadArrayToArray(ThreadSafeArray $value): array{
        return (array) $value;
    }
}
