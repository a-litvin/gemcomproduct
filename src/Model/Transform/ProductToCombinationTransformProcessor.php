<?php

namespace BelVG\GemcomProduct\Model\Transform;

use BelVG\GemcomProduct\Api\Transform\TransformProcessorInterface;
use BelVG\GemcomProduct\Model\Transfer\ImageTransferFactory;
use BelVG\GemcomProduct\Model\Transfer\ImageTransfer;
use BelVG\ModuleCore\Model\Response\Redirect;
use BelVG\ModuleCore\Model\Response\RedirectFactory;
use Product;
use Combination;
use StockAvailable;
use Context;
use Configuration;
use Translate;
use Validate;

class ProductToCombinationTransformProcessor implements TransformProcessorInterface
{
    /**
     * @var ImageTransformProcessor
     */
    private $imageProcessor;

    /**
     * @var PriceTransformProcessor
     */
    private $priceProcessor;

    /**
     * @var SpecificPriceTransformProcessor
     */
    private $specificPriceProcessor;

    public function __construct()
    {
        $this->imageProcessor = TransformProcessorFactory::create('BelVG\\GemcomProduct\\Model\\Transform\\ImageTransformProcessor');
        $this->priceProcessor = TransformProcessorFactory::create('BelVG\\GemcomProduct\\Model\\Transform\\PriceTransformProcessor');
        $this->specificPriceProcessor = TransformProcessorFactory::create('BelVG\\GemcomProduct\\Model\\Transform\\SpecificPriceTransformProcessor');
    }

    /**
     * @inheritDoc
     * ToDo: Add errors handling
     */
    public function process(array $params)
    {
        $context = Context::getContext();
        $fromProductId = $params['id_product'];
        $fromProduct = new Product($fromProductId);
        $toProductId = $params['id_product_destination'];
        $toProduct = new Product($toProductId);

        if (!Validate::isLoadedObject($fromProduct) || !Validate::isLoadedObject($toProduct)) {
            $context->controller->errors[] = $this->l('Source or destination product doesn\'t exist');
            return false;
        }

        $imagesIds = $this->getProductImages($fromProduct, $context->language->id);
        if (!empty($imagesIds)) {
            $toProductImages = $this->getProductImages($toProduct, $context->language->id);
            $this->imageProcessor->process([
                $toProductId,
                $imagesIds,
                empty($toProductImages)
            ]);
            $imagesIds = array_diff($this->getProductImages($toProduct, $context->language->id), $toProductImages);
        }

        $combinationId = $toProduct->addAttribute(
            0,
            $fromProduct->weight,
            0,
            0,
            $imagesIds,
            $fromProduct->reference,
            $fromProduct->odooid,
            $fromProduct->ean13,
            null,
            null,
            $fromProduct->upc
        );
        $combination = new Combination($combinationId);
        if (!Validate::isLoadedObject($combination)) {
            $context->controller->errors[] = $this->l('Couldn\'t create a combination from the product');
            return false;
        }
        $combination->data_adamshop = $this->getGemcomDataForCombination($fromProduct);
        $combination->update();

        $fromProductPrice = Product::priceCalculation(
            $context->shop->id,
            $fromProduct->id,
            null,
            null,
            null,
            null,
            $context->currency->id,
            null,
            null,
            false,
            6,
            false,
            true,
            true,
            $specificPriceOutput,
            true
        );
        $toProductPrice = Product::priceCalculation(
            $context->shop->id,
            $toProduct->id,
            null,
            null,
            null,
            null,
            $context->currency->id,
            null,
            null,
            false,
            6,
            false,
            false,
            false,
            $specificPriceOutput,
            false
        );
        $this->priceProcessor->process([
            $toProduct->id,
            $combinationId,
            is_null($toProductPrice) ? (float) $toProduct->price : $toProductPrice,
            $fromProductPrice,
            6
        ]);

        $productQuantity = StockAvailable::getQuantityAvailableByProduct($fromProduct->id);
        StockAvailable::setQuantity($toProduct->id, $combinationId, $productQuantity);

        /*$this->specificPriceProcessor->process([
            $fromProduct->id,
            0,
            $toProduct->id,
            $combinationId
        ]);*/

        $fromProduct->delete();

        $response = RedirectFactory::create(
            [
                'controller' => 'AdminProducts',
                'id_product' => $toProductId,
                'updateproduct' => ''
            ],
            Redirect::ADMIN_SCOPE
        );

        return $response;
    }

    /**
     * @param Product $product
     * @return false|string
     */
    private function getGemcomDataForCombination(Product $product)
    {
        $gemcomData = json_decode($product->data_adamshop, true) ?: [];
        $gemcomData['name'] = $product->name[Configuration::get('PS_LANG_DEFAULT')];
        $gemcomData['default_code'] = $product->reference;
        $gemcomData['weight_bruto'] = $product->weight;
        $gemcomData['id'] = $product->odooid;
        $gemcomData['price'] = $product->price;
        return json_encode($gemcomData);
    }

    /**
     * @param Product $product
     * @param int $langId
     * @return array
     */
    private function getProductImages($product, $langId)
    {
        $images = $product->getImages($langId);
        $imagesIds = [];

        if (!empty($images)) {
            $imagesIds = array_map(function($item)
            {
                return $item['id_image'];
            }, $images);
        }

        return $imagesIds;
    }

    /**
     * @param string $string
     * @return string
     */
    public function l($string)
    {
        return Translate::getModuleTranslation('belvggemcomproduct', $string, 'belvggemcomproduct');
    }
}
