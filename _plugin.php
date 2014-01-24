<?php
/*
Plugin Name: aitch-ref!
Plugin URI: http://wordpress.org/extend/plugins/aitch-ref/
Description: href junk. Requires PHP >= 5.3 and Wordpress >= 3.0
Version: 0.9
Author: Eric Eaglstun
Author URI: http://ericeaglstun.com
*/

register_activation_hook( __FILE__, create_function("", '$ver = "5.3"; if( version_compare(phpversion(), $ver, "<") ) die( "This plugin requires PHP version $ver or greater be installed." );') );

require __DIR__.'/index.php';