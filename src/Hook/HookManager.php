<?php

namespace BelVG\GemcomProduct\Hook;

use BelVG\ModuleCore\Hook\AbstractHookManager;

class HookManager extends AbstractHookManager
{
    /**
     * @inheritDoc
     */
    protected function getHookHandlerByName($name)
    {
        return __NAMESPACE__ . '\\' . ucfirst($name) . self::HOOK_HANDLER_POSTFIX;
    }
}
