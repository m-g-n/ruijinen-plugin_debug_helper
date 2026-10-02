<?php
/**
 * 任意のファイルに error_log() で内容を出力する
 *
 * @package ruijinen-debug-helper
 * @author mgn
 * @license GPL-2.0+
 */

namespace Ruijinen\DebugHelper\Debug;

// phpcs:disable PEAR.NamingConventions.ValidClassName.Invalid -- クラス名は README に記載している公開APIのため変更しない.
/**
 * 任意のファイルに error_log() で内容を出力する
 */
class Output_error_log {
	// phpcs:enable

	/**
	 * 出力前にファイルの中身を空にするか
	 *
	 * @var bool
	 */
	public $reset = false;

	/**
	 * 出力先のファイルパス
	 *
	 * @var string
	 */
	public $file = __DIR__ . '/error_log';

	/**
	 * 指定の内容をファイルに出力する
	 *
	 * @param mixed $data 出力内容. false や 0 などもそのまま出力する.
	 */
	public function output_error_log( $data = '' ) {
		if ( '' === $data ) {
			return;
		}
		if ( true === $this->reset ) {
			file_put_contents( $this->file, '' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- デバッグ用途のため直接書き込む.
		}
		// print_r では false や null が空文字になるため、bool と null は var_export で出力する.
		$output = ( is_bool( $data ) || is_null( $data ) ) ? var_export( $data, true ) : print_r( $data, true ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions -- デバッグ用途.
		error_log( $output . "\n", 3, $this->file ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- デバッグ用途.
	}
}
