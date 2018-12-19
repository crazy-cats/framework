<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Module\Model;

use CrazyCat\Framework\App\Db\Manager as DbManager;
use CrazyCat\Framework\App\EventManager;
use CrazyCat\Framework\App\Translator;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
abstract class AbstractLangModel extends AbstractModel {

    /**
     * @var array
     */
    static protected $langFields;

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

    public function __construct( Translator $translator, EventManager $eventManager, DbManager $dbManager, array $data = array() )
    {
        $this->translator = $translator;

        parent::__construct( $eventManager, $dbManager, $data );
    }

    /**
     * @param string $modelName
     * @param string $mainTable
     * @param string $langTable
     * @param string $idFieldName
     * @param string $connName
     * @return void
     */
    protected function init()
    {
        list( $modelName, $mainTable, $idFieldName, $connName ) = array_pad( func_get_args(), 4, null );
        parent::init( $modelName, $mainTable, $idFieldName, $connName );

        $this->langTable = $this->mainTable . '_lang';
        if ( static::$langFields === null ) {
            static::$langFields = array_diff( $this->conn->getAllColumns( $this->langTable ), [ $this->idFieldName, $this->langFieldName ] );
        }
    }

    /**
     * @return array
     */
    public function getLangFields()
    {
        return static::$langFields;
    }

    /**
     * @param int|string $id
     * @param string|null $field
     * @return $this
     */
    public function load( $id, $field = null )
    {
        $this->beforeLoad();

        $mainTable = $this->conn->getTableName( $this->mainTable );
        $langTable = $this->conn->getTableName( $this->langTable );
        $fieldName = ( $field === null ) ? $this->idFieldName : $field;
        $lang = $this->translator->getLangCode();
        $sql = 'SELECT * ' .
                'FROM `%s` AS `main` ' .
                'LEFT JOIN `%s` AS `lang` ON `lang`.`%s` = `main`.`%s` AND `lang`.`%s` = ? ' .
                'WHERE `main`.`%s` = ?';
        $this->setData( $this->conn->fetchRow( sprintf( $sql, $mainTable, $langTable, $this->idFieldName, $this->idFieldName, $this->langFieldName, $fieldName ), [ $lang, $id ] ) );

        $this->afterLoad();

        return $this;
    }

    /**
     * @return $this
     */
    public function save()
    {
        $this->beforeSave();

        $dataFields = array_keys( $this->data );
        $langFields = array_intersect( static::$langFields, $dataFields );
        $mainValues = $langValues = [];
        foreach ( $this->data as $field => $value ) {
            if ( in_array( $field, $langFields ) ) {
                $langValues[$field] = $value;
            }
            else {
                $mainValues[$field] = $value;
            }
        }

        if ( $this->getData( $this->idFieldName ) ) {
            $this->conn->update( $this->mainTable, $mainValues, [ sprintf( '`%s` = ?', $this->idFieldName ) => $this->getData( $this->idFieldName ) ] );
        }
        else {
            $id = $this->conn->insert( $this->mainTable, $mainValues );
            $this->setData( $this->idFieldName, $id );
        }

        $langValues[$this->idFieldName] = $this->getData( $this->idFieldName );
        $langValues[$this->langFieldName] = $this->translator->getLangCode();
        $this->conn->insertUpdate( $this->langTable, [ $langValues ], $langFields );

        $this->afterSave();

        return $this;
    }

    /**
     * @return $this
     */
    public function delete()
    {
        if ( ( $id = $this->getData( $this->idFieldName ) ) ) {
            $this->beforeDelete();
            $this->conn->delete( $this->mainTable, [ sprintf( '`%s` = ?', $this->idFieldName ) => $id ] );
            $this->conn->delete( $this->langTable, [ sprintf( '`%s` = ?', $this->idFieldName ) => $id ] );
            $this->afterDelete();
        }

        return $this;
    }

}
