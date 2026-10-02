<?php
/**
 * Plugin name: 類人猿デバッグサポート
 * Description: 類人猿パターンプラグインのデバッグをサポートする機能を搭載
 * Version: 0.0.9
 * Requires PHP: 7.4
 * Text Domain: ruijinen-debug-helper
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
define( 'RJE_DH_PLUGIN_KEY', 'RJE_Debug_Helper' ); // このプラグインのユニークキー.
define( 'RJE_DH_PLUGIN_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) . '/' ); // このプラグインのURL.
define( 'RJE_DH_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/' ); // このプラグインのパス.
define( 'RJE_DH_PLUGIN_BASENAME', plugin_basename( __FILE__ ) ); // このプラグインのベースネーム.
define( 'RJE_DH_PLUGIN_TEXTDOMAIN', 'ruijinen-debug-helper' ); // テキストドメイン名.
define( 'RJE_DH_PLUGIN_DIRNAME', basename( __DIR__ ) ); // このプラグインのディレクトリ名.

/**
 * Include files.
 */
require_once RJE_DH_PLUGIN_PATH . 'vendor/autoload.php'; // アップデート用composer.

// 各処理用のクラスを読み込む.
foreach ( glob( RJE_DH_PLUGIN_PATH . 'App/*/*.php' ) as $filename ) {
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
		add_action( 'plugins_loaded', array( $this, 'bootstrap' ) );
		add_action( 'init', array( $this, 'load_textdomain' ) );
	}

	/**
	 * Bootstrap.
	 */
	public function bootstrap() {
		// 初期実行.
		new App\Setup\AutoUpdate(); // 自動更新確認.
		new App\Setup\InPluginUpdateMessage(); // 更新アラートメッセージに追加でメッセージを表示.

		// 汎用的なデバッグ用のクラス定義.
		new Debug\ViewListFilterFromHook();

		// Snow Monkeyテーマが有効かチェックし、有効の場合のみSnow Monkey用のデバッグ関数を読み込む.
		$theme = wp_get_theme( get_template() );
		if ( in_array( $theme->template, array( 'snow-monkey', 'snow-monkey/resources' ), true ) ) {
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
