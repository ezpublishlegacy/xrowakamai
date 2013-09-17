<?php /* #?ini charset="utf-8"?

[Settings]
# Time to live at the edge server
#TTL=14400
# Maxium Time to live at the origin
#MaxTTL=14400
#Debug=enabled
#SSL=disabled
#Filter=CDNFilter::filter
#Hostnames that should no use the CDN
#ExcludeHostList[]=test.example.com
#Modules[content/view]=3600
#Modules[content/*]=3600
# eZ 4x
#Modules[content/view]=ContentViewTest
# eZ 5x
#Modules[content/view]=XROW\CDN\ContentViewTest
#Modules[xrowakamai/time]=3600

Directories[]
Directories[]=var
Directories[]=extension
Directories[]=design
Directories[]=share/icons

[Rules]
List[]
List[]=distribution
List[]=database
#Js needs to be local for security reasons till http://en.wikipedia.org/wiki/Cross-origin_resource_sharing works
#List[]=js

[Rule-distribution]
Dirs[]
#Dirs[]=\/(extension|design|var)(\/[a-z0-9_-]+)*\/(images|public|packages)
Dirs[]=\/extension\/[a-z0-9_-]+\/design\/[a-z0-9_-]+\/(images|stylesheets)
Dirs[]=\/design\/[a-z0-9_-]+\/(images|stylesheets)
Dirs[]=\/var\/storage\/packages
Dirs[]=\/var\/[a-z0-9_-]+\/cache\/public
Dirs[]=\/var\/[a-z0-9_-]+\/storage\/(images|images-versioned)
Suffixes[]
Suffixes[]=gif
Suffixes[]=jpg
Suffixes[]=jpeg
Suffixes[]=png
Suffixes[]=ico
Suffixes[]=css
Replacement=//www.example.com

[Rule-js]
Distribution=true
Dirs[]
#Dirs[]=\/(extension|design|var)(\/[a-z0-9_-]+)*\/(javascript|public|packages)
Dirs[]=\/extension\/[a-z0-9_-]+\/design\/[a-z0-9_-]+\/(javascript|lib)
Dirs[]=\/design\/[a-z0-9_-]+\/javascript
Dirs[]=\/var\/[a-z0-9_-]+\/cache\/public
Dirs[]=\/var\/storage\/packages
Suffixes[]
Suffixes[]=js
Replacement=//www.example.com

#[ContentViewSettings]
#NodeList[]
# Array with NodeID and time combinations.
#NodeList[123]=3600
#NodeList[1234]=480

*/ ?>