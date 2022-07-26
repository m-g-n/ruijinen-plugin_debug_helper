<?php
/**
 * 指定のフィルターに何の関数がかかっているかチェック
 */
function print_filters_for( $hook = '' ) {
	global $wp_filter;
	if( empty( $hook ) || !isset( $wp_filter[$hook] ) )
		return;

	error_log(print_r($wp_filter[$hook], true));
	// print '<pre>';
	// print_r( $wp_filter[$hook] );
	// print '</pre>';
}