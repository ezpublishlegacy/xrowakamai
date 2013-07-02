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
            if ( $time > time() or ! $time )
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
                    $result = true;
                }
                else
                {
                    $result = false;
                }
            }
            elseif ( isset( $rule ) && in_array( self::CLASSNAMESPACE, class_implements( $rule ) ) )
            {
                $result = call_user_func( $rule . "::isNotModified", $moduleName, $functionName, $params, $time );
            }
            elseif ( isset( $rule ) && !in_array( self::CLASSNAMESPACE, class_implements( $rule ) ) )
            {
                throw new Exception( "Class '$rule' does`t implement " . self::CLASSNAMESPACE . "." );
            }
            if ( !empty( $result ) )
            {
                header( "HTTP/1.1 304 Not Modified" );
                if( CDNTools::debug() )
                {
                    eZLog::write( "304 " . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], "xrowcdn_304.log");
                }
                eZExecution::cleanExit();
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
        if ( isset( $rule ) && is_numeric( $rule ) )
        {
            header_remove("Expires");
            header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', time() ) . ' GMT' );
            header( 'Cache-Control: public, must-revalidate, max-age=' . $rule );
            header( 'Edge-control: !log-cookie,max-age=60' );
            header( 'Age: 0' );
            header( 'Pragma: ' );
            if( CDNTools::debug() )
            {
                eZLog::write( "now/$rule " . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], "xrowcdn_200.log");
            }
        }
        elseif ( isset( $rule ) && in_array( self::CLASSNAMESPACE, class_implements( $rule ) ) )
        {
            $last_modified = call_user_func( $rule . "::getLastModified", $moduleName, $functionName, $params  );
            if ( $last_modified )
            {
                header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', $last_modified ) . ' GMT' );
            }
            $ttl = call_user_func( $rule . "::ttl", $moduleName, $functionName, $params );
            if ( $ttl )
            {
                header_remove("Expires");
                header( 'Cache-Control: public, must-revalidate, max-age=' . $ttl );
                header( 'Edge-control: !log-cookie,max-age=60' );
                header( 'Age: 0' );
                header( 'Pragma: ' );
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