<?php
/**
 * CSSの読み込み
 *
 * @package ruijinen-debug-helper
 * @author mgn
 * @license GPL-2.0+
 */

namespace Ruijinen\DebugHelper\App\Setup;

/**
 * CSSの読み込み
 */
class Assets {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// テーマのスタイル登録後に読み込むため優先度を下げる.
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ), 20 );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ), 20 );
	}

	/**
	 * Enqueue front assets
	 */
	public function wp_enqueue_scripts() {
		$this->enqueue_style( RJE_DH_PLUGIN_KEY . '_front', 'dist/css/front.css' );
	}

	/**
	 * Enqueue Block Editor Assets
	 */
	public function enqueue_block_editor_assets() {
		$this->enqueue_style( RJE_DH_PLUGIN_KEY . '_editor', 'dist/css/editor.css' );
	}

	/**
	 * スタイルを読み込む（Snow Monkey 有効時はメインスタイルの後に読み込む）
	 *
	 * @param string $handle ハンドル名.
	 * @param string $path   プラグインディレクトリからのファイルパス.
	 */
	private function enqueue_style( $handle, $path ) {
		$file = RJE_DH_PLUGIN_PATH . $path;
		if ( ! file_exists( $file ) ) {
			return;
		}
		wp_enqueue_style( $handle, RJE_DH_PLUGIN_URL . $path, $this->get_dependencies(), filemtime( $file ) );
	}

	/**
	 * 依存するスタイルのハンドルを取得
	 * テーマの読み込み後に判定する必要があるため、読み込み時に都度取得する
	 * 未登録のハンドルを依存に指定するとスタイル自体が出力されないため、登録済みの場合のみ指定する
	 *
	 * @return array
	 */
	private function get_dependencies() {
		if ( ! method_exists( '\Framework\Helper', 'get_main_style_handle' ) ) {
			return array();
		}
		$handle = \Framework\Helper::get_main_style_handle();
		return wp_style_is( $handle, 'registered' ) ? array( $handle ) : array();
	}
}

new Assets();
