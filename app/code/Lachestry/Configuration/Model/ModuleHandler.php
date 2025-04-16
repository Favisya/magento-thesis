<?php

declare(strict_types=1);

namespace Lachestry\Configuration\Model;

class ModuleHandler
{
    public function getModuleName(string $instance): string
    {
        $instance = explode('\\', $instance);
        $name = array_slice($instance, 0, 2);

        return implode('_', $name);
    }
}
