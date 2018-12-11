<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\Utility;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Html {

    static public function htmlSelectSpace()
    {
        return html_entity_decode( '&#160;', ENT_NOQUOTES, 'UTF-8' );
    }

}
