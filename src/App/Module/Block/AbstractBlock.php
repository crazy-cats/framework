<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Module\Block;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
abstract class AbstractBlock extends \CrazyCat\Framework\App\Theme\Block {

    public function toHtml()
    {
        $profileName = 'Render block: ' . $this->getData( 'name' );

        profile_start( $profileName );
        $html = parent::toHtml();
        profile_end( $profileName );

        return $html;
    }

}
