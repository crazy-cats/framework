<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Db;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
class MySql extends AbstractAdapter
{
    public const TYPE = 'mysql';

    /**
     * Column types
     */
    public const COL_TYPE_INT = 'int';
    public const COL_TYPE_TINYINT = 'tinyint';
    public const COL_TYPE_DOUBLE = 'double';
    public const COL_TYPE_VARCHAR = 'varchar';
    public const COL_TYPE_TEXT = 'text';
    public const COL_TYPE_MEDIUMTEXT = 'mediumtext';
    public const COL_TYPE_DATETIME = 'datetime';

    /**
     * Index types
     */
    public const INDEX_NORMAL = '';
    public const INDEX_PRIMARY = 'PRIMARY';
    public const INDEX_UNIQUE = 'UNIQUE';
    public const INDEX_FULLTEXT = 'FULLTEXT';

    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * @var string
     */
    private $tblPrefix;

    public function __construct($config)
    {
        $this->pdo = new \PDO(
            sprintf('mysql:host=%s;dbname=%s;charset=utf8', $config['host'], $config['database']),
            $config['username'],
            $config['password']
        );

        $this->tblPrefix = $config['prefix'];
    }

    /**
     * @param string $sql
     * @param array  $binds
     * @return array
     * @throws \Exception
     */
    public function fetchAll($sql, array $binds = [])
    {
        $statement = $this->pdo->prepare($sql, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
        if (!$statement->execute($binds)) {
            [, , $errorInfo] = $statement->errorInfo();
            throw new \Exception(sprintf("%s, SQL is:\n%s", $errorInfo, $sql));
        }
        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param string $sql
     * @param array  $binds
     * @return array
     * @throws \Exception
     */
    public function fetchPairs($sql, array $binds = [])
    {
        $statement = $this->pdo->prepare($sql, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
        if (!$statement->execute($binds)) {
            [, , $errorInfo] = $statement->errorInfo();
            throw new \Exception(sprintf("%s, SQL is:\n%s", $errorInfo, $sql));
        }
        $data = [];
        while (list($key, $value) = $statement->fetch(\PDO::FETCH_NUM)) {
            $data[$key] = $value;
        }
        return $data;
    }

    /**
     * @param string $sql
     * @param array  $binds
     * @return array
     * @throws \Exception
     */
    public function fetchCol($sql, array $binds = [])
    {
        $statement = $this->pdo->prepare($sql, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
        if (!$statement->execute($binds)) {
            [, , $errorInfo] = $statement->errorInfo();
            throw new \Exception(sprintf("%s, SQL is:\n%s", $errorInfo, $sql));
        }
        $data = [];
        while (($row = $statement->fetchColumn())) {
            $data[] = $row;
        }
        return $data;
    }

    /**
     * @param string $sql
     * @param array  $binds
     * @return array
     * @throws \Exception
     */
    public function fetchRow($sql, array $binds = [])
    {
        $statement = $this->pdo->prepare($sql, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
        if (!$statement->execute($binds)) {
            [, , $errorInfo] = $statement->errorInfo();
            throw new \Exception(sprintf("%s, SQL is:\n%s", $errorInfo, $sql));
        }
        return $statement->fetch(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * @param string $sql
     * @param array  $binds
     * @return string|null
     * @throws \Exception
     */
    public function fetchOne($sql, array $binds = [])
    {
        $statement = $this->pdo->prepare($sql, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
        if (!$statement->execute($binds)) {
            [, , $errorInfo] = $statement->errorInfo();
            throw new \Exception(sprintf("%s, SQL is:\n%s", $errorInfo, $sql));
        }
        return $statement->fetchColumn();
    }

    /**
     * @param string $table
     * @param array  $data [ key => value ]
     * @return int
     * @throws \Exception
     */
    public function insert($table, array $data)
    {
        $fields = array_keys($data);
        $keyMarks = implode(
            ', ',
            array_map(
                function ($key) {
                    return '`' . $key . '`';
                },
                $fields
            )
        );

        $valueMarks = implode(
            ', ',
            array_map(
                function () {
                    return '?';
                },
                $fields
            )
        );

        $sql = sprintf('INSERT INTO `%s` ( %s ) VALUES ( %s )', $this->getTableName($table), $keyMarks, $valueMarks);
        $statement = $this->pdo->prepare($sql);
        foreach (array_values($data) as $k => $value) {
            $statement->bindValue($k + 1, $value);
        }
        $statement->execute();

        [, , $errorInfo] = $statement->errorInfo();
        if ($errorInfo) {
            throw new \Exception(sprintf("%s, SQL is:\n%s", $errorInfo, $sql));
        }
        return $this->pdo->lastInsertId();
    }

    /**
     * @param array $data
     * @return array
     */
    private function processKeyValue(array $data)
    {
        $fields = array_keys($data[0]);
        $keyMarks = array_map(
            function ($key) {
                return '`' . $key . '`';
            },
            $fields
        );

        $valueMarks = [];
        $numFields = count($fields);
        for ($i = 0; $i < count($data); $i++) {
            $valueMarks[] = rtrim(str_repeat('?, ', $numFields), ', ');
        }

        return [$keyMarks, $valueMarks];
    }

    /**
     * @param string $table
     * @param array  $data [ [ key => value ], [ key => value ], ... ]
     * @throws \Exception
     */
    public function insertArray($table, array $data)
    {
        if (!isset($data[0])) {
            $data = array_values($data);
        }
        [$keyMarks, $valueMarks] = $this->processKeyValue($data);

        $k = 0;
        $sql = sprintf(
            'INSERT INTO `%s` ( %s ) VALUES ( %s )',
            $this->getTableName($table),
            implode(', ', $keyMarks),
            implode(' ), ( ', $valueMarks)
        );
        $statement = $this->pdo->prepare($sql);
        foreach ($data as $row) {
            foreach ($row as $value) {
                $statement->bindValue(++$k, $value);
            }
        }

        if (!$statement->execute()) {
            [, , $errorInfo] = $statement->errorInfo();
            throw new \Exception(sprintf("%s, SQL is:\n%s", $errorInfo, $sql));
        }
    }

    /**
     * @param string $table
     * @param array  $data [ [ key => value ], [ key => value ], ... ]
     * @param array  $updateFields
     * @return void
     * @throws \Exception
     */
    public function insertUpdate($table, array $data, $updateFields)
    {
        if (!isset($data[0])) {
            $data = array_values($data);
        }
        [$keyMarks, $valueMarks] = $this->processKeyValue($data);

        $keyMarksTxt = implode(', ', $keyMarks);
        $valueMarksTxt = implode('), (', $valueMarks);
        $updateMarksTxt = implode(
            ', ',
            array_map(
                function ($key) {
                    return '`' . $key . '` = VALUES( `' . $key . '` )';
                },
                $updateFields
            )
        );

        $k = 0;
        $sql = sprintf(
            'INSERT INTO `%s` ( %s ) VALUES ( %s ) ON DUPLICATE KEY UPDATE %s',
            $this->getTableName($table),
            $keyMarksTxt,
            $valueMarksTxt,
            $updateMarksTxt
        );
        $statement = $this->pdo->prepare($sql);
        foreach ($data as $row) {
            foreach ($row as $value) {
                $statement->bindValue(++$k, $value);
            }
        }

        if (!$statement->execute()) {
            [, , $errorInfo] = $statement->errorInfo();
            throw new \Exception(sprintf("%s, SQL is:\n%s", $errorInfo, $sql));
        }
    }

    /**
     * @param string $table
     * @param array  $data [ key => value ]
     * @param array  $conditions
     * @throws \Exception
     */
    public function update($table, array $data, array $conditions = [])
    {
        $updateMarks = implode(
            ', ',
            array_map(
                function ($key) {
                    return '`' . $key . '` = ?';
                },
                array_keys($data)
            )
        );

        $conditionSql = '';
        $binds = array_values($data);
        foreach ($conditions as $condition => $bind) {
            $conditionSql .= ' AND ' . $condition;
            if (strpos($condition, '?') !== false) {
                $binds[] = $bind;
            }
        }

        $sql = sprintf('UPDATE `%s` SET %s WHERE 1=1 %s', $this->getTableName($table), $updateMarks, $conditionSql);
        $statement = $this->pdo->prepare($sql);
        if (!$statement->execute($binds)) {
            [, , $errorInfo] = $statement->errorInfo();
            throw new \Exception(sprintf("%s, SQL is:\n%s", $errorInfo, $sql));
        }
    }

    /**
     * @param string $table
     * @param array  $conditions
     * @throws \Exception
     */
    public function delete($table, array $conditions = [])
    {
        $conditionSql = '';
        $binds = [];
        foreach ($conditions as $condition => $bind) {
            $conditionSql .= ' AND ' . $condition;
            if (strpos($condition, '?') !== false) {
                $binds[] = $bind;
            }
        }

        $sql = sprintf('DELETE FROM `%s` WHERE 1=1 %s', $this->getTableName($table), $conditionSql);
        $statement = $this->pdo->prepare($sql);
        if (!$statement->execute($binds)) {
            [, , $errorInfo] = $statement->errorInfo();
            throw new \Exception(sprintf("%s, SQL is:\n%s", $errorInfo, $sql));
        }
    }

    /**
     * @param string $table
     * @param array  $columns [ [ attribute => value ] ] attributes: name, type, length, unsign, null, default, auto_increment
     * @param array  $indexes [ 'columns' => [], 'type' => xxx, 'name' => xxx ]
     * @param array  $options [ 'engine' => xxx, 'charset' => xxx ]
     * @return void
     * @throws \Exception
     */
    public function createTable($table, array $columns, array $indexes = [], array $options = [])
    {
        $tbl = $this->getTableName($table);

        $sqlColumns = implode(
            ",\n",
            array_map(
                function ($column) {
                    $name = $column['name'];
                    $type = $column['type'];
                    $length = isset($column['length']) ? ('(' . $column['length'] . ')') : '';
                    $unsign = (isset($column['unsign']) && $column['unsign']) ? 'UNSIGNED' : '';
                    $null = (isset($column['null']) && !$column['null']) ? 'NOT NULL' : '';
                    $default = isset($column['default']) ? sprintf('DEFAULT \'%s\'', $column['default']) : ($null);
                    $autoIncrement = (isset($column['auto_increment']) && $column['auto_increment']) ? sprintf(
                        'AUTO_INCREMENT, PRIMARY KEY (`%s`)',
                        $name
                    ) : '';
                    return sprintf('`%s` %s%s %s %s %s', $name, $type, $length, $unsign, $default, $autoIncrement);
                },
                $columns
            )
        );
        $engine = isset($options['engine']) ? $options['engine'] : 'InnoDB';
        $charset = isset($options['charset']) ? $options['charset'] : 'utf8';
        $sqlOptions = sprintf('ENGINE=%s DEFAULT CHARSET=%s', $engine, $charset);
        $sql = sprintf("CREATE TABLE IF NOT EXISTS `%s` (\n%s\n) %s;", $tbl, $sqlColumns, $sqlOptions);

        if (!empty($indexes)) {
            $sqlIndexes = array_map(
                function ($index) {
                    $columns = implode('`, `', $index['columns']);
                    $type = isset($index['type']) ? $index['type'] : '';
                    $name = !empty($index['name']) ? $index['name'] : strtoupper(implode('_', $index['columns']));
                    return sprintf('ADD %s KEY `%s` ( `%s` )', $type, $name, $columns);
                },
                $indexes
            );
            foreach ($sqlIndexes as $sqlIndex) {
                $sql .= sprintf("\nALTER TABLE `%s` %s;", $tbl, $sqlIndex);
            }
        }

        $statement = $this->pdo->prepare($sql);
        if (!$statement->execute()) {
            [, , $errorInfo] = $statement->errorInfo();
            throw new \Exception(sprintf("%s, SQL is:\n%s", $errorInfo, $sql));
        }
    }

    /**
     * @param string $table
     * @param array  $column [ attribute => value ]  attributes: name, type, length, unsign, null, default, auto_increment
     * @return void
     * @throws \Exception
     */
    public function addColumn($table, $column)
    {
        $name = $column['name'];
        $type = $column['type'];
        $length = isset($column['length']) ? $column['length'] : '';
        $unsign = (isset($column['unsign']) && $column['unsign']) ? 'UNSIGNED' : '';
        $default = isset($column['default']) ? $column['default'] : 'NULL';
        $null = ((isset($column['null']) && $column['null']) || !isset($column['null'])) ? ('DEFAULT ' . $default) : 'NOT NULL';
        $comment = (!empty($column['comment'])) ? sprintf('COMMENT \'%s\'', $column['comment']) : '';

        $sql = sprintf(
            'ALTER TABLE `%s` ADD `%s` %s(%d) %s %s %s %s;',
            $this->getTableName($table),
            $name,
            $type,
            $length,
            $unsign,
            $null,
            $comment
        );
        $statement = $this->pdo->prepare($sql);
        if (!$statement->execute()) {
            [, , $errorInfo] = $statement->errorInfo();
            throw new \Exception(sprintf("%s, SQL is:\n%s", $errorInfo, $sql));
        }
    }

    /**
     * @param string $table
     * @return array
     * @throws \Exception
     */
    public function getAllColumns($table)
    {
        return $this->fetchCol(sprintf('SHOW COLUMNS FROM `%s`;', $this->getTableName($table)));
    }

    /**
     * @param string $table
     * @return string
     */
    public function getTableName($table)
    {
        return ($this->tblPrefix === '') ? $table :
            ((strpos($table, $this->tblPrefix) === 0) ? $table : ($this->tblPrefix . $table));
    }

    /**
     * @return void
     */
    public function beginTransaction()
    {
        $this->pdo->beginTransaction();
    }

    /**
     * @return bool
     */
    public function commitTransaction()
    {
        return $this->pdo->commit();
    }

    /**
     * @return void
     */
    public function rollbackTransaction()
    {
        $this->pdo->rollBack();
    }
}
