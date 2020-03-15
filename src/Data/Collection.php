<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\Data;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     http://crazy-cat.cn
 */
class Collection implements \IteratorAggregate, \Countable {

    /**
     * @var \CrazyCat\Framework\Data\DataObject[]
     */
    protected $items = [];

    /**
     * @return \CrazyCat\Framework\Data\DataObject|null
     */
    public function getItemById( $id )
    {
        return isset( $this->items[$id] ) ? $this->items[$id] : null;
    }

    /**
     * @return \Traversable
     */
    public function getIterator()
    {
        return new \ArrayIterator( $this->items );
    }

    /**
     * @return int
     */
    public function count()
    {
        return count( $this->items );
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = [];
        foreach ( $this->items as $item ) {
            $array[] = $item->toArray();
        }
        return $array;
    }

}
