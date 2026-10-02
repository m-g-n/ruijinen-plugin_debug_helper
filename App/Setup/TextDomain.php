<?php
/**
 * テキストドメインの読み込み
 *
 * @package ruijinen-debug-helper
 * @author mgn
 * @license GPL-2.0+
 */

namespace Ruijinen\DebugHelper\App\Setup;

/**
 * テキストドメインの読み込み
 */
class TextDomain {

	/**
	 * Constructor.
	 */
	public function __construct() {
		load_plugin_textdomain( RJE_DH_PLUGIN_TEXTDOMAIN, false, RJE_DH_PLUGIN_DIRNAME . '/languages' );
		add_filter( 'load_textdomain_mofile', array( $this, 'load_textdomain_mofile' ), 10, 2 );
	}

	/**
	 * When local .mo file exists, load this.
	 *
	 * @param string $mofile Path to the MO file.
	 * @param string $domain Text domain. Unique identifier for retrieving translated strings.
	 * @return string
	 */
	public function load_textdomain_mofile( $mofile, $domain ) {
		if ( RJE_DH_PLUGIN_TEXTDOMAIN === $domain ) {
			$local_mofile = RJE_DH_PLUGIN_PATH . 'languages/' . basename( $mofile );
			if ( file_exists( $local_mofile ) ) {
				return $local_mofile;
			}
		}
		return $mofile;
	}
}
