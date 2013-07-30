<?php

/*namespace XROW\CDN;

use \eZINI as eZINI;
*/
class CDNTools
{
    static private $debug = null;
    static private $ttl = null;

    static function cacheHeader( $ttl = null, $last_modified = null )
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
        if( $ttl == 1 )
        {
            header( 'Cache-Control: no-store, no-cache, must-revalidate' );
            header( 'Edge-control: !no-store,!log-cookie,max-age=70' );
        }
        else
        {
            header( 'Cache-Control: public, must-revalidate, max-age=' . $ttl );
            header( 'Edge-control: !log-cookie,max-age=' . $ttl );
        }

        if ( $last_modified )
        {
            header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', $last_modified ) . ' GMT' );
        }

        header( 'Age: 0' );
        header( 'Pragma: ' );
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
                self::$ttl = 4*3600;
            }
        }
        return self::$ttl;
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
}