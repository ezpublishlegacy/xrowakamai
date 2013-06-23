<?php

class xrowCDNTools
{
    private $memcache = false;
    static function hash( eZURI $uri )
    {
        return md5( $GLOBALS['eZCurrentAccess']['name'] . $uri->uriString(true) );
    }
    static function memcache()
    {
        if ( ! self::$memcache )
        {
            $memcache = new Memcache();
            if (!$memcache->connect('localhost', 11211) )
            {
                throw new Exception("Could not connect");
            }
            
            self::$memcache = $memcache;
        }
        return self::$memcache;
    }
}