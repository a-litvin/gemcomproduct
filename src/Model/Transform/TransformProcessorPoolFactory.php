<?php

namespace BelVG\GemcomProduct\Model\Transform;

use Exception;

class TransformProcessorPoolFactory
{
    /**
     * @return TransformProcessorPool
     */
    public static function create(array $pool)
    {
        $transformerPool = [];
        foreach ($pool as $item) {
            try {
                $transformerPool[] = TransformProcessorFactory::create($item);
            } catch (Exception $exception) {

            }
        }
        return new TransformProcessorPool($transformerPool);
    }
}
