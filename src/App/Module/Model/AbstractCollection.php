<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Module\Model;

use CrazyCat\Framework\App\Db\Manager as DbManager;
use CrazyCat\Framework\App\ObjectManager;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
abstract class AbstractCollection extends \CrazyCat\Framework\Data\Collection {

    /**
     * @var \CrazyCat\Framework\App\Db\AbstractAdapter
     */
    protected $conn;

    /**
     * @var \CrazyCat\Framework\App\ObjectManager
     */
    protected $objectManager;

    /**
     * @var string
     */
    protected $connName = 'default';

    /**
     * @var string
     */
    protected $idFieldName;

    /**
     * @var string
     */
    protected $mainTable;

    /**
     * @var string
     */
    protected $modelClass;

    /**
     * @var string[]
     */
    protected $fields = [];

    /**
     * @var array
     */
    protected $conditions = [];

    /**
     * @var array
     */
    protected $sortOrders = [];

    /**
     * @var int
     */
    protected $currentPage = 1;

    /**
     * @var int
     */
    protected $pageSize;

    /**
     * @var array
     */
    protected $keyMap = [
        'eq' => "`%s` = ?",
        'neq' => "`%s` != ?",
        'like' => "`%s` LIKE ?",
        'nlike' => "`%s` NOT LIKE ?",
        'in' => "`%s` IN(?)",
        'nin' => "`%s` NOT IN(?)",
        'is' => "`%s` IS ?",
        'notnull' => "`%s` IS NOT NULL",
        'null' => "`%s` IS NULL",
        'gt' => "`%s` > ?",
        'lt' => "`%s` < ?",
        'gteq' => "`%s` >= ?",
        'lteq' => "`%s` <= ?",
        'finset' => "FIND_IN_SET(?, `%s`)",
        'regexp' => "`%s` REGEXP ?",
        'ntoa' => "INET_NTOA(`%s`) LIKE ?",
    ];

    public function __construct( ObjectManager $objectManager, DbManager $dbManager )
    {
        $this->objectManager = $objectManager;
        $this->construct();
        $this->conn = $dbManager->getConnection( $this->connName );
    }

    /**
     * @param string $modelClass
     */
    protected function init( $modelClass )
    {
        $this->modelClass = $modelClass;
        $this->idFieldName = $this->objectManager->get( $modelClass )->getIdFieldName();
        $this->mainTable = $this->objectManager->get( $modelClass )->getMainTable();
    }

    /**
     * @param string|array $field
     * @param array|null $andConditions
     * @return array [ sql, binds ]
     */
    protected function parseConditions( $field, $andConditions = null )
    {
        $sql = '';
        $binds = [];
        if ( is_array( $field ) ) {
            foreach ( $field as $orConditions ) {
                list( $orSql, $orBinds ) = $this->parseConditions( $orConditions['field'], $orConditions['conditions'] );
                $sql .= ' OR ( ' . $orSql . ' )';
                $binds = array_merge( $binds, $orBinds );
            }
            $sql = '( ' . ltrim( ' OR ' ) . ' )';
        }
        foreach ( $andConditions as $symbol => $value ) {
            $sql .= sprintf( $this->keyMap[$symbol], $field );
            $binds[] = $value;
        }
        return [ $sql, $binds ];
    }

    /**
     * @param array|string $fields
     * @return $this
     */
    public function addFieldToSelect( $fields )
    {
        if ( !is_array( $fields ) ) {
            $fields = [ $fields ];
        }
        $this->fields = array_unique( array_merge( $this->fields, $fields ) );
        return $this;
    }

    /**
     * @param string $field
     * @param array $condition
     * @return $this
     */
    public function addFieldToFilter( $field, $condition )
    {
        if ( !isset( $this->conditions[$field] ) ) {
            $this->conditions[$field] = [];
        }
        foreach ( $condition as $a => $value ) {
            $this->conditions[$field][$a] = $value;
        }
        return $this;
    }

    /**
     * @param string $field
     * @param array $dir
     * @return $this
     */
    public function addOrder( $field, $dir = 'ASC' )
    {
        if ( isset( $this->sortOrders[$field] ) ) {
            unset( $this->sortOrders[$field] );
        }
        $this->sortOrders[$field] = $field . ' ' . $dir;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * @param int $size
     * @return $this
     */
    public function setPageSize( $size )
    {
        $this->pageSize = $size;
        return $this;
    }

    /**
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * @param int $page
     * @return $this
     */
    public function setCurrentPage( $page )
    {
        $this->currentPage = $page;
        return $this;
    }

    /**
     * @return $this
     */
    public function load()
    {
        $fields = empty( $this->fields ) ? '*' : ( '`' . implode( '`, `', $this->fields ) . '`' );
        $table = $this->conn->getTableName( $this->mainTable );
        $txtConditions = '';
        $binds = [];
        foreach ( $this->conditions as $field => $conditions ) {
            list( $andSql, $andBinds ) = $this->parseConditions( $field, $conditions );
            $txtConditions .= ' AND ' . $andSql;
            $binds = array_merge( $binds, $andBinds );
        }
        $sortOrders = empty( $this->sortOrders ) ? '' : implode( ', ', sortOrders );
        $limitation = $this->pageSize ? ( $this->pageSize * ( $this->currentPage - 1 ) . ', ' . $this->pageSize ) : '';
        foreach ( $this->conn->fetchAll( sprintf( 'SELECT `%s`, %s FROM `%s` WHERE 1=1 `%s` %s %s', $this->idFieldName, $fields, $table, $conditions, $sortOrders, $limitation ), $binds ) as $itemData ) {
            $this->items[$itemData[$this->idFieldName]] = $this->objectManager->create( $this->modelClass, [ 'data' => $itemData ] );
        }
        return $this;
    }

    abstract protected function construct();
}
