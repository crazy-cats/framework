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
class Config extends \CrazyCat\Framework\Data\Object {

    const DIR = DIR_APP . DS . 'config';

    public function __construct()
    {
        if ( !is_file( self::DIR . DS . 'env.php' ) ) {
            throw new \Exception( 'Config file does not exist.' );
        }
        parent::__construct( require self::DIR . DS . 'env.php' );
    }

}
