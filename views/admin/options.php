<?php

namespace aitchref;

/*
*	show link to admin options in 'settings' sidebar
*
*/
function admin_menu(){
	add_options_page( 'aitch ref! Settings', 'aitch ref!', 'manage_options', 'aitch-ref', __NAMESPACE__.'\options_general' );
}
add_action( 'admin_menu', __NAMESPACE__.'\admin_menu' );

/*
*	add 'settings' link in main plugins page
*	attached to plugin_action_links_* action
*	@param array
*	@return array
*/
function admin_plugins( $links ){
	$settings_link = '<a href="options-general.php?page=aitch-ref">Settings</a>';  
	array_unshift( $links, $settings_link );
	return $links;
}
add_filter( 'plugin_action_links_aitch-ref/_plugin.php', __NAMESPACE__.'\admin_plugins' );
	
/*
*
*	@param string
*	@return array
*/
function message( $string = NULL ){
	static $messages = NULL ;
	
	if( is_null($messages) )
		$messages = array();
		
	if( !is_null($string) )
		$messages[] = $string;
		
	return $messages;
}

/*
*	callback for add_options_page() to render options page in admin 
*
*/
function options_general(){
	if( isset($_POST['urls']) ){
		update_urls( $_POST['urls'] );
	}
	
	$vars = (object) array();
	
	$vars->messages = implode( "\n", message() );
	$vars->path = plugins_url( '', __FILE__ );
	$vars->urls = esc_textarea( get_urls() );
	
	render( 'admin/options-general', $vars );
}

/*
*	render a page into wherever
*	(only used in admin screen)
*	@param string
*	@param object|array
*	@return
*/
function render( $filename, $vars = array() ){
	$template = __DIR__.'/views/'.$filename.'.php';
	if( file_exists($template) ){
		extract( (array) $vars, EXTR_SKIP );
		include $template;
	}
}
	
/*
*
*	@param string
*	@return
*/
function update_urls( $str ){
	$urls = preg_split ("/\s+/", $str);
	$urls = array_map( 'trim', $urls );
	$urls = array_unique( $urls );
	sort( $urls );
	
	foreach( $urls as $k=>$url ){
		// no trailing slash!
		if( strrpos($url, '/') == (strlen($url)-1) ){
			$urls[$k] = substr( $url, 0, -1 );
		}
	}
	
	$urls = json_encode( $urls );
	update_option( 'aitchref_urls', $urls );
	
	message( '<div class="updated fade"><p>aitch-ref! updated</p></div>' );
}