<?php

namespace BelVG\GemcomProduct\Model\Transform;

use BelVG\GemcomProduct\Api\Transform\TransformProcessorInterface;
use Product;

class FeaturesTransformProcessor implements TransformProcessorInterface
{
    /**
     * @inheritDoc
     */
    public function process(array $params)
    {
        list($fromProductId, $toProductId) = $params;
        Product::duplicateFeatures($fromProductId, $toProductId);
        return true;
    }
}
