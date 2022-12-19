<?php

namespace BelVG\GemcomProduct\Hook;

use BelVG\ModuleCore\Api\Hook\HookHandlerInterface;
use BelVG\ModuleCore\Hook\AbstractHookHandler;
use BelVG\GemcomProduct\Model\Transform\TransformProcessorFactory;
use BelVG\ModuleCore\Api\Response\ResponseInterface;
use Tools;

class ActionProductUpdateHookHandler extends AbstractHookHandler implements HookHandlerInterface
{
    /**
     * @inheritDoc
     */
    public function process($arguments)
    {
        $pool = [
            'BelVG\\GemcomProduct\\Model\\Transform\\ProductToCombinationTransformProcessor',
            'BelVG\\GemcomProduct\\Model\\Transform\\CombinationToProductTransformProcessor'
        ];
        $params = array_shift($arguments);
        $params['module'] = $this->module;
        $destinationProductId = Tools::getValue('moveProductToCombination');
        $response = true;

        if ($destinationProductId) {
            $transformProcessor = TransformProcessorFactory::create('BelVG\\GemcomProduct\\Model\\Transform\\ProductToCombinationTransformProcessor');
            $params['id_product_destination'] = $destinationProductId;
            $response = $transformProcessor->process($params);
        }
        if (Tools::getIsset('conversionСombinationToProduct')) {
            $transformProcessor = TransformProcessorFactory::create('BelVG\\GemcomProduct\\Model\\Transform\\CombinationToProductTransformProcessor');
            $params['id_product_attribute'] = Tools::getValue('id_product_attribute');
            $response = $transformProcessor->process($params);
        }

        if ($response instanceof ResponseInterface) {
            return $response->process();
        }

        return $response;
    }
}
