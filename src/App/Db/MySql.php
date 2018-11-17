<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Db;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class MySql extends AbstractAdapter {

    const TYPE = 'mysql';

    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * @var string
     */
    private $tblPrefix;

    public function __construct( $config )
    {
        $this->pdo = new \PDO( sprintf( 'mysql:host=%s;dbname=%s;charset=utf8', $config['host'], $config['database'] ), $config['username'], $config['password'] );

        $this->tblPrefix = $config['prefix'];
    }

    /**
     * @param string $sql
     * @param array $binds
     * @return array
     */
    public function fetchAll( $sql, array $binds = [] )
    {
        $statement = $this->pdo->prepare( $sql );
        if ( !$statement->execute( $binds ) ) {
            list(,, $errorInfo ) = $statement->errorInfo();
            throw new \Exception( $errorInfo );
        }
        return $statement->fetchAll( \PDO::FETCH_ASSOC );
    }

    /**
     * @param string $sql
     * @param array $binds
     * @return array
     */
    public function fetchPairs( $sql, array $binds = [] )
    {
        $statement = $this->pdo->prepare( $sql );
        if ( !$statement->execute( $binds ) ) {
            list(,, $errorInfo ) = $statement->errorInfo();
            throw new \Exception( $errorInfo );
        }
        $data = [];
        while ( list( $key, $value ) = $statement->fetch( \PDO::FETCH_NUM ) ) {
            $data[$key] = $value;
        }
        return $data;
    }

    /**
     * @param string $sql
     * @param array $binds
     * @return array
     */
    public function fetchCol( $sql, array $binds = [] )
    {
        $statement = $this->pdo->prepare( $sql );
        if ( !$statement->execute( $binds ) ) {
            list(,, $errorInfo ) = $statement->errorInfo();
            throw new \Exception( $errorInfo );
        }
        $data = [];
        while ( ( $row = $statement->fetchColumn() ) ) {
            $data[] = $row;
        }
        return $data;
    }

    /**
     * @param string $sql
     * @param array $binds
     * @return array
     */
    public function fetchRow( $sql, array $binds = [] )
    {
        $statement = $this->pdo->prepare( $sql );
        if ( !$statement->execute( $binds ) ) {
            list(,, $errorInfo ) = $statement->errorInfo();
            throw new \Exception( $errorInfo );
        }
        return $statement->fetch( \PDO::FETCH_ASSOC );
    }

    /**
     * @param string $sql
     * @param array $binds
     * @return string|null
     */
    public function fetchOne( $sql, array $binds = [] )
    {
        $statement = $this->pdo->prepare( $sql );
        if ( !$statement->execute( $binds ) ) {
            list(,, $errorInfo ) = $statement->errorInfo();
            throw new \Exception( $errorInfo );
        }
        return $statement->fetchColumn();
    }

    /**
     * @param string $table
     * @param array $data [ key => value ]
     * @return int
     */
    public function insert( $table, array $data )
    {
        $fields = array_keys( $data );
        $keyMarks = implode( ', ', array_map( function ( $key ) {
                    return '`' . $key . '`';
                }, $fields ) );

        $valueMarks = implode( ', ', array_map( function () {
                    return '?';
                }, $fields ) );

        $statement = $this->pdo->prepare( sprintf( 'INSERT INTO `%s` ( %s ) VALUES ( %s )', $table, $keyMarks, $valueMarks ) );
        foreach ( array_values( $data ) as $k => $value ) {
            $statement->bindValue( $k + 1, $value );
        }
        $statement->execute();

        if ( !( $id = $this->pdo->lastInsertId() ) ) {
            list(,, $errorInfo ) = $statement->errorInfo();
            throw new \Exception( $errorInfo );
        }
        return $id;
    }

    /**
     * @param string $table
     * @param array $data [ [ key => value ], [ key => value ], ... ]
     */
    public function insertArray( $table, array $data )
    {
        $fields = array_keys( $data[0] );
        $keyMarks = implode( ', ', array_map( function ( $key ) {
                    return '`' . $key . '`';
                }, $fields ) );

        $valueMarks = [];
        $numFields = count( $fields );
        for ( $i = 0; $i < count( $data ); $i ++ ) {
            $valueMarks[] = rtrim( str_repeat( '?, ', $numFields ), ', ' );
        }

        $k = 0;
        $statement = $this->pdo->prepare( sprintf( 'INSERT INTO `%s` ( %s ) VALUES ( %s )', $table, $keyMarks, implode( '), (', $valueMarks ) ) );
        foreach ( $data as $row ) {
            foreach ( $row as $value ) {
                $statement->bindValue( ++$k, $value );
            }
        }

        if ( !$statement->execute() ) {
            list(,, $errorInfo ) = $statement->errorInfo();
            throw new \Exception( $errorInfo );
        }
    }

    /**
     * @param string $table
     * @param array $data [ key => value ]
     * @param array $conditions
     */
    public function update( $table, array $data, array $conditions = [] )
    {
        $updateMarks = implode( ', ', array_map( function ( $key ) {
                    return '`' . $key . '` = ?';
                }, array_keys( $data ) ) );

        $conditionSql = '';
        $binds = array_values( $data );
        foreach ( $conditions as $condition => $bind ) {
            $conditionSql .= ' AND ' . $condition;
            if ( strpos( $condition, '?' ) !== false ) {
                $binds[] = $bind;
            }
        }
        $statement = $this->pdo->prepare( sprintf( 'UPDATE `%s` SET %s WHERE 1=1 %s', $table, $updateMarks, $conditionSql ) );

        if ( !$statement->execute( $binds ) ) {
            list(,, $errorInfo ) = $statement->errorInfo();
            throw new \Exception( $errorInfo );
        }
    }

    /**
     * @param string $table
     * @param array $conditions
     */
    public function delete( $table, array $conditions = [] )
    {
        $conditionSql = '';
        $binds = [];
        foreach ( $conditions as $condition => $bind ) {
            $conditionSql .= ' AND ' . $condition;
            if ( strpos( $condition, '?' ) !== false ) {
                $binds[] = $bind;
            }
        }
        $statement = $this->pdo->prepare( sprintf( 'DELETE FROM `%s` WHERE 1=1 %s', $table, $conditionSql ) );

        if ( !$statement->execute( $binds ) ) {
            list(,, $errorInfo ) = $statement->errorInfo();
            throw new \Exception( $errorInfo );
        }
    }

    /**
     * @param string $table
     * @param array $columns [ attribute => value ]  attributes: name, type, length, unsign, null, default
     * @param array $indexes [ 'columns' => [], 'type' => xxx, 'name' => xxx ]
     * @param array $options [ 'engine' => xxx, 'charset' => xxx ]
     */
    public function createTable( $table, array $columns, array $indexes = [], array $options = [] )
    {
        $sqlColumns = implode( ",\n", array_map( function( $column ) {
                    $name = $this->getTableName( $column['name'] );
                    $type = $column['type'];
                    $length = isset( $column['length'] ) ? $column['length'] : '';
                    $unsign = ( isset( $column['unsign'] ) && $column['unsign'] ) ? 'UNSIGNED' : '';
                    $default = isset( $column['default'] ) ? $column['default'] : 'NULL';
                    $null = ( isset( $column['null'] ) && $column['null'] ) ? ( 'DEFAULT ' . $default ) : 'NOT NULL';
                    return sprintf( '`%s` %s(%d) %s %s', $name, $type, $length, $unsign, $null );
                }, $columns ) );

        $sqlIndexes = implode( ",\n", array_map( function( $index ) {
                    $columns = implode( '`, `', $index['columns'] );
                    $type = isset( $index['type'] ) ? $index['type'] : '';
                    $name = !empty( $index['name'] ) ? $index['name'] : strtoupper( implode( '_', $index['columns'] ) );
                    return sprintf( 'ADD %s KEY `%s` ( `%s` )', $type, $name, $columns );
                }, $indexes ) );

        $engine = isset( $options['engine'] ) ? $options['engine'] : 'InnoDB';
        $charset = isset( $options['charset'] ) ? $options['charset'] : 'utf8';
        $sqlOptions = sprintf( 'ENGINE=%s DEFAULT CHARSET=%s', $engine, $charset );

        $tbl = $this->getTableName( $table );
        $sql = sprintf( "CREATE TABLE IF NOT EXISTS `%s` (\n%s\n) %s;\n", $tbl, $sqlColumns, $sqlOptions ) .
                ( empty( $sqlIndexes ) ? '' : sprintf( "ALTER TABLE `%s`\n%s;", $tbl, $sqlIndexes ) );
        $statement = $this->pdo->prepare( $sql );
        if ( !$statement->execute() ) {
            list(,, $errorInfo ) = $statement->errorInfo();
            throw new \Exception( $errorInfo );
        }
    }

    /**
     * @param string $table
     * @return string
     */
    public function getTableName( $table )
    {
        return ( $this->tblPrefix === '' ) ? $table :
                ( ( strpos( $table, $this->tblPrefix ) === 0 ) ? $table : ( $this->tblPrefix . $table) );
    }

}
