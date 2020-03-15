<?php

/*
 * Copyright © 2020 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Session\SaveHandler;

/**
 * @category CrazyCat
 * @package  CrazyCat\Framework
 * @author   Liwei Zeng <zengliwei@163.com>
 * @link     http://crazy-cat.cn
 */
class Files extends AbstractHandler {

    const TYPE = 'files';

    /**
     * Default storage folder
     */
    const DIR = DIR_VAR . DS . 'session';

    /**
     * @var string
     */
    private $storageDir = self::DIR;

    protected function init()
    {
        session_module_name( 'files' );

        if ( !empty( $this->config['storage_dir'] ) ) {
            $this->storageDir = $this->config['storage_dir'];
        }
    }

    public function open( $savePath, $sessionName )
    {
        if ( !empty( $savePath ) && is_dir( $savePath ) ) {
            $this->storageDir = $savePath;
        }
        if ( !is_dir( $this->storageDir ) ) {
            mkdir( $this->storageDir, 0755, true );
        }
        return true;
    }

    public function read( $sessionId )
    {
        return is_file( $this->storageDir . DS . $sessionId ) ?
                file_get_contents( $this->storageDir . DS . $sessionId ) :
                '';
    }

    public function write( $sessionId, $sessionData )
    {
        return file_put_contents( $this->storageDir . DS . $sessionId, $sessionData ) === false ? false : true;
    }

    public function close()
    {
        return true;
    }

    public function destroy( $sessionId )
    {
        if ( is_file( $this->storageDir . DS . $sessionId ) ) {
            unlink( $this->storageDir . DS . $sessionId );
        }
        return true;
    }

    public function gc( $maxLifetime )
    {
        foreach ( glob( $this->storageDir . DS . 'sess_*' ) as $file ) {
            if ( is_file( $file ) && ( filemtime( $file ) + $maxLifetime ) < time() ) {
                unlink( $file );
            }
        }
        return true;
    }

}
