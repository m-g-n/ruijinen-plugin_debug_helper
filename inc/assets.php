<?php
/**
 * @package ruijinen-debug-helper
 * @author mgn
 * @license GPL-2.0+
 */

namespace Ruijinen\debug\App\Setup;

class Assets {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', [ $this, 'wp_enqueue_scripts' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_editor_assets' ] );
	}

	/**
	 * Enqueue front assets
	 */
	public function wp_enqueue_scripts() {
		$path = 'dist/css/front.css';
		wp_enqueue_style( RJE_DH_PLUGIN_KEY . '_front', RJE_DH_PLUGIN_URL . $path, [ \Framework\Helper::get_main_style_handle() ], filemtime( RJE_DH_PLUGIN_PATH . $path ) );
	}

	/**
	 * Enqueue Block Editor Assets
	 */
	public function enqueue_block_editor_assets() {
		$path = 'dist/css/editor.css';
		wp_enqueue_style( RJE_DH_PLUGIN_KEY . '_editor', RJE_DH_PLUGIN_URL . $path, [ \Framework\Helper::get_main_style_handle() ], filemtime( RJE_DH_PLUGIN_PATH . $path ) );
	}
}

new Assets();
