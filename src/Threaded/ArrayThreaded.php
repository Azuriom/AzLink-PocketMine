<?php

namespace Azuriom\AzLink\PocketMine\Threaded;

use Threaded;

class ArrayThreaded extends Threaded
{

    public function __construct(array $array = [])
    {
        foreach ($array as $key => $value) {
            $this[$key] = $value;
        }
    }


    public function toArray(): array
    {
        return (array) $this;
    }
}