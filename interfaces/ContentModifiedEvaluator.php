<?php

/*namespace XROW\CDN;*/

interface ContentModifiedEvaluator
{
    /*
     * Function to determine weather the content has been modifed since.
     * Module/view params
     * @param string $moduleName Modulename
     * @param string $functionName Viewname
     * @param mixed[] $array Paramters of the module
     * @param int $time If modified since unix timestamp
     * @return boolean|int "false" whether the content has been modifed since or the ttl for the resource.
     */
    static function isNotModified( $moduleName, $functionName, $params, $time );
    /*
     * Function to determine time to live for the request
     * @param string $moduleName Modulename
     * @param string $functionName Viewname
     * @param mixed[] $array Paramters of the module
     * @return int Positive number in seconds
     */
    static function ttl( $moduleName, $functionName, $params );
    /*
     * Function to determine time of last modification for the request
     * @param string $moduleName Modulename
     * @param string $functionName Viewname
     * @param mixed[] $array Paramters of the module
     * @return int Timestamp of last modification of the resource
     */
    static function getLastModified( $moduleName, $functionName, $params );
}