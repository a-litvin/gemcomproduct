<?php

namespace BelVG\GemcomProduct\Model\Transform;

use BelVG\GemcomProduct\Api\Transform\TransformProcessorInterface;
use BelVG\GemcomProduct\Model\Transfer\ImageTransferFactory;
use BelVG\GemcomProduct\Model\Transfer\ImageTransfer;
use BelVG\ModuleCore\Model\Response\RedirectFactory;
use BelVG\ModuleCore\Model\Response\Redirect;
use Product;
use Combination;
use StockAvailable;
use Context;
use Configuration;
use Translate;
use Validate;
use Tools;

class CombinationToProductTransformProcessor implements TransformProcessorInterface
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


    /**
     * @var FeaturesTransformProcessor
     */
    private $featuresProcessor;

    public function __construct()
    {
        $this->imageProcessor = TransformProcessorFactory::create('BelVG\\GemcomProduct\\Model\\Transform\\ImageTransformProcessor');
        $this->priceProcessor = TransformProcessorFactory::create('BelVG\\GemcomProduct\\Model\\Transform\\PriceTransformProcessor');
        $this->specificPriceProcessor = TransformProcessorFactory::create('BelVG\\GemcomProduct\\Model\\Transform\\SpecificPriceTransformProcessor');
        $this->featuresProcessor = TransformProcessorFactory::create('BelVG\\GemcomProduct\\Model\\Transform\\FeaturesTransformProcessor');
    }

    /**
     * @inheritDoc
     * ToDo: Add errors handling
     */
    public function process(array $params)
    {
        $context = Context::getContext();
        $importCategory = Configuration::get('ID_OBDELAVA_CATEGORY');
        $productAttributeId = $params['id_product_attribute'];
        $combination = new Combination($productAttributeId);

        $gemcomData = json_decode($combination->data_adamshop, true);
        $combinationName = str_replace(['"', '\'', '#'], '', $gemcomData['name']);
        if (empty($combinationName)) {
            $combinationName = 'Empty product name';
        }

        $parentProduct = new Product($combination->id_product);
        $price = Product::priceCalculation(
            $context->shop->id,
            $parentProduct->id,
            $combination->id,
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

        $product = new Product();
        $product->link_rewrite = [Configuration::get('PS_LANG_DEFAULT') => Tools::str2url($combinationName)];
        $product->name = [Configuration::get('PS_LANG_DEFAULT') => $combinationName];
        $product->weight = $combination->weight;
        $product->reference = $combination->reference;
        $product->odooid = $combination->odooid;
        $product->ean13 = $combination->ean13;
        $product->upc = $combination->upc;
        $product->price = $this->restorePrice($combination->data_adamshop) ?: $price;
        $product->id_category_default = $importCategory;
        $product->description = $parentProduct->description;
        $product->description_short = $parentProduct->description_short;
        $product->id_tax_rules_group = $parentProduct->id_tax_rules_group;
        // Disable a product by default
        $product->active = false;
        $product->data_adamshop = $combination->data_adamshop;
        $product->add();
        $product->addToCategories([
            Configuration::get('PS_ROOT_CATEGORY'),
            $importCategory
        ]);

        if (!Validate::isLoadedObject($product)) {
            $context->controller->errors[] = $this->l('A product was not created');
            return false;
        }

        $imagesIds = $this->getCombinationImages($combination);
        if (!empty($imagesIds)) {
            $this->imageProcessor->process([
                $product->id,
                $imagesIds,
                1
            ]);
        }

        $this->priceProcessor->process([
            $product->id,
            0,
            $product->price,
            $price,
            6
        ]);

        $combinationQuantity = StockAvailable::getQuantityAvailableByProduct($combination->id_product, $combination->id);
        StockAvailable::setQuantity($product->id, false, $combinationQuantity);

        /*$this->specificPriceProcessor->process([
            $parentProduct->id,
            $combination->id,
            $product->id,
            0
        ]);*/

        $this->featuresProcessor->process([
            $parentProduct->id,
            $product->id
        ]);

        $combination->delete();

        $response = RedirectFactory::create(
            [
                'controller' => 'AdminProducts',
                'id_product' => $product->id,
                'updateproduct' => ''
            ],
            Redirect::ADMIN_SCOPE
        );

        return $response;
    }

    /**
     * @param Combination $combination
     * @return array
     */
    private function getCombinationImages($combination)
    {
        $images = $combination->getWsImages();
        $imagesIds = [];

        if (!empty($images)) {
            $imagesIds = array_map(function($item)
            {
                return $item['id'];
            }, $images);
        }

        return $imagesIds;
    }

    /**
     * @param string $originalData
     * @return false|float
     */
    private function restorePrice($originalData)
    {
        $data = json_decode($originalData, true);
        return isset($data['price']) ? (float) $data['price'] : false;
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
