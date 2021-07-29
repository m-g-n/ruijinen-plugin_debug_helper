<?php
/**
 * Plugin name: 類人猿デバッグサポート
 * Description: 類人猿パターンプラグインのデバッグをサポートする機能を搭載
 * Version: 0.0.0.1
 *
 * @package ruijinen-debug-helper
 * @author mgn
 * @license GPL-2.0+
 */

/**
 * Snow Monkey 以外のテーマを利用している場合は有効化してもカスタマイズが反映されないようにする
 */
$theme = wp_get_theme();
if ( 'snow-monkey' !== $theme->template && 'snow-monkey/resources' !== $theme->template ) {
	return;
}

/**
 * 定数を宣言
 */
define( 'RJE_DH_PLUGIN_URL', plugins_url( '', __FILE__ ) ); // このプラグインのURL
define( 'RJE_DH_PLUGIN_PATH', plugin_dir_path( __FILE__ ) ); // このプラグインのパス


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
