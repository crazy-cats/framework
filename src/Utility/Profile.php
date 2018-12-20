<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\Utility;

use CrazyCat\Framework\App\Config;
use CrazyCat\Framework\App\Io\Http\Request;
use CrazyCat\Framework\App\ObjectManager;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Profile {

    /**
     * @var array
     */
    static private $currentProfileNames = [];

    /**
     * @var array
     */
    static private $profiles = [];

    /**
     * @var array
     */
    static private $profileNames = [];

    /**
     * @param string $realName
     * @return string
     */
    static private function getName( $realName )
    {
        return substr( $realName, 0, strrpos( $realName, '_' ) );
    }

    /**
     * @return array
     */
    static private function getNestingProfiles()
    {
        $profiles = [];
        foreach ( self::$profiles as $path => $profile ) {
            $profile['title'] = self::getName( strrpos( $path, '/' ) !== false ? substr( $path, strrpos( $path, '/' ) + 1 ) : $path );
            $profile['children'] = [];
            eval( '$profiles[\'' . implode( '\'][\'children\'][\'', explode( '/', $path ) ) . '\'] = $profile;' );
        }
        return $profiles;
    }

    /**
     * @param array|null $profiles
     * @return string
     */
    static private function getResultHtml( $profiles, $level = 0 )
    {
        $html = '';
        $now = microtime( true );
        $nowUsedMemory = memory_get_usage();
        foreach ( $profiles as $profile ) {
            $spaces = str_repeat( '&nbsp;', 4 * $level );
            $usedTime = ( ( isset( $profile['end_at'] ) ? $profile['end_at'] : $now ) - $profile['start_at'] ) * 1000;
            $usedMemory = ( ( isset( $profile['end_used_memory'] ) ? $profile['end_used_memory'] : $nowUsedMemory ) - $profile['start_used_memory'] );
            if ( !empty( $profile['children'] ) ) {
                $html .= sprintf( '<tr><td>%s<span>%s</span> start</td><td>%s</td><td>%s</td></tr>', $spaces, $profile['title'], '-', '-' ) .
                        self::getResultHtml( $profile['children'], $level + 1 ) .
                        sprintf( '<tr><td>%s<span>%s</span> end</td><td>%s</td><td>%s</td></tr>', $spaces, $profile['title'], $usedTime, $usedMemory );
            }
            else {
                $html .= sprintf( '<tr><td>%s<span>%s</span></td><td>%s</td><td>%s</td></tr>', $spaces, $profile['title'], $usedTime, $usedMemory );
            }
        }
        return $html;
    }

    /**
     * @return string
     */
    static public function printProfiles()
    {
        if ( !ObjectManager::getInstance()->get( Config::class )->getValue( 'profile' ) ||
                ObjectManager::getInstance()->get( Request::class )->getParam( Request::AJAX_PARAM ) ) {
            return;
        }
        echo sprintf( '<table class="profiles"><thead><tr>' .
                '<th>Profile Name</th>' .
                '<th>Used Time (ms)</th>' .
                '<th>Used Memory (byte)</th></tr></thead>' .
                '<tbody>%s</tbody><tfoot><tr><td colspan="3">&nbsp;</td></tr></tfoot></table>', self::getResultHtml( self::getNestingProfiles() ) );
    }

    /**
     * Set start point for a new profile
     * @param string $name
     */
    static public function start( $name )
    {
        if ( !isset( self::$profileNames[$name] ) ) {
            self::$profileNames[$name] = 0;
        }
        $realName = $name . '_' . self::$profileNames[$name] ++;
        array_push( self::$currentProfileNames, $realName );

        self::$profiles[implode( '/', self::$currentProfileNames )] = [
            'start_at' => microtime( true ),
            'start_used_memory' => memory_get_usage()
        ];
    }

    /**
     * Set end point for an exist profile
     * @param string $name
     */
    static public function end( $name )
    {
        if ( $name != self::getName( self::$currentProfileNames[count( self::$currentProfileNames ) - 1] ) ) {
            throw new \Exception( sprintf( 'Not an expected end name `%s`.', $name ) );
        }

        $path = implode( '/', self::$currentProfileNames );
        self::$profiles[$path]['end_at'] = microtime( true );
        self::$profiles[$path]['end_used_memory'] = memory_get_usage();

        array_pop( self::$currentProfileNames );
    }

}
