<?php
/**
 * Plugin name: 類人猿デバッグサポート
 * Description: 類人猿パターンプラグインのデバッグをサポートする機能を搭載
 * Version: 0.0.8
 *
 * @package ruijinen-debug-helper
 * @author mgn
 * @license GPL-2.0+
 */

namespace Ruijinen\DebugHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 定数を宣言
 */
define( 'RJE_DH_PLUGIN_KEY', 'RJE_Debug_Helper' ); // このプラグインのユニークキー
define( 'RJE_DH_PLUGIN_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) . '/' ); // このプラグインのURL
define( 'RJE_DH_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/' ); // このプラグインのパス
define( 'RJE_DH_PLUGIN_BASENAME', plugin_basename( __FILE__ ) ); // このプラグインのベースネーム.
define( 'RJE_DH_PLUGIN_TEXTDOMAIN', 'ruijinen-block-patterns' ); //テキストドメイン名.
define( 'RJE_DH_PLUGIN_DIRNAME', basename(__DIR__) ); //このディレクトリーのパス.

/**
 * include files.
 */
require_once(RJE_DH_PLUGIN_PATH . 'vendor/autoload.php'); //アップデート用composer.

//各処理用のクラスを読み込む
foreach (glob(RJE_DH_PLUGIN_PATH.'App/**/*.php') as $filename) {
	require_once $filename;
}
/**
 * 初期設定.
 */
class Bootstrap {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', [ $this, 'bootstrap' ] );
		add_action( 'init', [ $this, 'load_textdomain' ] );
	}

	/**
	 * Bootstrap.
	 */
	public function bootstrap() {
		//初期実行
		new App\Setup\AutoUpdate(); //自動更新確認

		//汎用的なデバッグ用のクラス定義
		$debug_common = new Debug\ViewListFilterFromHook();
		// $debug_common->error_log_list_filter('wp_head'); //NOTE:error_logでフックにかかってる関数一覧を出すコード

		//Snow Monkeyテーマが有効かチェックし、有効の場合のみSnow Monkey用のデバッグ関数を読み込む
		$theme = wp_get_theme( get_template() );
		if ( 'snow-monkey' == $theme->template || 'snow-monkey/resources' == $theme->template ) {
			new Debug\SnowMonkey();
		}
	}

	/**
	 * Load Textdomain.
	 */
	public function load_textdomain() {
		new App\Setup\TextDomain();
	}
}

new Bootstrap();
