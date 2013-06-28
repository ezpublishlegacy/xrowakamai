<?php

/**
 * Connector interface
*
* @package xrow\CDN
*/
class xrowAkamaiStashConnector implements xrowCDNConnector
{
    private static $uri = false;

    static function clearAll()
    {
        return true;
    }
    static function clearCacheByNode( eZContentObjectTreeNode $node )
    {
        $ini = eZINI::instance( 'site.ini' );
        $uri = new eZURI( '/content/view/full/' . $node->NodeID );
        xrowCDNTools::invalidate( $uri );
        return true;
    }
    static function clearCacheByObject( eZContentObject $object )
    {
        foreach ( $object->assignedNodes() as $node )
        {
        	self::clearCacheByNode($node);
        }
        return true;
    }
    static function generateResponse( eZUri $uri )
    {
        self::$uri = $uri;
        if (array_key_exists( 'HTTP_IF_MODIFIED_SINCE', $_SERVER) and $_SERVER['REQUEST_METHOD'] == 'GET' )
        {
            $conds = array( 'node_id' => $contentObjectID );

            $node =  eZPersistentObject::fetchObjectList( eZContentObjectTreeNode::definition(),
            null,
            $conds,
            null,
            null,
            true );

            $time = strtotime( $_SERVER['HTTP_IF_MODIFIED_SINCE'] );
            $hash = xrowCDNTools::hash( self::$uri );
            $stash = xrowCDNTools::stash();
            $stashItem = $stash->getItem( $hash );
            $obj = $stashItem->get();
            //@TODO If HTTP_IF_MODIFIED_SINCE too old deliver response.
            if( $stashItem->isMiss() or !isset( $obj->expire ) )
            {
                header("HTTP/1.1 304 Not Modified");
                eZExecution::cleanExit();
            }
        }
        return true;
    }
    static function storeResult( $html )
    {
        $ini = eZINI::instance ( 'xrowcdn.ini' );
        if ( $ini->hasVariable ( 'Settings', 'Filter' ) and function_exists( $ini->variable ( 'Settings', 'Filter' ) ) ) {
            $html = call_user_func ( $ini->variable ( 'Settings', 'Filter' ), $html );
        }
        
        if ( $GLOBALS['eZRequestedModuleParams']['module_name'] == 'content' and $GLOBALS['eZRequestedModuleParams']['function_name'] == 'view' )
        {
            $node = eZContentObjectTreeNode::fetch( $GLOBALS['eZRequestedModuleParams']['parameters']['NodeID'] );
            if ( $node instanceof eZContentObjectTreeNode )
            {
                $GLOBALS['CONTENT_LAST_MODIFIED'] = $node->attribute( 'modified_subnode' ) + 4 * 3600;
            }
        }
       
        if ( isset( $GLOBALS['CONTENT_LAST_MODIFIED'] ) )
        {
            header('Last-Modified: '.gmdate('D, d M Y H:i:s', $GLOBALS['CONTENT_LAST_MODIFIED']).' GMT');
            header('Cache-Control: public, must-revalidate, max-age=' . xrowCDNTools::ttl() );
            header('Age: 0' );
            header('Pragma: ' );
        }
        if ( self::$uri instanceof eZURI )
        {
            $hash = xrowCDNTools::hash( self::$uri );
            $stash = xrowCDNTools::stash();
            $stashItem = $stash->getItem( $hash );
            $obj = $stashItem->get();
            if ( !isset( $obj ) )
            {
                $obj = new xrowCacheItem();
            }
            if ( isset( $GLOBALS['CONTENT_LAST_MODIFIED'] ) )
            {
                $obj->last_modified = $GLOBALS['CONTENT_LAST_MODIFIED'];
            }
            $obj->response = $html;
            // Cache expires in four hours.
            $stashItem->set($obj, xrowCDNTools::ttl()  );
            if ( xrowCDNTools::debug() )
            {
                header("X-Cache-Key: " . $hash );
                header("X-Cache-Uri: " . self::$uri->uriString(true) );
            }
        }
        return $html;
    }
}