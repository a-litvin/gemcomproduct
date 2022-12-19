<?php

namespace BelVG\GemcomProduct\Model\Transform;

use BelVG\GemcomProduct\Api\Transform\TransformProcessorInterface;
use SpecificPrice;

class SpecificPriceTransformProcessor implements TransformProcessorInterface
{
    /**
     * @inheritDoc
     */
    public function process(array $params)
    {
        $result = true;
        list($fromProductId, $fromProductAttributeId, $toProductId, $toProductAttributeId) = $params;
        $specificPrices = SpecificPrice::getByProductId($fromProductId, $fromProductAttributeId);
        if (!$specificPrices) {
            return $result;
        }

        foreach ($specificPrices as $price) {
            if ((int) $price['id_product_attribute'] !== $fromProductAttributeId) {
                $sourceSpecificPrice = new SpecificPrice($price['id_specific_price']);
                $specificPrice = clone $sourceSpecificPrice;
                $specificPrice->id = null;
                $specificPrice->id_product = $toProductId;
                $specificPrice->id_product_attribute = $toProductAttributeId;
                $result &= $specificPrice->add();
                $result &= $sourceSpecificPrice->delete();
            }
        }

        return $result;
    }
}
