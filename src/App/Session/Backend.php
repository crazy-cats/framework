<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Session;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Backend extends AbstractSession {

    const NAME = 'backend';

    /**
     * @return array|null
     */
    public function getGridBookmarks( $key )
    {
        return $this->storage->getData( 'grid_bookmarks_' . $key );
    }

    /**
     * @param array $bookmarks
     * @return $this
     */
    public function setGridBookmarks( $key, array $bookmarks )
    {
        $this->storage->setData( 'grid_bookmarks_' . $key, $bookmarks );
        return $this;
    }

}
