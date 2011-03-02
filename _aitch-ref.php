<?php
/*
Plugin Name: aitch-ref!
Plugin URI: http://wordpress.org/extend/plugins/aitch-ref/
Description: href junk. Requires PHP >= 5.2 and Wordpress >= 3.0
Version: 0.51
Author: Eric Eaglstun
Author URI: http://ericeaglstun.com
*/

class AitchRef{
	
	private static $baseurl = 'http://';
	private static $cwd = '/var/www/plugins/aitch-ref';			// full server path to current directory
	private static $is_mu = TRUE;
	private static $messages = array();							// error / success messages to user
	private static $path = '/wp-content/plugins/aitch-ref/';	// web accessible path to current to current directory, w trailing slash
	private static $possible = array();							// a list of the possible base urls that can be replaced
	private static $render = '';								// path to view being rendered (currently only admin)
	
	// run once on setup
	static public function _setup(){
		$pathinfo = pathinfo(__FILE__);
		
		self::$is_mu = is_multisite();
		self::$possible = self::getUrls( TRUE );
		
		self::$baseurl = 'http://'.$_SERVER['HTTP_HOST'];
		self::$cwd = $pathinfo['dirname'];
		
		// sorry if this is confusing, some servers (media temple) have strangeness using $_SERVER['DOCUMENT_ROOT']
		self::$path = self::_site_url_absolute(WP_PLUGIN_URL).'/'.basename( $pathinfo['dirname'] ).'/';
		
		// these can return back urls starting with /
		add_filter( 'admin_url', 'AitchRef::_site_url' );
		add_filter( 'bloginfo', 'AitchRef::_site_url' );
		add_filter( 'bloginfo_url', 'AitchRef::_site_url' );
		add_filter( 'get_pagenum_link', 'AitchRef::_site_url' );
		add_filter( 'option_home', 'AitchRef::_site_url' );
		add_filter( 'option_url', 'AitchRef::_site_url' );
		add_filter( 'plugins_url', 'AitchRef::_site_url' );
		add_filter( 'post_link', 'AitchRef::_site_url' );
		add_filter( 'the_content', 'AitchRef::_site_url' );
		add_filter( 'upload_dir', 'AitchRef::_site_url' );
		add_filter( 'url', 'AitchRef::_site_url' );
		add_filter( 'wp_list_pages', 'AitchRef::_site_url' );
		
		// these need to return back with leading http://
		add_filter( 'get_permalink', 'AitchRef::_site_url_absolute' ); 
		add_filter( 'home_url', 'AitchRef::_site_url_absolute' );
		add_filter( 'option_siteurl', 'AitchRef::_site_url_absolute' );
		add_filter( 'page_link', 'AitchRef::_site_url_absolute' ); 
		add_filter( 'pre_post_link', 'AitchRef::_site_url_absolute' );
		add_filter( 'site_url', 'AitchRef::_site_url_absolute' );
		add_filter( 'template_directory_uri', 'AitchRef::_site_url_absolute' );	
		
		// admin
		add_action( 'admin_menu', 'AitchRef::_admin_menu' );
		add_filter( 'plugin_action_links_'.plugin_basename(__FILE__), 'AitchRef::_admin_plugins' );
	}
	
	// add_filter callback
	static public function _site_url( $url ){
		$url2 = str_replace( self::$possible, '', $url );
		return $url2;		
	}
	
	// add_filter callback
	static public function _site_url_absolute( $url ){
		$url2 = str_replace( self::$possible, self::$baseurl, $url ); 
		return $url2;
	}
	
	// show options in 'settings' sidebar
	static public function _admin_menu(){
		add_options_page( 'AitchRef Settings', 'aitch ref!', 'manage_options', 'aitch-ref', 'AitchRef::_options_page' );
	}
	
	// add 'settings' link in main plugins page
	static public function _admin_plugins( $links ){
		$settings_link = '<a href="options-general.php?page=aitch-ref">Settings</a>';  
		array_unshift( $links, $settings_link );
		return $links;
	}
	
	// callback for add_options_page() to render options page in admin 
	static public function _options_page(){
		if( isset($_POST['urls']) ){
			self::updateUrls($_POST['urls']);
		}
		
		$vars = (object) array();
		$vars->messages = implode( "\n", self::$messages );
		$vars->path = self::$path;
		$vars->urls = self::getUrls();
		self::render( 'admin', $vars );
	}
	
	// db interaction
	static private function getUrls( $as_array = FALSE ){
		$urls = self::get_option( 'aitchref_urls' );
		
		// backwards compat, now storing this option as a json encoded string cuz im a maverick
		if( !is_array($urls) ){
			$urls = (array) json_decode( $urls );
		}
		
		if( $as_array ){
			return $urls;
		} else {
			$str = implode( "\n", $urls );
			return $str;
		}
	}
	
	static private function updateUrls( $str ){
		$urls = preg_split ("/\s+/", $str);
		sort( $urls );
		foreach( $urls as $k=>$url ){
			// no trailing slash!
			if( strrpos($url, '/') == (strlen($url)-1) ){
				$urls[$k] = substr( $url, 0, -1 );
			}
		}
		
		$urls = json_encode( $urls );
		self::update_option( 'aitchref_urls', $urls );
		
		array_push( self::$messages, '<div class="updated fade"><p>aitch-ref! updated</p></div>' );
	}
	
	// render a page into wherever
	static private function render( $filename, $vars = array() ){
		self::$render = self::$cwd.'/'.$filename.'.php';
		if( file_exists(self::$render) ){
			extract( (array) $vars, EXTR_SKIP );
			include self::$render;
		}
	}
	
	// wrappers for get_option, MU / single blog installs
	static private function get_option( $key ){
		return self::$is_mu ? get_blog_option( 1, $key ) : get_option( $key );
	}
	
	static private function update_option( $key, $val ){
		return self::$is_mu ? update_blog_option( 1, $key, $val ) : update_option( $key, $val );
	}
	
	static private function delete_option( $key ){
		return self::$is_mu ? delete_blog_option( 1, $key ) : delete_option( $key );
	}
}

AitchRef::_setup();