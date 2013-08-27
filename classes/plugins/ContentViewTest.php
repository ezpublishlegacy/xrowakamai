<?php

/**
*
* @package xrow\CDN
*/
/*
namespace XROW\CDN;

use \eZPersistentObject as eZPersistentObject;
use \eZContentObjectTreeNode as eZContentObjectTreeNode;
*/
class ContentViewTest implements ContentModifiedEvaluator, ContentPermissionEvaluator
{
    static function etag( $moduleName, $functionName, $params )
    {
        $current_user = eZUser::currentUser();
        $etag = new ETAG();
        $etag->time = self::getLastModified($moduleName, $functionName, $params);
        if ( $etag->time < ( time() - CDNTools::maxttl() ) )
        {
            $etag->time = time();
        }
        if( !$current_user->isAnonymous() )
        {
            $etag->permission = eRASMoCookie::generateCookieValue( $current_user );
            return $etag;
        }
        else
        {
            $etag->permission = CDNTools::ANONYMOUSHASH;
            return $etag;
        }
    }
    static function isNotModified( $moduleName, $functionName, $params, $time )
    {
        /* 
         * @TODO maybe implement
         * $expire = time() - self::ttl($moduleName, $functionName, $params); if ( $expire < $time ) { return false; }
         */
        if ( isset( $params['NodeID'] ) && is_numeric( $params['NodeID'] ) )
        {
            
            if( eZINI::instance( "xrowcdn.ini")->hasVariable( "ContentViewSettings", "NodeList" ) )
            {
                $nodes = eZINI::instance( "xrowcdn.ini")->variable( "ContentViewSettings", "NodeList" );
                
                if ( array_key_exists($params['NodeID'], $nodes) and is_numeric($nodes[$params['NodeID']]) )
                {
                    $ttl = self::ttl($moduleName, $functionName, $params);
                    $expire = time() - self::ttl($moduleName, $functionName, $params);
                    if ( $expire < $time )
                    {
                    	return $ttl;
                    }
                    else
                    {
                        return false;
                    }
                }
            }
            $conds = array( 
                'node_id' => (int)$params['NodeID'] 
            );
            $node = eZPersistentObject::fetchObject( eZContentObjectTreeNode::definition(), null, $conds, true );
            if ( $node and ( $node->attribute( 'modified_subnode' ) <= $time ) )
            {
                return self::ttl( $moduleName, $functionName, $params );
            }
        }
        else
        {
            return false;
        }
    }

    static function getLastModified( $moduleName, $functionName, $params )
    {
        if ( isset( $params['NodeID'] ) && is_numeric( $params['NodeID'] ) )
        {
            if( eZINI::instance( "xrowcdn.ini")->hasVariable( "ContentViewSettings", "NodeList" ) )
            {
                $nodes = eZINI::instance( "xrowcdn.ini")->variable( "ContentViewSettings", "NodeList" );
                if ( array_key_exists($params['NodeID'], $nodes) and is_numeric($nodes[$params['NodeID']]) )
                {
                    return time();
                }
            }
            $node = eZContentObjectTreeNode::fetch( (int)$params['NodeID'] );
            if ( $node instanceof eZContentObjectTreeNode )
            {
                return $node->attribute( 'modified_subnode' );
            }
        }
        else
        {
            return false;
        }
    }

    static function ttl( $moduleName, $functionName, $params )
    {
        if( eZINI::instance( "xrowcdn.ini")->hasVariable( "ContentViewSettings", "NodeList" ) )
        {
            $nodes = eZINI::instance( "xrowcdn.ini")->variable( "ContentViewSettings", "NodeList" );
            if ( array_key_exists($params['NodeID'], $nodes) and is_numeric($nodes[$params['NodeID']]) )
            {
                return (int) $nodes[$params['NodeID']];
            }
        }
        return CDNTools::ttl();
    }
}