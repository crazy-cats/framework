<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App;

use CrazyCat\Framework\App\Setup\Wizard;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Config extends \CrazyCat\Framework\Data\Object {

    const DIR = DIR_APP . DS . 'config';
    const FILE = self::DIR . DS . 'env.php';

    public function __construct( Wizard $wizard )
    {
        if ( !is_file( self::FILE ) ) {
            $wizard->launch();
        }
        parent::__construct( require self::FILE );
    }

}
