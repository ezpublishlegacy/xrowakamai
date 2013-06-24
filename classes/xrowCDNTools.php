<?php

class xrowCDNTools
{
    static private $stash = false;
    static function hash( eZURI $uri )
    {
        return md5( $GLOBALS['eZCurrentAccess']['name'] . $uri->uriString(true) );
    }
    /*
     *  @return Stash
     */
    static function stash()
    {
        if ( ! self::$stash )
        {
            // Using memcached options
            $options = array();
            $options['servers'][] = array('192.168.0.1', '11211');
            $options['servers'][] = array('192.168.0.1', '11211');
            
            $options['prefix_key'] = 'ezpublish';
            $options['libketama_compatible'] = true;
            $options['cache_lookups'] = true;
            $options['serializer'] = 'json';
            
            $driver = new Stash\Driver\Memcache($options);
            $stash = new Stash\Pool( $driver );
             
            self::$stash = $stash;
        }
        return self::$stash;
    }
}