<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Component\Module\Model;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
abstract class AbstractModel extends \CrazyCat\Framework\App\Data\DataObject
{
    /**
     * @var array
     */
    protected $orgData = [];

    /**
     * @var array
     */
    protected static $mainFields = [];

    /**
     * @var \CrazyCat\Framework\App\Db\AbstractAdapter
     */
    protected $conn;

    /**
     * @var \CrazyCat\Framework\App\Db\Manager
     */
    protected $dbManager;

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
     * @var bool
     */
    protected $isNew;

    /**
     * @var string
     */
    protected $mainTable;

    /**
     * @var string
     */
    protected $modelName;

    public function __construct(
        \CrazyCat\Framework\App\EventManager $eventManager,
        \CrazyCat\Framework\App\Db\Manager $dbManager,
        array $data = []
    ) {
        $this->dbManager = $dbManager;
        $this->eventManager = $eventManager;

        $this->construct();

        parent::__construct($data);
    }

    /**
     * @param string $modelName
     * @param string $mainTable
     * @param string $idFieldName
     * @param string $connName
     * @return void
     * @throws \ReflectionException
     */
    protected function init()
    {
        [$modelName, $mainTable, $idFieldName, $connName] = array_pad(func_get_args(), 4, null);

        $this->connName = $connName ?: 'default';
        $this->idFieldName = $idFieldName ?: 'id';
        $this->modelName = $modelName;
        $this->mainTable = $mainTable;

        $this->conn = $this->dbManager->getConnection($this->connName);

        if (!isset(self::$mainFields[static::class])) {
            self::$mainFields[static::class] = $this->conn->getAllColumns($this->mainTable);
        }
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
     * @return int|null
     */
    public function getId()
    {
        return $this->getData($this->idFieldName);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    protected function beforeLoad()
    {
        $this->eventManager->dispatch('model_load_before', ['model' => $this]);
        $this->eventManager->dispatch($this->modelName . '_load_before', ['model' => $this]);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    protected function afterLoad()
    {
        $this->eventManager->dispatch('model_load_after', ['model' => $this]);
        $this->eventManager->dispatch($this->modelName . '_load_after', ['model' => $this]);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    protected function beforeSave()
    {
        if (!$this->getData($this->idFieldName)) {
            $this->isNew = true;
        }
        $this->eventManager->dispatch('model_save_before', ['model' => $this]);
        $this->eventManager->dispatch($this->modelName . '_save_before', ['model' => $this]);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    protected function afterSave()
    {
        $this->eventManager->dispatch('model_save_after', ['model' => $this]);
        $this->eventManager->dispatch($this->modelName . '_save_after', ['model' => $this]);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    protected function beforeDelete()
    {
        $this->eventManager->dispatch('model_delete_before', ['model' => $this]);
        $this->eventManager->dispatch($this->modelName . '_delete_before', ['model' => $this]);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    protected function afterDelete()
    {
        $this->eventManager->dispatch('model_delete_after', ['model' => $this]);
        $this->eventManager->dispatch($this->modelName . '_delete_after', ['model' => $this]);
    }

    /**
     * @param int|string  $id
     * @param string|null $field
     * @return $this
     * @throws \ReflectionException
     */
    public function load($id, $field = null)
    {
        $this->beforeLoad();

        $table = $this->conn->getTableName($this->mainTable);
        $fieldName = ($field === null) ? $this->idFieldName : $field;
        $this->setData($this->conn->fetchRow(sprintf('SELECT * FROM `%s` WHERE `%s` = ?', $table, $fieldName), [$id]));
        $this->orgData = $this->data;

        $this->afterLoad();

        return $this;
    }

    /**
     * @return $this
     * @throws \ReflectionException
     */
    public function save()
    {
        $this->beforeSave();

        $data = $this->getData();
        foreach (array_keys($data) as $key) {
            if (!in_array($key, self::$mainFields[static::class])) {
                unset($data[$key]);
            }
        }
        if (!empty($data[$this->idFieldName])) {
            $this->conn->update(
                $this->conn->getTableName($this->mainTable),
                $data,
                [sprintf('`%s` = ?', $this->idFieldName) => $data[$this->idFieldName]]
            );
        } else {
            $id = $this->conn->insert($this->conn->getTableName($this->mainTable), $data);
            $this->setData($this->idFieldName, $id);
        }

        $this->afterSave();

        return $this;
    }

    /**
     * @return $this
     * @throws \ReflectionException
     */
    public function delete()
    {
        if (($id = $this->getData($this->idFieldName))) {
            $this->beforeDelete();
            $this->conn->delete(
                $this->conn->getTableName($this->mainTable),
                [sprintf('`%s` = ?', $this->idFieldName) => $id]
            );
            $this->afterDelete();
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getModelName()
    {
        return $this->modelName;
    }

    abstract protected function construct();
}
