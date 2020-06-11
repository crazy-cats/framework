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
abstract class AbstractCollection extends \CrazyCat\Framework\App\Data\Collection
{
    /**
     * @var \CrazyCat\Framework\App\Db\AbstractAdapter
     */
    protected $conn;

    /**
     * @var \CrazyCat\Framework\App\EventManager
     */
    protected $eventManager;

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
     * @var string
     */
    protected $modelName;

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
     * @var bool
     */
    protected $loaded = false;

    /**
     * @var array
     */
    protected $keyMap = [
        'eq'      => "%s = ?",
        'neq'     => "%s != ?",
        'like'    => "%s LIKE ?",
        'nlike'   => "%s NOT LIKE ?",
        'in'      => "%s IN(?)",
        'nin'     => "%s NOT IN(?)",
        'is'      => "%s IS ?",
        'notnull' => "%s IS NOT NULL",
        'null'    => "%s IS NULL",
        'gt'      => "%s > ?",
        'lt'      => "%s < ?",
        'gteq'    => "%s >= ?",
        'lteq'    => "%s <= ?",
        'finset'  => "FIND_IN_SET(?, %s)",
        'regexp'  => "%s REGEXP ?",
        'ntoa'    => "INET_NTOA(%s) LIKE ?",
    ];

    public function __construct(
        \CrazyCat\Framework\App\ObjectManager $objectManager,
        \CrazyCat\Framework\App\EventManager $eventManager,
        \CrazyCat\Framework\App\Db\Manager $dbManager
    ) {
        $this->eventManager = $eventManager;
        $this->objectManager = $objectManager;

        $this->construct();
        $this->conn = $dbManager->getConnection($this->connName);
    }

    /**
     * @param string $modelClass
     * @throws \ReflectionException
     */
    protected function init($modelClass)
    {
        $this->modelClass = $modelClass;
        $this->modelName = $this->objectManager->get($modelClass)->getModelName();
        $this->idFieldName = $this->objectManager->get($modelClass)->getIdFieldName();
        $this->mainTable = $this->objectManager->get($modelClass)->getMainTable();
    }

    /**
     * @param array $conditions
     * @return array [ sql, binds ]
     */
    protected function parseConditions($conditions)
    {
        /**
         * Structure of attribute `conditions` is like:
         *     [ [ cond1 OR cond2 ] AND [ cond3 OR cond4 ] AND [ cond5 ] ]
         */
        $txtConditions = '';
        $binds = [];
        foreach ($conditions as $conditionGroup) {
            [$andSql, $andBinds] = $this->parseConditionGroup($conditionGroup);
            $txtConditions .= ' AND ( ' . $andSql . ' )';
            $binds = array_merge($binds, $andBinds);
        }
        return [$txtConditions, $binds];
    }

    /**
     * @param string|array $field
     * @param array|null   $conditions
     * @return array [ sql, binds ]
     */
    protected function parseConditionGroup($field, $conditions = null)
    {
        $sql = '';
        $binds = [];
        if (is_array($field)) {
            foreach ($field as $orConditions) {
                [$orSql, $orBinds] = $this->parseConditionGroup(
                    $orConditions['field'],
                    $orConditions['conditions']
                );
                $sql .= ' OR ( ' . $orSql . ' )';
                $binds = array_merge($binds, $orBinds);
            }
            $sql = '( ' . ltrim($sql, ' OR ') . ' )';
        } else {
            foreach ($conditions as $symbol => $value) {
                if (in_array($symbol, ['in', 'nin'])) {
                    $mask = '';
                    foreach ($value as $val) {
                        $mask .= ', ?';
                        $binds[] = $val;
                    }
                    $sql .= sprintf(strtr($this->keyMap[$symbol], ['?' => ltrim($mask, ', ')]), ('`' . $field . '`'));
                } else {
                    $sql .= sprintf($this->keyMap[$symbol], ('`' . $field . '`'));
                    $binds[] = $value;
                }
            }
        }
        return [$sql, $binds];
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    protected function beforeLoad()
    {
        $this->eventManager->dispatch('collection_load_after', ['collection' => $this]);
        $this->eventManager->dispatch($this->modelName . '_collection_load_before', ['collection' => $this]);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    protected function afterLoad()
    {
        $this->eventManager->dispatch('collection_load_after', ['collection' => $this]);
        $this->eventManager->dispatch($this->modelName . '_collection_load_after', ['collection' => $this]);
    }

    /**
     * @param array|string $fields
     * @return $this
     */
    public function addFieldToSelect($fields)
    {
        if (!is_array($fields)) {
            $fields = [$fields];
        }
        $this->fields = array_unique(array_merge($this->fields, $fields));
        return $this;
    }

    /**
     * @param string|array $field
     * @param array|null   $conditions
     * @return $this
     */
    public function addFieldToFilter($field, $conditions = null)
    {
        if (is_array($field)) {
            $this->conditions[] = $field;
        } else {
            $this->conditions[] = [['field' => $field, 'conditions' => $conditions]];
        }
        return $this;
    }

    /**
     * @param string $field
     * @param string $dir
     * @return $this
     */
    public function addOrder($field, $dir = 'ASC')
    {
        if (isset($this->sortOrders[$field])) {
            unset($this->sortOrders[$field]);
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
    public function setPageSize($size)
    {
        $this->pageSize = (int)$size;
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
    public function setCurrentPage($page)
    {
        $this->currentPage = (int)$page;
        return $this;
    }

    /**
     * @return $this
     * @throws \ReflectionException
     */
    public function load()
    {
        if ($this->loaded) {
            return $this;
        }

        $this->beforeLoad();

        if (empty($this->fields)) {
            $fields = '*';
        } else {
            if (!in_array($this->idFieldName, $this->fields)) {
                array_unshift($this->fields, $this->idFieldName);
            }
            $fields = '`' . implode('`, `', $this->fields) . '`';
        }
        $table = $this->conn->getTableName($this->mainTable);
        [$txtConditions, $binds] = $this->parseConditions($this->conditions);
        $sortOrders = empty($this->sortOrders) ? '' : ('ORDER BY ' . implode(', ', $this->sortOrders));
        $limitation = $this->pageSize ? ('LIMIT ' . $this->pageSize * ($this->currentPage - 1) . ', ' . $this->pageSize) : '';
        $itemsData = $this->conn->fetchAll(
            sprintf(
                'SELECT %s FROM `%s` AS `main` WHERE 1=1 %s %s %s',
                $fields,
                $table,
                $txtConditions,
                $sortOrders,
                $limitation
            ),
            $binds
        );
        foreach ($itemsData as $itemData) {
            $this->items[$itemData[$this->idFieldName]] = $this->objectManager->create(
                $this->modelClass,
                ['data' => $itemData]
            );
        }

        $this->loaded = true;
        $this->afterLoad();

        return $this;
    }

    /**
     * @return \CrazyCat\Framework\App\Data\DataObject|null
     * @throws \ReflectionException
     */
    public function getItemById($id)
    {
        $this->load();
        return parent::getItemById($id);
    }

    /**
     * @return \Traversable
     * @throws \ReflectionException
     */
    public function getIterator()
    {
        $this->load();
        return parent::getIterator();
    }

    /**
     * @return int
     * @throws \ReflectionException
     */
    public function count()
    {
        $this->load();
        return parent::count();
    }

    /**
     * @return int
     */
    public function getSize()
    {
        $table = $this->conn->getTableName($this->mainTable);
        [$txtConditions, $binds] = $this->parseConditions($this->conditions);
        return (int)$this->conn->fetchOne(
            sprintf('SELECT COUNT(*) FROM `%s` AS `main` WHERE 1=1 %s', $table, $txtConditions),
            $binds
        );
    }

    /**
     * @return int[]
     */
    public function getAllIds()
    {
        $table = $this->conn->getTableName($this->mainTable);
        [$txtConditions, $binds] = $this->parseConditions($this->conditions);
        return $this->conn->fetchCol(
            sprintf('SELECT `%s` FROM `%s` AS `main` WHERE 1=1 %s', $this->idFieldName, $table, $txtConditions),
            $binds
        );
    }

    /**
     * @return \CrazyCat\Framework\App\Component\Module\Model\AbstractModel|null
     * @throws \ReflectionException
     */
    public function getFirstItem()
    {
        return $this->count() ? reset($this->items) : null;
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
     * @return string
     */
    public function getModelName()
    {
        return $this->modelName;
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function toArray()
    {
        $this->load();

        $itemArr = [];
        foreach ($this->items as $item) {
            $itemArr[] = $item->toArray();
        }

        return [
            'total'       => $this->getSize(),
            'pageSize'    => $this->pageSize,
            'currentPage' => $this->currentPage,
            'items'       => $itemArr
        ];
    }

    /**
     * @return void
     */
    abstract protected function construct();
}
