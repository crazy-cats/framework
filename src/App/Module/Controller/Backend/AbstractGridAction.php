<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Module\Controller\Backend;

use CrazyCat\Framework\App\Io\Http\Response;
use CrazyCat\Framework\App\Module\Controller\Backend\Context;
use CrazyCat\Framework\Utility\StaticVariable;
use CrazyCat\Framework\App\Module\Block\Backend\AbstractGrid;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
abstract class AbstractGridAction extends AbstractAction {

    const DEFAULT_PAGE_SIZE = 20;

    /**
     * @var \CrazyCat\Framework\App\Module\Block\Backend\AbstractGrid
     */
    protected $block;

    /**
     * @var \CrazyCat\Framework\App\Module\Model\AbstractCollection
     */
    protected $collection;

    public function __construct( Context $context )
    {
        parent::__construct( $context );

        $this->construct();
    }

    /**
     * @param string $collectionClass
     */
    protected function init( $collectionClass, $blockClass )
    {
        $this->block = $this->objectManager->create( $blockClass );
        $this->collection = $this->objectManager->create( $collectionClass );
    }

    /**
     * @param array|null $filters
     * @return array
     */
    protected function addFilters( $filters )
    {
        if ( empty( $filters ) ) {
            return [];
        }

        foreach ( $this->block->getFields() as $field ) {
            if ( empty( $field['filter']['type'] ) ) {
                continue;
            }
            switch ( $field['filter']['type'] ) {

                case AbstractGrid::FIELD_TYPE_SELECT :
                    if ( $filters[$field['name']] != StaticVariable::NO_SELECTION ) {
                        $this->collection->addFieldToFilter( $field['name'], [ $field['filter']['condition'] => $filters[$field['name']] ] );
                    }
                    break;

                case AbstractGrid::FIELD_TYPE_TEXT :
                    if ( !empty( $filter = trim( $filters[$field['name']] ) ) ) {
                        $this->collection->addFieldToFilter( $field['name'], [ $field['filter']['condition'] => ( $field['filter']['condition'] == 'like' ) ? ( '%' . $filter . '%' ) : $filter ] );
                    }
                    break;
            }
        }
    }

    /**
     * @param string|null $sorting
     * @return array
     */
    protected function addSorting( $sorting )
    {
        $sortings = $this->block->getSortings();
        if ( !empty( $sorting ) ) {
            list( $fieldName, $dir ) = explode( ',', $sorting );
            foreach ( $sortings as $k => $sorting ) {
                if ( $sorting['field'] == $fieldName ) {
                    unset( $sortings[$k] );
                    break;
                }
            }
            array_unshift( $sortings, [ 'field' => $fieldName, 'dir' => $dir ] );
        }
        foreach ( $sortings as $sorting ) {
            $this->collection->addOrder( $sorting['field'], $sorting['dir'] );
        }
        return $sortings;
    }

    /**
     * @return void
     */
    protected function run()
    {
        $this->session->setGridBookmarks( [
            AbstractGrid::BOOKMARK_FILTER => $this->addFilters( $this->request->getParam( 'filter' ) ),
            AbstractGrid::BOOKMARK_SORTING => $this->addSorting( $this->request->getParam( 'sorting' ) )
        ] );

        $this->collection->setPageSize( $this->request->getParam( 'limit' ) ?: self::DEFAULT_PAGE_SIZE  );

        if ( ( $page = $this->request->getParam( 'p' ) ) ) {
            $this->collection->setCurrentPage( $page );
        }

        $this->response->setType( Response::TYPE_JSON )->setData( $this->collection->toArray() );
    }

    /**
     * @return void
     */
    abstract protected function construct();
}
