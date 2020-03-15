<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     http://crazy-cat.cn
 */
class ObjectManager
{
    const CACHE_DI_NAME = 'di';

    /**
     * @var \CrazyCat\Framework\App\ObjectManager
     */
    private static $instance;

    /**
     * @var array
     */
    private $singletons = [];

    /**
     * @var array
     */
    private $preferences = [];

    /**
     * Get object manager singleton
     * @return \CrazyCat\Framework\App\ObjectManager
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new ObjectManager();
        }
        return self::$instance;
    }

    /**
     * @param array $preferences
     */
    public function collectPreferences(array $preferences)
    {
        $this->preferences = array_merge(
            $this->preferences,
            array_map(
                function ($preference) {
                    return trim($preference, '\\');
                },
                $preferences
            )
        );
    }

    /**
     * @param string $preference
     * @param array $argumentArr
     * @return object
     * @throws \ReflectionException
     */
    public function create($preference, $argumentArr = [])
    {
        $preference = trim($preference, '\\');
        if (isset($this->preferences[$preference])) {
            return $this->create($this->preferences[$preference], $argumentArr);
        }

        $reflectionClass = new \ReflectionClass('\\' . $preference);

        if (!($constructor = $reflectionClass->getConstructor())) {
            return $reflectionClass->newInstanceWithoutConstructor();
        }

        $arguments = [];
        foreach ($constructor->getParameters() as $parameter) {
            /* @var $parameter \ReflectionParameter */
            if (isset($argumentArr[$parameter->getName()])) {
                $arguments[] = $argumentArr[$parameter->getName()];
            } elseif ($parameter->isOptional()) {
                $arguments[] = $parameter->getDefaultValue();
            } elseif (($injectedClass = $parameter->getClass())) {
                $arguments[] = $this->get($injectedClass->getName());
            } else {
                throw new \Exception(
                    sprintf('Argument `%s` of class `%s` is required.', $parameter->getName(), $class)
                );
            }
        }

        return $reflectionClass->newInstanceArgs($arguments);
    }

    /**
     * @param string $preference
     * @param array $argumentArr
     * @return object
     * @throws \ReflectionException
     */
    public function get($preference, $argumentArr = [])
    {
        $preference = trim($preference, '\\');
        if ($preference == self::class) {
            return self::getInstance();
        }
        if (isset($this->preferences[$preference])) {
            return $this->get($this->preferences[$preference], $argumentArr);
        }
        if (!isset($this->singletons[$preference])) {
            $this->singletons[$preference] = $this->create($preference, $argumentArr);
        }
        return $this->singletons[$preference];
    }
}
