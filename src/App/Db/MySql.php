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
        $statement->execute( $binds );
        return $statement->fetchAll();
    }

    /**
     * @param string $sql
     * @param array $binds
     * @return array
     */
    public function fetchPairs( $sql, array $binds = [] )
    {
        $statement = $this->pdo->prepare( $sql );
        $statement->execute( $binds );
        $data = [];
        foreach ( $statement->fetch( FETCH_ASSOC ) as $key => $value ) {
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
        $statement->execute( $binds );
        $data = [];
        while ( ( $row = $statement->fetchColumn( FETCH_ASSOC ) ) ) {
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
        $statement->execute( $binds );
        return $statement->fetch( FETCH_ASSOC );
    }

    /**
     * @param string $sql
     * @param array $binds
     * @return string|null
     */
    public function fetchOne( $sql, array $binds = [] )
    {
        $statement = $this->pdo->prepare( $sql );
        $statement->execute( $binds );
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

        $statement = $this->pdo->prepare( 'INSERT INTO `' . $table . '` ( ' . $keyMarks . ' ) VALUES ( ' . $valueMarks . ' )' );
        foreach ( array_values( $data ) as $k => $value ) {
            $statement->bindValue( $k + 1, $value );
        }
        $statement->execute();

        return $this->pdo->lastInsertId();
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
        $statement = $this->pdo->prepare( 'INSERT INTO `' . $table . '` ( ' . $keyMarks . ' ) VALUES ( ' . implode( '), (', $valueMarks ) . ' )' );
        foreach ( $data as $row ) {
            foreach ( $row as $value ) {
                $statement->bindValue( ++$k, $value );
            }
        }
        $statement->execute();
    }

}
