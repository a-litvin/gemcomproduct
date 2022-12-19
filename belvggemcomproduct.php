<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__.'/vendor/autoload.php';

class BelVGGemcomProduct extends Module
{
    /**
     * @var string[]
     */
    protected $hooks = [
        'actionProductUpdate'
    ];

    /**
     * @var \BelVG\ModuleCore\Api\Hook\HookManagerInterface
     */
    private $hookManager;

    /**
     * BelVGGemcomProduct constructor
     */
    public function __construct()
    {
        $this->name = 'belvggemcomproduct';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'BelVG';
        $this->need_instance = 0;

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Gemcom Product Manager');
        $this->description = $this->l('Gemcom Product Management Features.');
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => '1.6.1.24');

        Module::getInstanceByName('belvgmodulecore');
        $this->hookManager = \BelVG\ModuleCore\Hook\HookManagerFactory::create('BelVG\\GemcomProduct\\Hook\\HookManager', $this, $this->hooks);
    }

    /**
     * @inheritDoc
     */
    public function install()
    {
        try {
            $installer = \BelVG\ModuleCore\Setup\InstallerFactory::create('BelVG\\GemcomProduct\\Setup\\Installer');
        } catch (\Exception $exception) {
            return false;
        }
        return parent::install() && $installer->install($this, $this->hooks);
    }

    /**
     * @inheritDoc
     */
    public function uninstall()
    {
        try {
            $installer = \BelVG\ModuleCore\Setup\InstallerFactory::create('BelVG\\GemcomProduct\\Setup\\Installer');
        } catch (\Exception $exception) {
            return false;
        }
        return $installer->uninstall() && parent::uninstall();
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        try {
            return $this->hookManager->handle($name, $arguments);
        } catch (Exception $exception) {
        }
    }
}
