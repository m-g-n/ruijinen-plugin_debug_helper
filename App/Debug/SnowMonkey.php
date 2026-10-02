<?php
/**
 * Snow Monkeyテーマに関するデバッグ用のクラス
 *
 * @package ruijinen-debug-helper
 * @author mgn
 * @license GPL-2.0+
 */

namespace Ruijinen\DebugHelper\Debug;

/**
 * Snow Monkeyテーマに関するデバッグ用のクラス
 */
class SnowMonkey {

	/**
	 * URLパラメーターで指定できるレイアウト（値 => 表示名）
	 *
	 * @var array
	 */
	const LAYOUTS = array(
		'blank-content'   => 'ランディングページ（ヘッダー・フッターあり）',
		'blank-slim'      => 'ランディングページ（スリム幅）',
		'blank'           => 'ランディングページ',
		'one-column-full' => 'フル幅',
		'one-column'      => '1カラム',
		'one-column-slim' => '1カラム（スリム幅）',
		'left-sidebar'    => '左サイドバー',
		'right-sidebar'   => '右サイドバー',
	);

	/**
	 * URLパラメーターで指定できるヘッダーレイアウト
	 *
	 * @var array
	 */
	const HEADER_LAYOUTS = array( '1row', '2row', 'center', 'left', 'simple' );

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'snow_monkey_layout', array( $this, 'change_layout' ) );
		add_filter( 'theme_mod_header-layout', array( $this, 'change_header_layout' ) );
		add_action( 'wp_body_open', array( $this, 'view_select_layout' ) );
		add_action( 'wp_footer', array( $this, 'js_change_url' ) );
		add_action( 'wp_footer', array( $this, 'disable_sme_animation' ) );
	}

	/**
	 * URLパラメーターでレイアウトを変更
	 * 値はテンプレートの読み込みパスに使われるため、許可したレイアウト名のみ受け付ける
	 *
	 * @param string $layout レイアウト名.
	 * @return string
	 */
	public function change_layout( $layout ) {
		$new_layout = $this->get_query_value( 'layout' );
		if ( array_key_exists( $new_layout, self::LAYOUTS ) ) {
			return $new_layout;
		}
		return $layout;
	}

	/**
	 * URLパラメーターでヘッダーのレイアウトを変更
	 * 値はテンプレートの読み込みパスに使われるため、許可したレイアウト名のみ受け付ける
	 *
	 * @param string $layout ヘッダーレイアウト名.
	 * @return string
	 */
	public function change_header_layout( $layout ) {
		$header_layout = $this->get_query_value( 'header-layout' );
		if ( in_array( $header_layout, self::HEADER_LAYOUTS, true ) ) {
			return $header_layout;
		}
		return $layout;
	}

	/**
	 * レイアウト選択用のセレクトボックスを表示（管理者権限のみ）
	 */
	public function view_select_layout() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<select id="RJE-DH_layout_select" class="RJE-DH_layout_select">
			<option value="" selected>デフォルト</option>
			<?php foreach ( self::LAYOUTS as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * レイアウト選択が変わった場合にURLにパラメータを付与して再読込する（管理者権限のみ）
	 */
	public function js_change_url() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
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

	/**
	 * パラメータがある場合はSnow Monkey Editorのアニメーションclassを削除
	 */
	public function disable_sme_animation() {
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
				// NOTE: VRTの readyEvent などで参照されている可能性があるため文言は変更しない
				console.log('Stoped Snow Monkey Editor Animations.');
			}
		});
		</script>
		<?php
	}

	/**
	 * URLパラメーターの値を取得
	 *
	 * @param string $key パラメーター名.
	 * @return string パラメーターがない場合は空文字
	 */
	private function get_query_value( $key ) {
		$value = filter_input( INPUT_GET, $key );
		return is_string( $value ) ? sanitize_key( $value ) : '';
	}
}
