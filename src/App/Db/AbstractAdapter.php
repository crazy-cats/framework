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
abstract class AbstractAdapter {

    /**
     * @param string $sql
     * @param array $binds
     * @return array
     */
    abstract public function fetchAll( $sql, array $binds = [] );

    /**
     * @param string $sql
     * @param array $binds
     * @return array
     */
    abstract public function fetchPairs( $sql, array $binds = [] );

    /**
     * @param string $sql
     * @param array $binds
     * @return array
     */
    abstract public function fetchCol( $sql, array $binds = [] );

    /**
     * @param string $sql
     * @param array $binds
     * @return array
     */
    abstract public function fetchRow( $sql, array $binds = [] );

    /**
     * @param string $sql
     * @param array $binds
     * @return string|null
     */
    abstract public function fetchOne( $sql, array $binds = [] );

    /**
     * @param string $table
     * @param array $data [ key => value ]
     * @return int
     */
    abstract public function insert( $table, array $data );

    /**
     * @param string $table
     * @param array $data [ [ key => value ], [ key => value ], ... ]
     */
    abstract public function insertArray( $table, array $data );

    /**
     * @param string $table
     * @param array $data [ key => value ]
     * @param array $conditions
     */
    abstract public function update( $table, array $data, array $conditions = [] );

    /**
     * @param string $table
     * @param array $conditions
     */
    abstract public function delete( $table, array $conditions = [] );
}
