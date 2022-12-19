<?php

namespace BelVG\GemcomProduct\Model\Transform;

use BelVG\GemcomProduct\Api\Transform\TransformProcessorInterface;
use BelVG\ModuleCore\Exception\ClassDoesNotExistException;
use BelVG\ModuleCore\Exception\WrongEntityTypeException;

class TransformProcessorFactory
{
    /**
     * @param string $instanceName
     * @return TransformProcessorInterface
     * @throws ClassDoesNotExistException
     * @throws WrongEntityTypeException
     */
    public static function create($instanceName)
    {
        if (!class_exists($instanceName)) {
            throw new ClassDoesNotExistException(sprintf('Cannot find class %s', $instanceName));
        }

        $transformProcessor = new $instanceName();
        if (!($transformProcessor instanceof TransformProcessorInterface)) {
            throw new WrongEntityTypeException(sprintf('%s is not an instance of the %s', $instanceName, TransformProcessorInterface::class));
        }

        return $transformProcessor;
    }
}
