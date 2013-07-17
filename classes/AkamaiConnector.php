<?php

/**
 * Connector interface
*
* @package xrow\CDN
*/
/*
namespace XROW\CDN;


use \eZINI as eZINI;
use \eZExecution as eZExecution;
use \eZLog as eZLog;
use \Exception as Exception;
*/
class AkamaiConnector implements CDNConnector
{
    //const CLASSNAMESPACE = 'XROW\CDN\ContentModifiedEvaluator';
    const CLASSNAMESPACE = 'ContentModifiedEvaluator';
    /**
     * @see xrowCDNConnector::clearAll()
     */
    static function clearAll()
    {
        return true;
    }
    /**
     * @see xrowCDNConnector::clearCacheByNode()
     */
    static function clearCacheByNode( eZContentObjectTreeNode $node )
    {
        $node->updateAndStoreModified();
        return true;
    }

    /**
     * @see xrowCDNConnector::checkNotModified()
     */
    static function clearCacheByObject( eZContentObject $object )
    {
        foreach ( $object->assignedNodes() as $node )
        {
            self::clearCacheByNode( $node );
        }
        return true;
    }
    /**
     * @see xrowCDNConnector::checkNotModified()
     */
    static function checkNotModified( $moduleName, $functionName, $params )
    {
        if ( array_key_exists( 'HTTP_IF_MODIFIED_SINCE', $_SERVER ) and ( $_SERVER['REQUEST_METHOD'] == 'GET' or $_SERVER['REQUEST_METHOD'] == 'HEAD' ) )
        {
            $time = strtotime( $_SERVER['HTTP_IF_MODIFIED_SINCE'] );
            if ( $time > time() or ! $time  or ( defined( 'CDN_GLOBAL_EXPIRY' ) and (strtotime( CDN_GLOBAL_EXPIRY ) > $time ) ) )
            {
                return true;
            }
            $ini = eZINI::instance( "xrowcdn.ini" );
            if ( $ini->hasVariable( 'Settings', 'Modules' ) )
            {
                $list = $ini->variable( 'Settings', 'Modules' );
                if ( isset( $list[$moduleName . '/' . $functionName] ) )
                {
                    $rule = $list[$moduleName . '/' . $functionName];
                }
                elseif ( isset( $list[$moduleName . '/*'] ) )
                {
                    $rule = $list[$moduleName . '/*'];
                }
            }
            $test =class_implements( $rule );
            if ( isset( $rule ) && is_numeric( $rule ) )
            {
                $expire = $time + $rule;
                if ( $expire < time() )
                {
                    header( "HTTP/1.1 304 Not Modified" );
                    CDNTools::cacheHeader( $expire, $time );
                    if( CDNTools::debug() )
                    {
                        eZLog::write( "304 " . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], "xrowcdn_304.log");
                    }
                    eZExecution::cleanExit();
                }
            }
            elseif ( isset( $rule ) && in_array( self::CLASSNAMESPACE, class_implements( $rule ) ) )
            {
                $ttl = call_user_func( $rule . "::isNotModified", $moduleName, $functionName, $params, $time );
                header( "HTTP/1.1 304 Not Modified" );
                CDNTools::cacheHeader( $ttl, $time );
                if( CDNTools::debug() )
                {
                    eZLog::write( "304 " . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], "xrowcdn_304.log");
                }
                eZExecution::cleanExit();
            }
            elseif ( isset( $rule ) && !in_array( self::CLASSNAMESPACE, class_implements( $rule ) ) )
            {
                throw new Exception( "Class '$rule' does`t implement " . self::CLASSNAMESPACE . "." );
            }
        }
        return true;
    }
    /**
     * @see xrowCDNConnector::deliver()
     */
    static function deliver( $html )
    {
        $ini = eZINI::instance( 'xrowcdn.ini' );
        if ( $ini->hasVariable( 'Settings', 'Filter' ) and function_exists( $ini->variable( 'Settings', 'Filter' ) ) )
        {
            $html = call_user_func( $ini->variable( 'Settings', 'Filter' ), $html );
        }
        if ( $_SERVER['REQUEST_METHOD'] != 'GET' and $_SERVER['REQUEST_METHOD'] != 'HEAD' )
        {
             return $html;
        }
        $moduleName = $GLOBALS['eZRequestedModuleParams']['module_name'];
        $functionName = $GLOBALS['eZRequestedModuleParams']['function_name'];
        $params = $GLOBALS['eZRequestedModuleParams']['parameters'];

        if ( $ini->hasVariable( 'Settings', 'Modules' ) )
        {
            $list = $ini->variable( 'Settings', 'Modules' );
            if ( isset( $list[$moduleName . '/' . $functionName] ) )
            {
                $rule = $list[$moduleName . '/' . $functionName];
            }
            elseif ( isset( $list[$moduleName . '/*'] ) )
            {
                $rule = $list[$moduleName . '/*'];
            }
        }
        if ( isset( $rule ) && is_numeric( $rule ) )
        {
            CDNTools::cacheHeader( $rule, time() );
            if( CDNTools::debug() )
            {
                eZLog::write( "now/$rule " . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], "xrowcdn_200.log");
            }
        }
        elseif ( isset( $rule ) && in_array( self::CLASSNAMESPACE, class_implements( $rule ) ) )
        {
            $last_modified = call_user_func( $rule . "::getLastModified", $moduleName, $functionName, $params  );
            $ttl = call_user_func( $rule . "::ttl", $moduleName, $functionName, $params );
            if ( $ttl )
            {
                CDNTools::cacheHeader( $ttl, $last_modified );
            }
            if( CDNTools::debug() )
            {
                eZLog::write( "$last_modified/$ttl " . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], "xrowcdn_200.log");
            }
        }
        elseif ( isset( $rule ) && !in_array( self::CLASSNAMESPACE, class_implements( $rule ) ) )
        {
            throw new Exception( "Class '$rule' does`t implement " . self::CLASSNAMESPACE . "." );
        }
        return $html;
    }
}