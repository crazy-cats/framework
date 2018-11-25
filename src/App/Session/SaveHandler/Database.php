<?php

/*
 * Copyright © 2018 CrazyCat, Inc. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */

namespace CrazyCat\Framework\App\Session\SaveHandler;

/**
 * @category CrazyCat
 * @package CrazyCat\Framework
 * @author Bruce Z <152416319@qq.com>
 * @link http://crazy-cat.co
 */
class Database extends AbstractHandler {

    const TYPE = 'database';

    protected function init()
    {
        session_module_name( 'user' );
    }

    public function open( $savePath, $name )
    {
        
    }

    public function read( $sessionId )
    {
        
    }

    public function write( $sessionId, $sessionData )
    {
        
    }

    public function close()
    {
        
    }

    public function destroy( $sessionId )
    {
        
    }

    public function gc( $maxLifetime )
    {
        
    }

}
