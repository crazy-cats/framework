<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

use CrazyCat\Framework\App;

/**
 * @param string $text
 * @param array $variables
 * @return string
 */
function __( $text, $variables = [] )
{
    return App::getInstance()->getTranslator()->translate( $text, $variables );
}

function htmlEscape( $str )
{
    return htmlspecialchars( $str, ENT_QUOTES );
}
