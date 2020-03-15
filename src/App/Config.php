<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App;

use CrazyCat\Framework\App\Area;
use CrazyCat\Framework\App\Setup\Wizard;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     http://crazy-cat.cn
 */
class Config extends \CrazyCat\Framework\Data\DataObject {

    const DIR = DIR_APP . DS . 'config';
    const FILE = self::DIR . DS . 'env.php';

    /**
     * @var \CrazyCat\Framework\App\Area
     */
    private $area;

    public function __construct( Area $area, Wizard $wizard )
    {
        if ( !is_file( self::FILE ) ) {
            $wizard->launch();
        }
        parent::__construct( require self::FILE );

        $this->area = $area;
    }

    /**
     * @return mixed
     */
    public function getValue( $path, $scope = null )
    {
        if ( $scope === null ) {
            $scope = $this->area->getCode();
        }
        $config = $this->getData( $scope );

        if ( isset( $config[$path] ) ) {
            return $config[$path];
        }

        $globalConfig = $this->getData( Area::CODE_GLOBAL );
        return isset( $globalConfig[$path] ) ? $globalConfig[$path] : null;
    }

}
