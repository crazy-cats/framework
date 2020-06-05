<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */

use CrazyCat\Framework\App\Component\Language\Translator;
use CrazyCat\Framework\App\ObjectManager;
use CrazyCat\Framework\Utility\Html;
use CrazyCat\Framework\Utility\Profile;

/**
 * @param string $text
 * @param array  $variables
 * @return string
 * @throws ReflectionException
 */
function __($text, $variables = [])
{
    return ObjectManager::getInstance()->get(Translator::class)->translate($text, $variables);
}

/**
 * @param string $str
 * @return string
 */
function htmlEscape($str)
{
    return htmlspecialchars($str, ENT_QUOTES);
}

/**
 * @return string
 * @throws ReflectionException
 */
function getLangCode()
{
    return ObjectManager::getInstance()->get(Translator::class)->getLangCode();
}

/**
 * @return string
 * @throws ReflectionException
 */
function spaceString()
{
    return ObjectManager::getInstance()->get(Html::class)->spaceString();
}

/**
 * @param array             $options
 * @param array|string|null $value
 * @return string
 */
function selectOptionsHtml(array $options, $value = null)
{
    return ObjectManager::getInstance()->get(Html::class)->selectOptionsHtml($options, $value);
}

/**
 * @param string $name
 * @return void
 * @throws ReflectionException
 */
function profile_start($name)
{
    Profile::start($name);
}

/**
 * @param string $name
 * @return void
 * @throws Exception
 */
function profile_end($name)
{
    Profile::end($name);
}
