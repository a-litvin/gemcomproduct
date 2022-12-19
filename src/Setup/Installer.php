<?php

namespace BelVG\GemcomProduct\Setup;

use BelVG\ModuleCore\Hook\HookManagerFactory;
use BelVG\ModuleCore\Setup\AbstractInstaller;
use Module;

class Installer extends AbstractInstaller
{
    /**
     * @inheritDoc
     */
    protected function getHookManager($module, $hooks)
    {
        return HookManagerFactory::create('BelVG\\GemcomProduct\\Hook\\HookManager', $module, $hooks);
    }
}
