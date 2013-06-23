<?php

interface xrowCDNConnector
{
    /*
     * Writes a new global timestamp into Memcache
    *
    */
    static function clearAll();
    /*
     * Writes multiple urls and timestamps if the objects modified time into memcache
    *
    */
    static function clearCacheByNode( eZContentObjectTreeNode $node );
    /*
     * @see clearCacheByNode
    */
    static function clearCacheByObject( eZContentObject $object );
    /*
     * Might generate a 304 Response and abort the execution
    *
    * The funtion can hook into ezpEvent::getInstance()->notify( 'request/input', array( $uri ) );
    */
    static function generateResponse( eZUri $uri );
    /*
     * ezpEvent::getInstance()->filter( 'response/output', $fullPage );
    */
    static function storeResult( $html );
}