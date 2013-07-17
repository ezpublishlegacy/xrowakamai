<?php /* #?ini charset="utf-8"?

#[Event]
#Listeners[]=module/start@AkamaiConnector::checkNotModified
#Listeners[]=response/output@AkamaiConnector::deliver
# eZ 5 and higher
#Listeners[]=module/start@XROW\CDN\AkamaiConnector::checkNotModified
#Listeners[]=response/output@XROW\CDN\AkamaiConnector::deliver

*/ ?>