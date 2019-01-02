<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Module\Model;

use CrazyCat\Framework\App\Db\Manager as DbManager;
use CrazyCat\Framework\App\EventManager;
use CrazyCat\Framework\App\ObjectManager;
use CrazyCat\Framework\App\Translator;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
abstract class AbstractLangCollection extends AbstractCollection {

    /**
     * @var \CrazyCat\Framework\App\Translator
     */
    protected $translator;

    /**
     * @var string
     */
    protected $langFieldName = 'lang';

    /**
     * @var string
     */
    protected $langTable;

    /**
     * @var array
     */
    protected $langFields;

    public function __construct( Translator $translator, ObjectManager $objectManager, EventManager $eventManager, DbManager $dbManager )
    {
        $this->translator = $translator;

        parent::__construct( $objectManager, $eventManager, $dbManager );
    }

    /**
     * @param string $modelClassName
     */
    protected function init( $modelClassName )
    {
        parent::init( $modelClassName );

        $this->langTable = $this->mainTable . '_lang';
        $this->langFields = $this->objectManager->get( $modelClassName )->getLangFields();
    }

    /**
     * @param string|array $field
     * @param array|null $conditions
     * @return array [ sql, binds ]
     */
    protected function parseConditions( $field, $conditions = null )
    {
        $sql = '';
        $binds = [];
        if ( is_array( $field ) ) {
            foreach ( $field as $orConditions ) {
                list( $orSql, $orBinds ) = $this->parseConditions( $orConditions['field'], $orConditions['conditions'] );
                $sql .= ' OR ( ' . $orSql . ' )';
                $binds = array_merge( $binds, $orBinds );
            }
            $sql = '( ' . ltrim( $sql, ' OR ' ) . ' )';
        }
        else {
            foreach ( $conditions as $symbol => $value ) {
                if ( in_array( $symbol, [ 'in', 'nin' ] ) ) {
                    $mask = '';
                    foreach ( $value as $val ) {
                        $mask .= ', ?';
                        $binds[] = $val;
                    }
                    $sql .= sprintf( strtr( $this->keyMap[$symbol], [ '?' => ltrim( $mask, ', ' ) ] ), ( $this->idFieldName ? ( '`main`.`' . $field . '`' ) : ( '`' . $field . '`' ) ) );
                }
                else {
                    $sql .= sprintf( $this->keyMap[$symbol], ( $this->idFieldName ? ( '`main`.`' . $field . '`' ) : ( '`' . $field . '`' ) ) );
                    $binds[] = $value;
                }
            }
        }
        return [ $sql, $binds ];
    }

    /**
     * @return $this
     */
    public function load()
    {
        if ( $this->loaded ) {
            return $this;
        }

        $this->beforeLoad();

        if ( empty( $this->fields ) ) {
            $fields = '`main`.*, `lang`.`' . implode( '`, `lang`.`', $this->langFields ) . '`';
        }
        else {
            if ( !in_array( $this->idFieldName, $this->fields ) ) {
                array_unshift( $this->fields, $this->idFieldName );
            }
            $fields = '`' . implode( '`, `', $this->fields ) . '`';
        }
        $maintable = $this->conn->getTableName( $this->mainTable );
        $langTable = $this->conn->getTableName( $this->langTable );
        /**
         * Structure of attribute `conditions` is like:
         *     [ [ cond1 OR cond2 ] AND [ cond3 OR cond4 ] AND [ cond5 ] ]
         */
        $txtConditions = '';
        $binds = [ $this->translator->getLangCode() ];
        foreach ( $this->conditions as $conditionGroup ) {
            list( $andSql, $andBinds ) = $this->parseConditions( $conditionGroup );
            $txtConditions .= ' AND ( ' . $andSql . ' )';
            $binds = array_merge( $binds, $andBinds );
        }
        $sortOrders = empty( $this->sortOrders ) ? '' : ( 'ORDER BY ' . implode( ', ', $this->sortOrders ) );
        $limitation = $this->pageSize ? ( 'LIMIT ' . $this->pageSize * ( $this->currentPage - 1 ) . ', ' . $this->pageSize ) : '';
        $sql = 'SELECT %s ' .
                'FROM `%s` AS `main` ' .
                'LEFT JOIN `%s` AS `lang` ON `lang`.`%s` = `main`.`%s` AND `lang`.`%s` = ? ' .
                'WHERE 1=1 %s %s %s';
        foreach ( $this->conn->fetchAll( sprintf( $sql, $fields, $maintable, $langTable, $this->idFieldName, $this->idFieldName, $this->langFieldName, $txtConditions, $sortOrders, $limitation ), $binds ) as $itemData ) {
            $this->items[$itemData[$this->idFieldName]] = $this->objectManager->create( $this->modelClass, [ 'data' => $itemData ] );
        }

        $this->loaded = true;
        $this->afterLoad();

        return $this;
    }

}
