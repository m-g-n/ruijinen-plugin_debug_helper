<?php
/**
 * WpRecordHook
 * https://kaminarimagazine.com/web/2020/04/24/wordpress%E3%81%A7%E5%AE%9F%E8%A1%8C%E3%81%95%E3%82%8C%E3%81%9F%E3%83%95%E3%83%83%E3%82%AF%E3%82%92%E3%81%99%E3%81%B9%E3%81%A6%E8%A8%98%E9%8C%B2%E3%81%97%E3%81%A6%E3%81%BF%E3%82%88%E3%81%86/
 * 通ったhookを記録して管理者宛にメールを送る
 */

class WpRecordHook {
	private
	$_list    = array(), // 実行済みフックのリスト
	$_excepts = array(),  // 記録から除外するフックの連想配列
	$_isOnce  = false, // 1回記録したフックは記録しないオプション
	$_regex   = '/./';  // 記録するフックのマッチパターン

	function __construct() {
		$this->_init();
	}

	/**
	 * 初期化処理
	 *
	 * @return void
	 */
	function _init() {
		if ( strpos( $_SERVER['REQUEST_URI'], 'wp-admin/admin-ajax.php' ) === false ) { // Heartbeat API による定期的なアクセスを除外する
			add_action( 'all', array( $this, '_rec' ) );
			register_shutdown_function( array( $this, '_send' ) );
		}
	}

	/**
	 * 記録から除外するフック名を指定
	 *
	 * @param  array $excepts
	 * @return void
	 */
	function addExcepts( array $excepts ) {
		foreach ( $excepts as $n ) {
			$this->_excepts[ $n ] = true;
		}
	}

	/**
	 * 記録するフックのマッチパターンを設定
	 *
	 * @param  string $regex
	 * @return void
	 */
	function setRegex( $regex ) {
		$this->_regex = $regex;
	}

	/**
	 * 同じフック名は一度しか記録しないモードを有効化
	 *
	 * @return void
	 */
	function setOnce() {
		$this->_isOnce = true;
	}

	/**
	 * フックの記録
	 *
	 * @return void
	 */
	function _rec() {
		$is_rec = false;
		$hook   = current_filter();
		if ( ! isset( $this->_excepts[ $hook ] ) && preg_match( $this->_regex, $hook ) ) {
			if ( $this->_isOnce ) {
				if ( array_search( $hook, $this->_list, true ) === false ) {
					$is_rec = true;
				}
			} else {
				$is_rec = true;
			}
		}
		if ( $is_rec ) {
			$this->_list[] = $hook;
		}
	}

	/**
	 * 記録結果を管理者メール宛に送信
	 *
	 * @return void
	 */
	function _send() {
		$output  = print_r( $this->_list, true );
		$to      = get_option( 'admin_email' );
		$subject = 'WordPress Hook list.';
		wp_mail( $to, $subject, $output );
	}
}
