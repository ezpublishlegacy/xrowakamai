<?php

/*namespace XROW\CDN;*/

interface ContentPermissionEvaluator
{
    /*
     * Function to determine if content is private
     * @param string $moduleName Modulename
     * @param string $functionName Viewname
     * @param mixed[] $array Paramters of the module
     * @return string Etag Hash
     */
    static function etag( $moduleName, $functionName, $params );
}