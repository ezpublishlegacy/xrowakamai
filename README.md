xrowakamai
==========

Integration with Akamai for eZ Publish


Setup

* Active the extenstion
* Setup MemcacheServers and INI settings

MemcacheServer[]=192.168.0.1
MemcacheServer[]=192.168.0.2

* Remove Expires header from index.php or ezpkernelweb.php

// 'Expires' => 'Mon, 26 Jul 1997 05:00:00 GMT',
 
