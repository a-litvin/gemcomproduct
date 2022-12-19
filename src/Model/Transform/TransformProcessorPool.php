<?php

namespace BelVG\GemcomProduct\Model\Transform;

use BelVG\GemcomProduct\Api\Transform\TransformProcessorInterface;

class TransformProcessorPool
{
    /**
     * @var TransformProcessorInterface[]
     */
    private $pool;

    /**
     * @param TransformProcessorInterface[] $pool
     */
    public function __construct(array $pool)
    {
        $this->pool[] = $pool;
    }

    /**
     * @param array $params
     */
    public function process(array $params)
    {
        foreach ($this->pool as $processor) {
            $processor->process($params);
        }
    }
}
