<?php

$timeout = 5;
if ( isset( $Params['timeout'] ) && is_numeric( $Params['timeout'] ) )
{
    $timeout = $Params['timeout'];
}
sleep( $timeout );

print( "Timeout: " . $timeout . " seconds." );

eZExecution::cleanExit();

?>