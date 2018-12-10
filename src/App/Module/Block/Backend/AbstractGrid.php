<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Module\Block\Backend;

use CrazyCat\Framework\App\Session\Backend as Session;
use CrazyCat\Framework\App\Theme\Block\Context;

/**
 * @category CrazyCat
 * @package CrazyCat\Index
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
abstract class AbstractGrid extends \CrazyCat\Framework\App\Module\Block\AbstractBlock {

    const BOOKMARK_FILTER = 'filter';
    const BOOKMARK_SORTING = 'sorting';

    /**
     * field types
     */
    const FIELD_TYPE_SELECT = 'select';
    const FIELD_TYPE_TEXT = 'text';

    protected $template = 'CrazyCat\Index::grid';

    /**
     * @var \CrazyCat\Framework\App\Session\Backend
     */
    protected $session;

    /**
     * @var array
     */
    protected $bookmarks;

    public function __construct( Session $session, Context $context, array $data = array() )
    {
        parent::__construct( $context, $data );

        $this->session = $session;
    }

    /**
     * @return array
     */
    public function getBookmarks()
    {
        if ( $this->bookmarks === null ) {
            $this->bookmarks = $this->session->getGridBookmarks( static::BOOKMARK_KEY ) ?:
                    [ self::BOOKMARK_FILTER => [], self::BOOKMARK_SORTING => [] ];
        }
        return $this->bookmarks;
    }

    /**
     * @param array $bookmarks
     * @return $this
     */
    public function setBookmarks( array $bookmarks )
    {
        $this->session->setGridBookmarks( static::BOOKMARK_KEY, $bookmarks );
        return $this;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return $this->getBookmarks()[self::BOOKMARK_FILTER];
    }

    /**
     * @return array
     */
    public function getSortings()
    {
        return $this->getBookmarks()[self::BOOKMARK_SORTING];
    }

    /**
     * @param string $fieldName
     * @return array|null
     */
    public function getSorting( $fieldName )
    {
        foreach ( $this->getBookmarks()[self::BOOKMARK_SORTING] as $sorting ) {
            if ( $sorting['field'] == $fieldName ) {
                return $sorting;
            }
        }
        return null;
    }

    /**
     * Return array structure is like:
     * [
     *     [
     *         'name' => string,
     *         'label' => string,
     *         'sort' => boolean,
     *         'filter' => [
     *             'type' => string,
     *             'options' => array,
     *             'condition' => string
     *         ]
     *     ]
     * ]
     *
     * @return array
     */
    abstract public function getFields();

    /**
     * @return string
     */
    abstract public function getSourceUrl();
}
