<?php

/**
 * Connector interface
*
* @package xrow\CDN
*/
class xrowAkamaiConnector implements xrowCDNConnector
{
    private static $uri = false;

    static function clearAll()
    {
        return true;
    }
    static function clearCacheByNode( eZContentObjectTreeNode $node )
    {
        return true;
    }
    static function clearCacheByObject( eZContentObject $object )
    {
        return true;
    }
    static function generateResponse( eZUri $uri )
    {
        self::$uri = $uri;
        if (array_key_exists( 'HTTP_IF_MODIFIED_SINCE', $_SERVER) )
        {
            $time = strtotime( $_SERVER['HTTP_IF_MODIFIED_SINCE'] );
            $hash = xrowCDNTools::hash( self::$uri );
            $result = xrowCDNTools::memcache()->get( $hash );
            if ( !$result )
            {
                header("HTTP/1.1 304 Not Modified");
                eZExecution::cleanExit();
            }
        }
        return true;
    }
    static function storeResult( $html )
    {
        if ( self::$uri instanceof eZURI )
        {
            $hash = xrowCDNTools::hash( self::$uri );
            $memcache = xrowCDNTools::$memcache();
            $memcache->set( $hash . "_html", $html, true, 4*3600);
            $get_result = $memcache->get('key');
        }
        return true;
    }
}