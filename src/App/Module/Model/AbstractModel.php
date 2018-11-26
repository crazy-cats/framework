<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Module\Model;

use CrazyCat\Framework\App\Db\Manager as DbManager;
use CrazyCat\Framework\App\EventManager;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
abstract class AbstractModel extends \CrazyCat\Framework\Data\Object {

    /**
     * @var \CrazyCat\Framework\App\Db\AbstractAdapter
     */
    protected $conn;

    /**
     * @var \CrazyCat\Framework\App\EventManager
     */
    protected $eventManager;

    /**
     * @var string
     */
    protected $connName;

    /**
     * @var string
     */
    protected $idFieldName;

    /**
     * @var string
     */
    protected $mainTable;

    public function __construct( EventManager $eventManager, DbManager $dbManager )
    {
        $this->construct();
        $this->conn = $dbManager->getConnection( $this->connName );
        $this->eventManager = $eventManager;

        parent::__construct();
    }

    /**
     * @param string $mainTable
     * @param string $connName
     */
    protected function init( $mainTable, $idFieldName, $connName = 'default' )
    {
        $this->connName = $connName;
        $this->mainTable = $mainTable;
        $this->idFieldName = $idFieldName;
    }

    /**
     * @return string
     */
    public function getIdFieldName()
    {
        return $this->idFieldName;
    }

    /**
     * @return string
     */
    public function getMainTable()
    {
        return $this->mainTable;
    }

    /**
     * @param int|string $id
     * @param string|null $field
     * @return $this
     */
    public function load( $id, $field = null )
    {
        $table = $this->conn->getTableName( $this->mainTable );
        $fieldName = ( $field === null ) ? $this->idFieldName : $field;
        return $this->setData( $this->conn->fetchRow( sprintf( 'SELECT * FROM `%s` WHERE `%s` = ?', $table, $fieldName ), [ $id ] ) );
    }

    /**
     * @return $this
     */
    public function save()
    {
        $this->eventManager->dispatch( 'model_save_before', [ 'model' => $this ] );

        if ( $this->getData( $this->idFieldName ) ) {
            $this->conn->update( $this->conn->getTableName( $this->mainTable ), $this->getData(), [ sprintf( '`%s` = ?', $this->idFieldName ) => $this->getData( $this->idFieldName ) ] );
        }
        else {
            $id = $this->conn->insert( $this->conn->getTableName( $this->mainTable ), $this->getData() );
            $this->setData( $this->idFieldName, $id );
        }

        $this->eventManager->dispatch( 'model_save_after', [ 'model' => $this ] );

        return $this;
    }

    abstract protected function construct();
}
