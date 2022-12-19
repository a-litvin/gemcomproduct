<?php

namespace BelVG\GemcomProduct\Model\Transform;

use BelVG\GemcomProduct\Api\Transform\TransformProcessorInterface;
use Product;
use Image;

/**
 * ToDo: class refactoring
 */
class ImageTransformProcessor implements TransformProcessorInterface
{
    /**
     * @inheritDoc
     */
    public function process(array $params)
    {
        list($productId, $imagesIds, $setCover) = $params;
        if (empty($imagesIds)) {
            return true;
        }

        foreach ($imagesIds as $imageId) {
            $oldImage = new Image($imageId);
            $newImage = clone $oldImage;
            $newImage->id_product = $productId;
            if ($setCover) {
                $newImage->cover = 1;
                $setCover = false;
            } else {
                $newImage->cover = 0;
            }
            $newImage->add();
            $newImage->createImgFolder();
            $sourcePath = _PS_PROD_IMG_DIR_ . $oldImage->getExistingImgPath() . '.jpg';
            $destinationPath = $newImage->getPathForCreation() . '.jpg';
            if (file_exists($sourcePath)) {
                copy($sourcePath, $destinationPath);
            }
            $oldImage->delete();
        }

        return true;
    }
}
