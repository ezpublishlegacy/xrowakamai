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
            $stash = xrowCDNTools::stash();
            $stashItem = $stash->getItem( $hash );

            $data = $stashItem->get();
            if( $stashItem->isMiss() )
            {
                header("HTTP/1.1 304 Not Modified");
                header("X-XROW-Cache: 304");
                eZExecution::cleanExit();
            }
        }
        return true;
    }
    static function storeResult( $html )
    {
        if ( $GLOBALS['eZRequestedModuleParams']['module_name'] == 'content' and $GLOBALS['eZRequestedModuleParams']['function_name'] == 'view' )
        {
            
            $node = eZContentObjectTreeNode::fetch( $GLOBALS['eZRequestedModuleParams']['parameters']['NodeID'] );

            if ( $node instanceof eZContentObjectTreeNode)
            {
                $GLOBALS['CONTENT_LAST_MODIFIED'] = $node->attribute( 'modified_subnode' ) + 4 * 3600;
            }
            
        }
       
        if ( isset( $GLOBALS['CONTENT_LAST_MODIFIED'] ) )
        {
            header('Last-Modified: '.gmdate('D, d M Y H:i:s', $GLOBALS['CONTENT_LAST_MODIFIED']).' GMT');
        }
        if ( self::$uri instanceof eZURI )
        {
            $hash = xrowCDNTools::hash( self::$uri );
            $stash = xrowCDNTools::stash();
            $stashItem = $stash->getItem( $hash . "_html" );
            // Cache expires in four hours.
            $stashItem->set($html, 4*3600);
        }
        return $html;
    }
}