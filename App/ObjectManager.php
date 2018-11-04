<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class ObjectManager {

    /**
     * @var \CrazyCat\Framework\App\ObjectManager
     */
    static private $instance;

    /**
     * @var array
     */
    private $singletons = [];

    /**
     * Get object manager singleton
     * @return \CrazyCat\Framework\App\ObjectManager
     */
    static public function getInstance()
    {
        if ( self::$instance === null ) {
            self::$instance = new ObjectManager;
        }
        return self::$instance;
    }

    /**
     * @param string $className
     * @param array $argumentArr
     * @return objec
     */
    public function create( $className, $argumentArr = [] )
    {
        $class = '\\' . trim( $className, '\\' );
        $reflectionClass = new \ReflectionClass( $class );

        if ( !( $constructor = $reflectionClass->getConstructor() ) ) {
            return $reflectionClass->newInstanceWithoutConstructor();
        }

        $arguments = [];
        foreach ( $constructor->getParameters() as $parameter ) {
            /* @var $parameter \ReflectionParameter */
            if ( isset( $argumentArr[$parameter->getName()] ) ) {
                $arguments[] = $argumentArr[$parameter->getName()];
            }
            elseif ( $parameter->isOptional() ) {
                $arguments[] = $parameter->getDefaultValue();
            }
            elseif ( ( $injectedClass = $parameter->getClass() ) ) {
                $arguments[] = $this->get( $injectedClass->getName() );
            }
            else {
                throw new \Exception( sprintf( 'Argument `%s` of class `%s` is required.', $parameter->getName(), $class ) );
            }
        }

        return $reflectionClass->newInstanceArgs( $arguments );
    }

    /**
     * @param string $className
     * @param array $argumentArr
     * @return objec
     */
    public function get( $className, $argumentArr = [] )
    {
        $cacheName = trim( $className, '\\' );
        if ( $cacheName == self::class ) {
            return self::getInstance();
        }
        if ( !isset( $this->singletons[$cacheName] ) ) {
            $this->singletons[$cacheName] = $this->create( $className, $argumentArr );
        }
        return $this->singletons[$cacheName];
    }

}
