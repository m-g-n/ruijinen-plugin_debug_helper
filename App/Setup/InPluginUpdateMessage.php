<?php
/**
 * プラグイン更新のアラートボックスに追加メッセージを表示する
 *
 * @author mgn
 * @license GPL-2.0+
 * @package ruijinen-debug-helper
 */

namespace Ruijinen\DebugHelper\App\Setup;

/**
 * プラグイン更新のアラートボックスに追加メッセージを表示する
 */
class InPluginUpdateMessage {

	/**
	 * 取得したお知らせJSONをキャッシュする時間（秒）
	 */
	const CACHE_EXPIRATION = 12 * HOUR_IN_SECONDS;

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'in_plugin_update_message-' . RJE_DH_PLUGIN_BASENAME, array( $this, 'in_plugin_update_message' ), 10, 2 );
	}

	/**
	 * 更新画面のアラートボックスにメッセージを追加
	 *
	 * @param array  $data     プラグインのデータ.
	 * @param object $response 更新情報.
	 */
	public function in_plugin_update_message( $data, $response ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- フックの引数.
		if ( empty( $data['new_version'] ) ) {
			return;
		}
		$messages = $this->get_the_notice_json( $data['new_version'] );
		if ( empty( $messages['message'] ) ) {
			return;
		}
		echo '<br>' . wp_kses_post( $messages['message'] );
		$url = ! empty( $messages['url'] ) ? esc_url( $messages['url'] ) : '';
		if ( $url ) {
			echo '<a href="' . $url . '" target="_blank" rel="noopener"> &#62;&#62;詳細を見る</a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- esc_url 済み.
		}
	}

	/**
	 * JSONからデータを取得して指定バージョンのメッセージを返す
	 *
	 * @param string $version バージョン番号.
	 * @return array|false メッセージ情報（message, url）. ない場合は false
	 */
	private function get_the_notice_json( $version ) {
		$notices = $this->fetch_notices();
		if ( ! isset( $notices[ $version ] ) || ! is_array( $notices[ $version ] ) ) {
			return false;
		}
		return $notices[ $version ];
	}

	/**
	 * お知らせJSONを取得してバージョンをキーにした配列で返す（結果は一定時間キャッシュ）
	 *
	 * @return array
	 */
	private function fetch_notices() {
		$cache_key = RJE_DH_PLUGIN_KEY . '_update_notice';
		$notices   = get_site_transient( $cache_key );
		if ( is_array( $notices ) ) {
			return $notices;
		}

		$notices  = array();
		$response = wp_remote_get(
			'https://rui-jin-en.com/update-notice/' . RJE_DH_PLUGIN_KEY . '.json',
			array( 'timeout' => 5 )
		);
		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			$body = wp_remote_retrieve_body( $response );
			if ( function_exists( 'mb_convert_encoding' ) ) {
				$body = mb_convert_encoding( $body, 'UTF-8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN' ); // 文字コードをUTF-8に変換.
			}
			$json = json_decode( $body, true );
			// バージョンごとのオブジェクトの配列になっているため、階層を1つ浅くする.
			if ( is_array( $json ) ) {
				foreach ( $json as $item ) {
					if ( is_array( $item ) ) {
						$notices = array_merge( $notices, $item );
					}
				}
			}
		}

		// 取得に失敗した場合も空配列をキャッシュし、画面表示のたびに通信しないようにする.
		set_site_transient( $cache_key, $notices, self::CACHE_EXPIRATION );
		return $notices;
	}
}
