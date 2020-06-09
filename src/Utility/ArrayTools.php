<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\Utility;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     https://crazy-cat.cn
 */
class ArrayTools
{
    /**
     * @param array  $data
     * @param int    $level
     * @param string $space
     * @return string
     */
    public function arrayToString(array $data, $level = 1, $space = '    ')
    {
        $prefix = str_repeat($space, $level);

        $arrString = [];
        foreach ($data as $key => $value) {
            switch (strtolower(gettype($value))) {
                case 'integer':
                case 'double':
                    $value = $value;
                    break;

                case 'string':
                    $value = '\'' . str_replace('\'', '\\\'', $value) . '\'';
                    break;

                case 'null':
                    $value = 'null';
                    break;

                case 'boolean':
                    $value = $value ? 'true' : 'false';
                    break;

                case 'array':
                    $value = $this->arrayToString($value, $level + 1);
                    break;
            }
            $arrString[] = $prefix . sprintf('\'%s\' => %s', str_replace('\'', '\\\'', $key), $value);
        }

        return sprintf("[\n%s\n%s]", implode(",\n", $arrString), str_repeat(' ', ($level - 1) * 4));
    }

    /**
     * @param array $optionsArray
     * @return array
     */
    public function toHashArray(array $optionsArray)
    {
        $hash = [];
        foreach ($optionsArray as $row) {
            $hash[$row['value']] = $row['label'];
        }
        return $hash;
    }

    /**
     * @param array $hashArray
     * @return array
     */
    public function toOptionsArray(array $hashArray)
    {
        $options = [];
        foreach ($hashArray as $value => $label) {
            $options[] = ['label' => $label, 'value' => $value];
        }
        return $options;
    }
}
