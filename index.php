<?php

namespace aitchref;

if( is_admin() )
	require __DIR__.'/admin.php';

require __DIR__.'/lib/functions.php';
			
class AitchRef{
	// these will be overwritten in setup()
	private static $baseurl = 'http://';						// is_ssl()
	private static $blog_id = 1;								// multiuser support
	private static $cwd = '/var/www/plugins/aitch-ref';			// full server path to current directory
	
	private static $possible = array();							// a list of the possible base urls that 
																// can be replaced
	 
	/*
	*	runs once when plugin has loaded, sets up vars and adds filters/actions
	*	@return NULL
	*/
	public static function setup(){
		$pathinfo = pathinfo(__FILE__);
		
		global $blog_id;
		self::$blog_id = $blog_id;
		self::$possible = get_urls( TRUE );
		
		self::$baseurl = is_ssl() ? 'https://'.$_SERVER['HTTP_HOST'] : 'http://'.$_SERVER['HTTP_HOST'];
		self::$cwd = $pathinfo['dirname'];
		
		// these can return back urls starting with /
		$relative = array( 'bloginfo', 'bloginfo_url', 'content_url', 'get_pagenum_link',
						   'option_url', 'plugins_url', 'pre_post_link', 'script_loader_src',
						   'style_loader_src', 'term_link', 'the_content', 'upload_dir',
						   'url', 'wp_list_pages' );
		$relative = apply_filters( 'aitch-ref-relative', $relative );
					   
		foreach( $relative as $filter )
			add_filter( $filter, __NAMESPACE__.'\AitchRef::site_url' );
		
		// these need to return back with leading http://
		$absolute = array( 'admin_url', 'get_permalink', 'home_url', 'login_url',
						   'option_home', 'option_siteurl', 'page_link', 'post_link',
						   'siteurl', 'site_url', 'stylesheet_uri', 
						   'template_directory_uri', 'wp_get_attachment_url' );
		$absolute = apply_filters( 'aitch-ref-relative', $absolute );
		
		foreach( $absolute as $filter )
			add_filter( $filter, __NAMESPACE__.'\AitchRef::site_url_absolute' );
	}
	
	/*
	*	add_filter callback
	*	@param mixed
	*	@return mixed
	*/
	public static function site_url( $url ){
		if( is_array($url) ){
			// this is to fix an issue in 'upload_dir' filter, 
			// $url[error] needs to be a boolean but str_replace casts to string
			$url2 = str_replace( self::$possible, '', array_filter($url) );
			$url2 = array_merge( $url, $url2 );
		} else {
			$url2 = str_replace( self::$possible, '', $url );
		}
			
		return $url2;		
	}
	
	/*
	*	add_filter callback
	*	@param mixed
	*	@return mixed
	*/
	public static function site_url_absolute( $url ){
		if( is_array($url) ){
			// this is to fix a bug in 'upload_dir' filter, 
			// $url[error] needs to be a boolean but str_replace casts to string
			$url2 = str_replace( self::$possible, self::$baseurl, array_filter($url) );
			$url2 = array_merge( $url, $url2 );
		} else {
			$url2 = str_replace( self::$possible, self::$baseurl, $url );
		}
		
		// what is this??
		if( strpos($url2, self::$baseurl) !== 0 && strpos($url2, 'http://') !== 0 ){
			$url2 = self::$baseurl.$url2;
		}
		
		return $url2;
	}
}

AitchRef::setup();

/*
*	db interaction
*	@param bool
*	@return string | array
*/
function get_urls( $as_array = FALSE ){
	$urls = get_option( 'aitchref_urls' );
	
	// backwards compat, now storing this option as a json encoded string cuz im a maverick
	if( !is_array($urls) )
		$urls = (array) json_decode( $urls );
	
	if( !$as_array )
		$urls = implode( "\n", $urls );
	
	return $urls;
}

// MU wrappers

/*
*
*	@param
*	@return
*/
function delete_option( $key ){
	global $blog_id;
	return is_multisite() ? \delete_blog_option( $blog_id, $key ) : \delete_option( $key );
}

/*
*
*	@param
*	@return
*/
function get_option( $key ){
	global $blog_id;
	return is_multisite() ? \get_blog_option( $blog_id, $key ) : \get_option( $key );
}

/*
*
*	@param
*	@param
*	@return
*/
function update_option( $key, $val ){
	global $blog_id;
	return is_multisite() ? \update_blog_option( $blog_id, $key, $val ) : \update_option( $key, $val );
}