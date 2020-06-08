<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Component\Module\Model;

use CrazyCat\Framework\App\Config;
use CrazyCat\Framework\App\Db\Manager as DbManager;
use CrazyCat\Framework\App\EventManager;
use CrazyCat\Framework\App\ObjectManager;
use CrazyCat\Framework\App\Component\Language\Translator;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
abstract class AbstractLangModel extends AbstractModel
{
    /**
     * @var array
     */
    protected static $langFields = [];

    /**
     * @var \CrazyCat\Framework\App\Component\Language\Translator
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

    public function __construct(
        Translator $translator,
        EventManager $eventManager,
        DbManager $dbManager,
        array $data = []
    ) {
        $this->translator = $translator;

        parent::__construct($eventManager, $dbManager, $data);
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
        list($modelName, $mainTable, $idFieldName, $connName) = array_pad(func_get_args(), 4, null);
        parent::init($modelName, $mainTable, $idFieldName, $connName);

        $this->langTable = $this->mainTable . '_lang';

        if (!isset(self::$langFields[static::class])) {
            self::$langFields[static::class] = [];
            foreach ($this->conn->getAllColumns($this->langTable) as $field) {
                if ($field == $this->idFieldName) {
                    continue;
                }
                self::$langFields[static::class][] = $field;
            }
        }
    }

    /**
     * @return array
     */
    public function getLangFields()
    {
        return self::$langFields[static::class];
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

        $tmp = array_map(
            function ($field) {
                return 'IFNULL( `lang`.`' . $field . '`, `defLang`.`' . $field . '` ) AS `' . $field . '`';
            },
            self::$langFields[static::class]
        );
        $fieldsSql = '`main`.*, ' . implode(', ', $tmp);

        $mainTable = $this->conn->getTableName($this->mainTable);
        $langTable = $this->conn->getTableName($this->langTable);
        $fieldName = ($field === null) ? $this->idFieldName : $field;

        $langCode = $this->translator->getLangCode();

        $config = ObjectManager::getInstance()->get(Config::class);
        $defLangCode = $config->getValue('general/default_languages') ?: $config->getValue('lang');

        $sql = 'SELECT %s ' .
            'FROM `%s` AS `main` ' .
            'LEFT JOIN `%s` AS `lang` ON `lang`.`%s` = `main`.`%s` AND `lang`.`%s` = ? ' .
            'LEFT JOIN `%s` AS `defLang` ON `defLang`.`%s` = `main`.`%s` AND `defLang`.`%s` = ? ' .
            'WHERE `main`.`%s` = ?';
        $this->setData(
            $this->conn->fetchRow(
                sprintf(
                    $sql,
                    $fieldsSql,
                    $mainTable,
                    $langTable,
                    $this->idFieldName,
                    $this->idFieldName,
                    $this->langFieldName,
                    $langTable,
                    $this->idFieldName,
                    $this->idFieldName,
                    $this->langFieldName,
                    $fieldName
                ),
                [$langCode, $defLangCode, $id]
            )
        );

        $this->afterLoad();

        return $this;
    }

    /**
     * @return $this
     */
    public function save()
    {
        $this->beforeSave();

        $data = $this->getData();
        $dataFields = array_keys($data);
        $langFields = array_intersect(self::$langFields[static::class], $dataFields);
        $mainValues = $langValues = [];
        foreach ($data as $field => $value) {
            if (in_array($field, $langFields)) {
                $langValues[$field] = $value;
            } elseif (in_array($field, self::$mainFields[static::class])) {
                $mainValues[$field] = $value;
            }
        }

        if (!empty($data[$this->idFieldName])) {
            $this->conn->update(
                $this->mainTable,
                $mainValues,
                [sprintf('`%s` = ?', $this->idFieldName) => $data[$this->idFieldName]]
            );
        } else {
            $id = $this->conn->insert($this->mainTable, $mainValues);
            $this->setData($this->idFieldName, $id);
        }

        $langValues[$this->idFieldName] = $this->getData($this->idFieldName);
        $langValues[$this->langFieldName] = $this->translator->getLangCode();
        $this->conn->insertUpdate($this->langTable, [$langValues], $langFields);

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
            $this->conn->delete($this->mainTable, [sprintf('`%s` = ?', $this->idFieldName) => $id]);
            $this->conn->delete($this->langTable, [sprintf('`%s` = ?', $this->idFieldName) => $id]);
            $this->afterDelete();
        }

        return $this;
    }
}
