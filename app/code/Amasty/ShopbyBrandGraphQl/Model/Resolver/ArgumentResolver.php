<?php

declare(strict_types=1);

namespace Amasty\ShopbyBrandGraphQl\Model\Resolver;

class ArgumentResolver
{
    public function convertArgs(array $args): array
    {
        $result = [];
        foreach ($args as $key => $arg) {
            $result[$this->convertFromCamelCase($key)] = $arg;
        }

        return $result;
    }

    private function convertFromCamelCase(string $name): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $name));
    }
}
