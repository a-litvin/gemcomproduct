<?php

namespace BelVG\GemcomProduct\Model\Transform;

use BelVG\GemcomProduct\Api\Transform\TransformProcessorInterface;
use SpecificPrice;
use Context;

class PriceTransformProcessor implements TransformProcessorInterface
{
    /**
     * @inheritDoc
     */
    public function process(array $params)
    {
        list($productId, $productAttributeId, $basePrice, $specialPrice, $precision) = $params;
        $reduction = round(1 - ($specialPrice / $basePrice), $precision);
        if ($reduction > 0) {
            $data = [
                'id_product' => $productId,
                'id_product_attribute' => $productAttributeId,
                'reduction' => $reduction
            ];
            $data += $this->getSpecificPriceDefaults();
            $specificPrice = new SpecificPrice();
            $specificPrice->hydrate($data);
            return $specificPrice->add();
        }

        return true;
    }

    private function getSpecificPriceDefaults()
    {
        $context = Context::getContext();
        $shop = $context->shop;
        $currency = $context->currency;

        return [
            'id_shop' => $shop->id,
            'id_shop_group' => $shop->id_shop_group,
            'id_currency' => $currency->id,
            'id_country' => 0,
            'id_group' => 0,
            'id_customer' => 0,
            'price' => -1,
            'from_quantity' => 1,
            'reduction_type' => 'percentage',
            'from' => '0000-00-00',
            'to' => '0000-00-00'
        ];
    }
}
