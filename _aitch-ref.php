<?
/*
Plugin Name: aitch-ref!
Plugin URI: http://wordpress.org/extend/plugins/aitch-ref/
Description: href junk. Requires PHP 5.
Version: 0.12
Author: Eric Eaglstun
Author URI: http://ericeaglstun.com
*/

if( (int) phpversion() < 5 ){
	echo( "<h1>aitch-ref! requires PHP 5.</h1>" );
	return;
}

// these can return back urls starting with /
add_filter( 'admin_url', 'AitchRef::_site_url' );
add_filter( 'bloginfo', 'AitchRef::_site_url' );
add_filter( 'bloginfo_url', 'AitchRef::_site_url' );
//add_filter( 'get_permalink', 'AitchRef::_site_url' );
add_filter( 'option_url', 'AitchRef::_site_url' );
add_filter( 'post_link', 'AitchRef::_site_url' );
add_filter( 'the_content', 'AitchRef::_site_url' );
add_filter( 'url', 'AitchRef::_site_url' );

// these need to return back with leading http://
add_filter( 'option_siteurl', 'AitchRef::_site_url' );
add_filter( 'site_url', 'AitchRef::_site_url_absolute' );

// admin
add_action( 'admin_menu', 'AitchRef::_admin_menu' );
add_filter( 'plugin_action_links_'.plugin_basename(__FILE__), 'AitchRef::_admin_plugins' );

AitchRef::_setup();

class AitchRef{
	
	static $baseurl = 'http://';
	static $cwd = 'plugins/aitch-ref';
	static $messages = array();
	static $possible = array();
	
	static public function _setup(){
		self::$baseurl = 'http://'.$_SERVER['HTTP_HOST'];
		self::$cwd = dirname(__FILE__);
		self::$possible = self::getUrls( TRUE );
	}
	
	// filters
	static public function _site_url( $url ){
		$url2 = str_replace( self::$possible, '', $url );
		return $url2;		
	}
	
	static public function _site_url_absolute( $url ){
		$url2 = str_replace( self::$possible, self::$baseurl, $url ); 
		return $url2;
	}
	
	// admin 
	static public function _admin_menu(){
		add_options_page( 'AitchRef Settings', 'aitch ref!', 1, 'aitch-ref', 'AitchRef::_options_page' );
	}
	
	static public function _admin_plugins( $links ){
		$settings_link = '<a href="options-general.php?page=aitch-ref">Settings</a>';  
		array_unshift( $links, $settings_link );
		return $links;
	}
	
	static public function _options_page(){
		if( isset($_POST['urls']) ){
			self::updateUrls($_POST['urls']);
		}
		
		$vars = (object) array();
		$vars->messages = implode( "\n", self::$messages );
		$vars->urls = self::getUrls();
		self::render('admin', $vars);
	}
	
	// db interaction
	static private function getUrls( $as_array = FALSE ){
		$urls = (array) get_option( 'aitchref_urls' );
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
		update_option( 'aitchref_urls', $urls );
		
		array_push( self::$messages, '<div class="updated fade"><p>aitch-ref! updated</p></div>' );
	}
	
	// render a page into wherever
	static private function render( $filename, $vars = array() ){
		$path = self::$cwd.'/'.$filename.'.php';
		if( file_exists($path) ){
			extract( (array) $vars, EXTR_SKIP );
			include $path;
		}
	}
}