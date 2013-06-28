xrowakamai
==========

Integration with Akamai for eZ Publish

The basic feature of this extension is that it will enchant to Events wil prebuild functionality.

The one event is set before processing the request as soon as possible to determine as soon as possible if a requests can be aborted and answered with an 304.

A second event is set after processing the request and sets additional headers.

Setup

* Active the extenstion
* Apply patch from patch/[version]/xrowakamai.diff The patch will remove expires header from index.php or ezpkernelweb.php
* Enable settings in xrowcdn.ini

Option A.) 
Set an ttl in seconds option for each ez module in [Settings].Modules in xrowcdn.ini

Modules[content/view]=3600

or

Modules[content/*]=3600

Option B.) 
Set an plugin wiht smart logic to handle the request.

Modules[content/view]=ContentViewTest

Create a new Plugin

See ContentViewTest.php as an example. Beware and use as less SQL queries as possible.