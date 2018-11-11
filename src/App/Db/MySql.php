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

    public function __construct( $config )
    {
        $this->pdo = new \PDO( sprintf( 'mysql:host=%s;dbname=%s;charset=utf8', $config['host'], $config['database'] ), $config['username'], $config['password'] );
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
     * @param array $data
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
     * @param array $data
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

}
