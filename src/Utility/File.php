<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\Utility;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class File {

    /**
     * Get folders of specified directory
     * 
     * @param string $dir
     * @param boolean $recursive
     * @return string[]
     */
    static public function getFolders( $dir, $recursive = false )
    {
        $subDirs = [];
        if ( ( $dh = opendir( $dir ) ) ) {
            while ( ( $file = readdir( $dh ) ) !== false ) {
                if ( $file !== '.' && $file !== '..' && is_dir( $dir . '/' . $file ) ) {
                    $subDirs[] = $file;
                    if ( $recursive ) {
                        $subDirs = array_merge( $subDirs, self::getFolders( $dir . DS . $file, true ) );
                    }
                }
            }
            closedir( $dh );
        }
        return $subDirs;
    }

    /**
     * Get folders of specified directory
     * 
     * @param string $dir
     * @param boolean $recursive
     * @return string[]
     */
    static public function getFiles( $dir, $recursive = false )
    {
        $files = [];
        if ( ( $dh = opendir( $dir ) ) ) {
            while ( ( $file = readdir( $dh ) ) !== false ) {
                if ( is_file( $dir . '/' . $file ) ) {
                    $files[] = $file;
                }
                if ( $recursive && ( $file !== '.' && $file !== '..' && is_dir( $dir . '/' . $file ) ) ) {
                    $files = array_merge( $files, self::getFiles( $dir . DS . $file, true ) );
                }
            }
            closedir( $dh );
        }
        return $files;
    }

}
