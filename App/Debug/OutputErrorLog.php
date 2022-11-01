<?php
/**
 * error_log()の出力内容を任意のファイルに出力する
 */
namespace Ruijinen\DebugHelper\Debug;

/**
 * error_log()の出力内容を任意のファイルに出力する
 */
class Output_error_log {
	/**
	 * Constructor.
	 * @param $data 出力内容
	 * @param $reset erro_logファイルの中身をリセットするか true=>リセット, false=>リセットしない
	 * @param $file error_logの内容の出力先
	 *
	 * 呼び出し方： new Debug\Output_error_log('テスト');
	 */
	public function __construct( $data = '', $reset = false, $file = __DIR__ . '/error_log') {
		if ( true === $reset ) {
			file_put_contents( $file, ''); //ログファイルを空白にする（リセット）
		}
		error_log( print_r( $data, true )."\n", 3, $file );
	}
}
