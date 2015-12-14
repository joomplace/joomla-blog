<?php

// Must global variable $jaxFuncNames to add function
// declaration to Community API.
global $jaxFuncNames;

// First argument should always be plugins to let Community know that its a plugin AJAX call.
// Second argument should be the plugin name, for instance 'profile'
// Third argument should be the plugin's function name to be called.
// It must be comma separated.
$jaxFuncNames[]	= 'plugins,profile,test';
$jaxFuncNames[]	= 'plugins,profile,saveProfile';
