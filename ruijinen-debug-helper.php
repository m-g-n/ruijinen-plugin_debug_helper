<?php
/**
 * Plugin name: 類人猿デバッグサポート
 * Description: 類人猿パターンプラグインのデバッグをサポートする機能を搭載
 * Version: 0.0.3
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

		//アクティベートチェックを行い問題がある場合はメッセージを出し離脱する.
		$activate_check = new App\Setup\ActivateCheck();
		if ( !empty( $activate_check->messages ) ) {
			add_action('admin_notices', array( $activate_check,'make_alert_message'));
			return;
		}

		/**
		 * URLパラメーターでレイアウトを変更
		 * ソース元：https://m-g-n.slack.com/archives/CHDKE3JF4/p1627467479030000?thread_ts=1627467339.029800&cid=CHDKE3JF4
		 */
		add_filter(
			'snow_monkey_layout',
			function( $layout ) {
				$new_layout = filter_input( INPUT_GET, 'layout' );
				if ( ! is_null( $new_layout ) ) {
					return $new_layout;
				}
				return $layout;
			}
		);
		add_filter(
			'theme_mod_header-layout',
			function( $layout ) {
				$header_layout = filter_input( INPUT_GET, 'header-layout' );
				if ( ! is_null( $header_layout ) ) {
					return $header_layout;
				}
				return $layout;
			}
		);

		/**
		 * レイアウト変更しやすいようにセレクトボックスを表示（ログイン時のみ表示）
		 */
		add_action(
			'wp_body_open',
			function(){
				if ( is_user_logged_in() ) {
					?>
		<select id="RJE-DH_layout_select" class="RJE-DH_layout_select">
			<option value="" selected>デフォルト</option>
			<option value="blank-content">ランディングページ（ヘッダー・フッターあり）</option>
			<option value="blank-slim">ランディングページ（スリム幅）</option>
			<option value="blank">ランディングページ</option>
			<option value="one-column-full">フル幅</option>
			<option value="one-column">1カラム</option>
			<option value="one-column-slim">1カラム（スリム幅）</option>
			<option value="left-sidebar">左サイドバー</option>
			<option value="right-sidebar">右サイドバー</option>
		</select>
					<?php
				}
			}
		);

		/**
		 * セレクトボックスの値変更された場合にURLパラメータを更新し遷移
		 * 参考：https://r17n.page/2019/08/22/js-manipulate-query-params/
		 */
		add_action(
			'wp_footer',
			function(){
				if ( current_user_can( 'administrator' ) ) :
					?>
		<script>
			document.addEventListener('DOMContentLoaded', function() {
				//変数
				const target = document.getElementById('RJE-DH_layout_select');
				const key    = 'layout';
				const url    = new URL(location);

				//セレクトボックスが存在しない場合は離脱
				if ( !target ) {
					return;
				}
				//パラメータがある場合はセレクトボックスの値を設定
				if ( url.searchParams.get( key ) ) {
					target.value = url.searchParams.get( key );
				}
				//セレクトボックスの値変更時にパラメータ値を書き換えて遷移
				target.addEventListener('change', (event) => {
					const selected = event.target.value;
					if ( '' === selected ) {
						url.searchParams.delete(key);
					} else {
						url.searchParams.set( key, selected );
					}
					window.location.href = url.href;
				});
			});
		</script>
					<?
				endif;
			}
		);

		/**
		 * パラメータがある場合はSnow Monkey Editorのアニメーションclassを削除
		 */
		add_action(
			'wp_footer',
			function(){
					?>
		<script>
			document.addEventListener('DOMContentLoaded', function() {
				const key    = 'sme_animation';
				const url    = new URL(location);
				const smeAnimationClasses = [
					"sme-animation-bounce-in",
					"sme-animation-bounce-down",
					"sme-animation-fade-in",
					"sme-animation-fade-in-up",
					"sme-animation-fade-in-down"
				];
				//指定のパラメータがある場合はアニメーションを止める
				if ( 'stop' === url.searchParams.get( key ) ) {
					smeAnimationClasses.forEach(function ( classname ) {
						let elements = document.getElementsByClassName(classname);
						while ( 0 < elements.length ) {
							elements[0].classList.remove(classname);
						}
					});
					console.log('Stoped Snow Monkey Editor Animations.');
				}
			});
			document.addEventListener('load', function() {
				const key    = 'sme_animation';
				const url    = new URL(location);
				//指定のパラメータがある場合はアニメーションを止める
				if ( 'stop' === url.searchParams.get( key ) ) {
					console.log('Stoped Snow Monkey Editor Animations.');
				}
			});
		</script>
					<?
			}
		);
	}

	/**
	 * Load Textdomain.
	 */
	public function load_textdomain() {
		new App\Setup\TextDomain();
	}
}

new Bootstrap();
