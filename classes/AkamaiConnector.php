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
     * @see xrowCDNConnector::clearCacheByObject()
     */
    static function clearCacheByObject( eZContentObject $object )
    {
        foreach ( $object->assignedNodes() as $node )
        {
            self::clearCacheByNode( $node );
        }
        return true;
    }
    
    static function setGlobalModuleParams( $module, $functionName, $parameters )
    {
        $uri = $GLOBALS['eZRequestedURI'];
        $userParameters = $uri->userParameters();
        $function = $module->Functions[$functionName];
        $params = array();
        $i = 0;
        if ( isset( $function["params"] ) )
        {
            $functionParameterDefinitions = $function["params"];
            foreach ( $functionParameterDefinitions as $param )
            {
                if ( isset( $parameters[$i] ) )
                {
                    $params[$param] = $parameters[$i];
                }
                /*else
                {
                    $params[$param] = null;
                }*/
                ++$i;
            }
        }
        if ( array_key_exists( 'Limitation', $parameters  ) )
        {
            $params['Limitation'] =& $parameters[ 'Limitation' ];
        }
        // check for unordered parameters and initialize variables if they exist
        if ( isset( $function["unordered_params"] ) )
        {
            $unorderedParams = $function["unordered_params"];
            foreach ( $unorderedParams as $urlParamName => $variableParamName )
            {
                if ( in_array( $urlParamName, $parameters ) )
                {
                    $pos = array_search( $urlParamName, $parameters );

                    $params[$variableParamName] = $parameters[$pos + 1];
                }
                /*else
                {
                    $params[$variableParamName] = false;
                }*/
            }
        }
        // Loop through user defines parameters
        if ( $userParameters !== false )
        {
            if ( !isset( $params['UserParameters'] ) or
                 !is_array( $params['UserParameters'] ) )
            {
                $params['UserParameters'] = array();
            }

            if ( is_array( $userParameters ) && count( $userParameters ) > 0 )
            {
                foreach ( array_keys( $userParameters ) as $paramKey )
                {
                    if( isset( $function['unordered_params'] ) &&
                        $unorderedParams != null )
                    {
                        if ( array_key_exists( $paramKey, $unorderedParams ) )
                        {
                            $params[$unorderedParams[$paramKey]] = $userParameters[$paramKey];
                            $unorderedParametersList[$unorderedParams[$paramKey]] = $userParameters[$paramKey];
                        }
                    }
                    $params['UserParameters'][$paramKey] = $userParameters[$paramKey];
                }
            }
        }
        return $params;
    }
    
    /**
     * @see xrowCDNConnector::checkNotModified()
     */
    static function checkNotModified( $module, $functionName, $params )
    {
        $params = self::setGlobalModuleParams( $module, $functionName, $params );
        $moduleName = $module->attribute( 'name' );
        if ( array_key_exists( 'HTTP_IF_MODIFIED_SINCE', $_SERVER ) and ( $_SERVER['REQUEST_METHOD'] == 'GET' or $_SERVER['REQUEST_METHOD'] == 'HEAD' ) )
        {
            $time = strtotime( $_SERVER['HTTP_IF_MODIFIED_SINCE'] );
            if ( $time > time() or ! $time  or ( defined( 'CDN_GLOBAL_EXPIRY' ) and ( strtotime( CDN_GLOBAL_EXPIRY ) > $time ) ) )
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
            if ( isset( $rule ) && is_numeric( $rule ) )
            {
                $expire = $time + $rule;
                if ( $expire > time() )
                {
                    header( "HTTP/1.1 304 Not Modified" );
                    CDNTools::cacheHeader( $rule, $time );
                    if( CDNTools::debug() )
                    {
                        eZLog::write( "Status:304 Expire:" . $expire . " " . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], "xrowcdn.log" );
                    }
                    eZExecution::cleanExit();
                }
            }
            elseif ( isset( $rule ) && in_array( self::CLASSNAMESPACE, class_implements( $rule ) ) )
            {
                $ttl = call_user_func( $rule . "::isNotModified", $moduleName, $functionName, $params, $time );
                if( $ttl )
                {
                    header( "HTTP/1.1 304 Not Modified" );
                    CDNTools::cacheHeader( $ttl, $time );
                    if( CDNTools::debug() )
                    {
                        eZLog::write( "Status:304 TTL:" . $ttl . " " . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] , "xrowcdn.log");
                    }
                    eZExecution::cleanExit();
                }
            }
            elseif ( isset( $rule ) && !in_array( self::CLASSNAMESPACE, class_implements( $rule ) ) )
            {
                throw new Exception( "Class '$rule' does`t implement " . self::CLASSNAMESPACE . "." );
            }
            //eZLog::write( "PROBE:" . $_SERVER['HTTP_IF_MODIFIED_SINCE'] . " " . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] , "xrowcdn.log");
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
        $params = array_merge( $GLOBALS['eZRequestedModuleParams']['parameters'], self::setGlobalModuleParams( $GLOBALS['eZRequestedModule'], $functionName, array() ) );
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
                eZLog::write( "Status:200 TTL:$rule " . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] , "xrowcdn.log");
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
                eZLog::write( "Status:200 TTL:$ttl " . gmdate( 'D, d M Y H:i:s', $last_modified ) . " " . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] , "xrowcdn.log");
            }
        }
        elseif ( isset( $rule ) && !in_array( self::CLASSNAMESPACE, class_implements( $rule ) ) )
        {
            throw new Exception( "Class '$rule' does`t implement " . self::CLASSNAMESPACE . "." );
        }
        return $html;
    }
}