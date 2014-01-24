<?php

/*
*	helper for AitchRef to use directly in templates
*	@param string the url
*	@param bool to use absolute or not
*	@return string
*/
function aitch( $url, $absolute = FALSE ){
	if( $absolute )
		return aitchref\AitchRef::site_url_absolute( $url );
	else
		return aitchref\AitchRef::site_url( $url );
}