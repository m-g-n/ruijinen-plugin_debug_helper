<?php
/**
 * Snow Monkeyテーマに関するデバッグ用のクラス
 */

namespace Ruijinen\DebugHelper\Debug;

class SnowMonkey {
	public function __construct() {
		add_filter( 'snow_monkey_layout', array($this, 'change_layout') );
		add_filter( 'theme_mod_header-layout', array($this, 'change_header_layout') );
		add_action( 'wp_body_open', array($this, 'view_select_layout') );
		add_action( 'wp_footer', array($this, 'js_change_url') );
		add_action( 'wp_footer', array($this, 'disable_sme_animation') );
	}

	/**
	 * URLパラメーターでレイアウトを変更
	 * ソース元：https://m-g-n.slack.com/archives/CHDKE3JF4/p1627467479030000?thread_ts=1627467339.029800&cid=CHDKE3JF4
	 */
	public function change_layout ( $layout ) {
		$new_layout = filter_input( INPUT_GET, 'layout' );
		if ( ! is_null( $new_layout ) ) {
			return $new_layout;
		}
		return $layout;
	}

	/**
	 * URLパラメーターでヘッダーのレイアウトを変更
	 */
	public function change_header_layout ( $layout ) {
		$header_layout = filter_input( INPUT_GET, 'header-layout' );
		if ( ! is_null( $header_layout ) ) {
			return $header_layout;
		}
		return $layout;
	}

	/**
	 * レイアウト選択用のセレクトボックスを表示（管理者権限のみ）
	 */
	public function view_select_layout () {
		if ( current_user_can( 'administrator' ) ) {
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

	/**
	 * レイアウト選択が変わった場合にURLにパラメータを付与して再読込する（管理者権限のみ）
	 */
	public function js_change_url () {
		if ( current_user_can( 'administrator' ) ) {
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
		<?php
		}
	}

	/**
	 * パラメータがある場合はSnow Monkey Editorのアニメーションclassを削除
	 */
	public function disable_sme_animation () {
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
		// document.addEventListener('load', function() {
		// 	const key    = 'sme_animation';
		// 	const url    = new URL(location);
		// 	//指定のパラメータがある場合はアニメーションを止める
		// 	if ( 'stop' === url.searchParams.get( key ) ) {
		// 		console.log('Stoped Snow Monkey Editor Animations.');
		// 	}
		// });
		</script>
		<?php
	}
}
