<?php

namespace XROW\CDN;

use \eZINI as eZINI;

class xrowCDNTools
{
    static private $debug = null;
    static private $ttl = null;

    static function ttl()
    {
        if ( self::$ttl === null )
        {
            // Using memcached options
            $options = array();
            $ini = eZINI::instance( 'xrowcdn.ini' );
            if ( $ini->hasVariable ( 'Settings', 'TTL' ) )
            {
                self::$ttl = (int)$ini->variable ( 'Settings', 'TTL' );
            }
            self::$ttl = 4*3600;
        }
        return self::$ttl;
    }
    static function debug()
    {
        if ( self::$debug === null )
        {
            $ini = eZINI::instance( 'xrowcdn.ini' );
            if ( $ini->hasVariable ( 'Settings', 'Debug' ) and $ini->variable ( 'Settings', 'Debug' ) == 'enbaled' )
            {
                self::$debug = true;
            }
            self::$debug = false;
        }
        return self::$debug;
    }
    /*
     *  @return Stash
     */
    
    /* OLDCODE
    static private $stash = false;
    static function hash( eZURI $uri )
    {
        return md5( $uri->uriString(true) );
    }
    static function stash()
    {
        if ( ! self::$stash )
        {
            // Using memcached options
            $options = array();
            $ini = eZINI::instance( 'xrowcdn.ini' );
            if ( $ini->hasVariable ( 'Settings', 'MemcacheServer' ) )
            {
                foreach ( $ini->variable( 'Settings', 'MemcacheServer' ) as $server )
                {
                    $options['servers'][] = array( $server, '11211');
                }
            }
            
            $options['prefix_key'] = 'ezpublish';
            $options['libketama_compatible'] = true;
            $options['cache_lookups'] = true;
            $options['serializer'] = 'json';
            
            $driver = new Stash\Driver\Memcache($options);
            $stash = new Stash\Pool( $driver );
             
            self::$stash = $stash;
        }
        return self::$stash;
    }
    static function invalidate( eZURI $uri )
    {
        $hash = xrowCDNTools::hash( $uri );
        $stash = xrowCDNTools::stash();
        $stashItem = $stash->getItem( $hash );
        $stashItem->clear();
        $obj = new xrowCacheItem();
        $obj->expire = time();
        $stashItem->set($obj, 4*3600);
    }
    */
}