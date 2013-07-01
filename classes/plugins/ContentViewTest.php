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
class ContentViewTest implements ContentModifiedEvaluator
{

    static function isNotModified( $moduleName, $functionName, $params, $time )
    {
        /* 
         * @TODO maybe implement
         * $expire = time() - self::ttl($moduleName, $functionName, $params); if ( $expire < $time ) { return false; }
         */
        if ( ! is_numeric( $params[1] ) )
        {
            return false;
        }
        $conds = array( 
            'node_id' => $params[1] 
        );
        $node = eZPersistentObject::fetchObject( eZContentObjectTreeNode::definition(), null, $conds, true );
        if ( $node )
        {
            return $node->attribute( 'modified_subnode' ) <= $time;
        }
        else
        {
            return false;
        }
    }

    static function getLastModified( $moduleName, $functionName, $params )
    {
        $node = eZContentObjectTreeNode::fetch( $params['NodeID'] );
        if ( $node instanceof eZContentObjectTreeNode )
        {
            return $node->attribute( 'modified_subnode' );
        }
    }

    static function ttl( $moduleName, $functionName, $params )
    {
        return CDNTools::ttl();
    }
}