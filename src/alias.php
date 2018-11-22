<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

use CrazyCat\Framework\App;
use CrazyCat\Framework\App\ObjectManager;
use CrazyCat\Framework\App\Url;

/**
 * @param string $text
 * @param array $variables
 * @return string
 */
function __( $text, $variables = [] )
{
    return App::getInstance()->getTranslator()->translate( $text, $variables );
}

/**
 * @param string $str
 * @return string
 */
function htmlEscape( $str )
{
    return htmlspecialchars( $str, ENT_QUOTES );
}

/**
 * @return string
 */
function getBaseUrl()
{
    return ObjectManager::getInstance()->get( Url::class )->getBaseUrl();
}

/**
 * @return string
 */
function getCurrentUrl()
{
    return ObjectManager::getInstance()->get( Url::class )->getCurrentUrl();
}

/**
 * @param string $path
 * @param array $params
 * @return string
 */
function getUrl( $path, array $params = [] )
{
    return ObjectManager::getInstance()->get( Url::class )->getUrl( $path, $params );
}
