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
        if ( ! is_numeric( $params['NodeID'] ) )
        {
            return false;
        }
        $conds = array( 
            'node_id' => (int)$params['NodeID'] 
        );
        $node = eZPersistentObject::fetchObject( eZContentObjectTreeNode::definition(), null, $conds, true );
        if ( $node and ( $node->attribute( 'modified_subnode' ) <= $time ) )
        {
            return self::ttl( $moduleName, $functionName, $params );
        }
        else
        {
            return false;
        }
    }

    static function getLastModified( $moduleName, $functionName, $params )
    {
        $node = eZContentObjectTreeNode::fetch( (int)$params['NodeID'] );
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