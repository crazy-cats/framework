<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
use CrazyCat\Framework\App\ObjectManager;
use CrazyCat\Framework\App\Url;
use CrazyCat\Framework\App\Theme\Manager as ThemeManager;
use CrazyCat\Framework\App\Translator;
use CrazyCat\Framework\Utility\Html;

/**
 * @param string $text
 * @param array $variables
 * @return string
 */
function __( $text, $variables = [] )
{
    return ObjectManager::getInstance()->get( Translator::class )->translate( $text, $variables );
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

/**
 * @param string $path
 * @param string|null $themeName
 * @return string
 */
function getStaticUrl( $path, $areaCode = null, $themeName = null )
{
    /* @var $themeManager \CrazyCat\Framework\App\Theme\Manager */
    $themeManager = ObjectManager::getInstance()->get( ThemeManager::class );

    /* @var $theme \CrazyCat\Framework\App\Theme */
    $theme = ( $areaCode === null || $themeName === null ) ?
            $themeManager->getCurrentTheme() :
            $themeManager->getThemes( $areaCode )[$themeName];

    return $theme->getStaticUrl( $path );
}

/**
 * @return string
 */
function getLangCode()
{
    return ObjectManager::getInstance()->get( Translator::class )->getLangCode();
}

/**
 * @return string
 */
function spaceString()
{
    return Html::spaceString();
}

/**
 * @param array $options
 * @param array|string|null $value
 * @return string
 */
function selectOptionsHtml( array $options, $value = null )
{
    return Html::selectOptionsHtml( $options, $value );
}
