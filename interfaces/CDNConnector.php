<?php

namespace XROW\CDN;

use \eZContentObjectTreeNode as eZContentObjectTreeNode;
use \eZContentObject as eZContentObject;

interface CDNConnector
{
    /**
     * Clears all caches.
     *
     */
    static function clearAll();
    /**
     * Updates ezcontentobject_tree.modified_subnode
     *
     * Beware the function eZContentObjectTreeNode::updateAndStoreModified() is also called during publishing.
     *
     * @param eZContentObjectTreeNode $node
     */
    static function clearCacheByNode( eZContentObjectTreeNode $node );
    /**
     * Updates ezcontentobject_tree.modified_subnode for all nodes of the object
     *
     * Beware the function eZContentObjectTreeNode::updateAndStoreModified() is also called during publishing.
     * @see xrowCDNConnector::clearCacheByNode()
     * @param eZContentObject $object
     */
    static function clearCacheByObject( eZContentObject $object );
    /**
     * Might generate a 304 Response and abort the execution
     * Listener function for Event "module/start" defined in [Event].Listener in site.ini 
     */
    static function checkNotModified( $moduleName, $functionName, $params );
    /**
     * Listener function for Event "response/output" defined in [Event].Listener in site.ini
     */
    static function deliver( $html );
}