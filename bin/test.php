<?php
echo "Expire for URI: " . $argv[2] ."\n";
if (empty($argv[2]))
{
    $argv[2] = "";
}
echo "Siteaccess: " . $GLOBALS['eZCurrentAccess']['name'] . "\n";
$uri = new eZURI( $argv[2], true );
xrowCDNTools::invalidate( $uri, $GLOBALS['eZCurrentAccess']['name'] );

$hash = xrowCDNTools::hash( $uri, $GLOBALS['eZCurrentAccess']['name'] );
$stash = xrowCDNTools::stash();
echo "Hash: " . $hash . "\n";
$stashItem = $stash->getItem( $hash );
$obj = $stashItem->get();
if ($stashItem->isMiss())
{
	echo "Miss: " . $stashItem->getKey() . "\n";
}
var_dump( $obj ); 