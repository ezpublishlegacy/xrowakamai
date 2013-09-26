<?php

/*namespace XROW\CDN;

use \eZINI as eZINI;
*/
class CDNTools
{
    static private $debug = null;
    static private $ttl = null;
    static private $maxttl = null;
    //remote_id of anonymous user
    const ANONYMOUSHASH = "faaeb9be3bd98ed09f606fc16d144eca";
    static function cacheHeader( $ttl = null, $last_modified = null, ETAG $etag = null )
    {
        if ( $ttl === null || !is_numeric( $ttl ) )
        {
            return false;
        }
        header_remove("Expires");
        header_remove("X-Powered-By");
        
        /**
         * max-age,no-store,no-cache,pre-check (serves as a max-age setting if there is no max-age) post-check (serves as an Akamai Prefresh setting)
        */
        if ( $ttl )
        {
            if( $etag !== null )
            {
                header( 'Cache-Control: private, must-revalidate, max-age=0' );
                header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' );
            }
            else
            {
                header( 'Cache-Control: public, must-revalidate, max-age=' . $ttl );
            }
            header( 'Edge-control: !log-cookie,max-age=' . $ttl );
            header( 'Age: 0' );
        }
        if ( $last_modified )
        {
            header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', $last_modified ) . ' GMT' );
        }

        if( $etag !== null )
        {
            header( 'ETag: ' . $etag->generate() );
            header( 'Pragma: no-cache' );
        }
    }

    static function ttl()
    {
        if ( self::$ttl === null )
        {
            $ini = eZINI::instance( 'xrowcdn.ini' );
            if ( $ini->hasVariable ( 'Settings', 'TTL' ) )
            {
                self::$ttl = (int)$ini->variable ( 'Settings', 'TTL' );
            }
            else
            {
                self::$ttl = 1800;
            }
        }
        return self::$ttl;
    }
    static function maxttl()
    {
        if ( self::$maxttl === null )
        {
            $ini = eZINI::instance( 'xrowcdn.ini' );
            if ( $ini->hasVariable ( 'Settings', 'MaxTTL' ) )
            {
                self::$maxttl = (int)$ini->variable ( 'Settings', 'MaxTTL' );
            }
            else
            {
                self::$maxttl = 4*3600;
            }
        }
        return self::$maxttl;
    }
    static function debug()
    {
        if ( self::$debug === null )
        {
            $ini = eZINI::instance( 'xrowcdn.ini' );
            if ( $ini->hasVariable ( 'Settings', 'Debug' ) and $ini->variable ( 'Settings', 'Debug' ) == 'enabled' )
            {
                self::$debug = true;
            }
            else
            {
                self::$debug = false;
            }
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
        $hash = CDNTools::hash( $uri );
        $stash = CDNTools::stash();
        $stashItem = $stash->getItem( $hash );
        $stashItem->clear();
        $obj = new xrowCacheItem();
        $obj->expire = time();
        $stashItem->set($obj, 4*3600);
    }
    */
}
