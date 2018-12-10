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

    /**
     * Customized a method to read CSV file with Chinese content,
     *     instead of using PHP fgetcsv function.
     * 
     * @param resource $handle
     * @param int $length
     * @param string $delimiter
     * @param string $enclosure
     * @return string[]
     */
    static public function getCsv( $handle, $length = 0, $delimiter = ',', $enclosure = '"' )
    {
        $separator = preg_quote( $delimiter );
        $quoteSymbol = preg_quote( $enclosure );

        $line = '';
        $eof = false;
        do {
            $line .= ( $length === null ? fgets( $handle ) : fgets( $handle, (int) $length ) );
            $eof = ( preg_match_all( '/' . $quoteSymbol . '/', $line ) % 2 == 0 );
        }
        while ( !$eof );
        if ( empty( $line ) ) {
            return false;
        }

        $csvPattern = '/(' . $quoteSymbol . '[^' . $quoteSymbol . ']*(?:' . $quoteSymbol . $quoteSymbol . '[^' . $quoteSymbol . ']*)*' . $quoteSymbol . '|[^' . $separator . ']*)' . $separator . '/';
        $csvLine = preg_replace( '/(?: |[ ])?$/', $separator, trim( $line ) );
        $matches = null;
        preg_match_all( $csvPattern, $csvLine, $matches );
        $row = $matches[1];
        for ( $col = 0; $col < count( $row ); $col++ ) {
            $row[$col] = preg_replace( '/^' . $quoteSymbol . '(.*)' . $quoteSymbol . '$/s', '$1', $row[$col] );
            $row[$col] = str_replace( $quoteSymbol . $quoteSymbol, $quoteSymbol, $row[$col] );
        }
        return $row;
    }

}
