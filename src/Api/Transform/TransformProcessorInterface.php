<?php

namespace BelVG\GemcomProduct\Api\Transform;

use BelVG\ModuleCore\Api\Response\ResponseInterface;

interface TransformProcessorInterface
{
    /**
     * @param array $params
     * @return ResponseInterface|bool|null
     */
    public function process(array $params);
}
