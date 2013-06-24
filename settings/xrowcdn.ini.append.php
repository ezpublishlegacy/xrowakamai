<?php /*

[Settings]

Directories[]
Directories[]=var
Directories[]=extension
Directories[]=design
Directories[]=share/icons

[Rules]
List[]
List[]=distribution
List[]=database
List[]=js

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
Replacement=http://www.example.com

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
Replacement=http://www.example.com

*/ ?>