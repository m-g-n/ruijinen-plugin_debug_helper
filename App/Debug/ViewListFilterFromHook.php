<?php
/**
 * 指定のフィルターに何の関数がかかっているかチェック
 */

namespace Ruijinen\DebugHelper\Debug;

class ViewListFilterFromHook {

	/**
	 * Construct
	 */
	public function __construct () {
		if ( !shortcode_exists( 'list_filter_from_hook' ) ) {
			add_shortcode('list_filter_from_hook', array( $this, 'shortcode_list_filter' ) );
		}
	}

	/**
	 * 指定のフックに何の関数がかかっているかを表示するショートコード
	 * ex. [list_filter_from_hook hook_name="wp_head"]
	 *
	 * @param $atts ショートコードの引数など
	 */
	public function shortcode_list_filter ( $atts ) {
		extract(shortcode_atts(array(
			'hook_name' => '',
		), $atts));
		$lists = $this->get_filer_list( $hook_name );
		if ( !$lists )
			return;
		$html = '<h2>'.$hook_name.'を使用してる関数・メソッド等</h2><ul>';
		foreach ( $lists as $priority => $callbacks ) {
			foreach ( $callbacks as $value ) {
				$html .= '<li>' . $priority . ', ' . $this->get_function_info( $value['function'] ) . '</li>';
			}
		}
		$html .= '</ul>';
		return $html;
	}

	/**
	 * 指定のフックに何の関数がかかっているかをerror_logに出力する
	 * ex. Output_error_log({出力したい内容}, {ファイルをリセットしてから出力するか}, {出力先})
	 *
	 * @param $hook_name 出力したいフック名
	 */
	public function error_log_list_filter( $hook_name = '', $reset = false, $file = '' ) {
		add_action (
			'wp_footer',
			function() use ( $hook_name, $reset, $file ) {
				$lists = $this->get_filer_list( $hook_name );
				if ( !$lists )
					return;
				$output = '**** '.$hook_name.'を使用してる関数・メソッド等 ****'."\n";
				foreach ( $lists as $priority => $callbacks ) {
					foreach ( $callbacks as $value ) {
						$output .= $priority . ', ' . $this->get_function_info( $value['function'] ) . "\n";
					}
				}
				$output .= '**** ここまで ****'."\n";
				$output_error_log = new Output_error_log();
				if ( $file )
					$output_error_log->file = $file;
				if ( $reset )
					$output_error_log->reset = $reset;
				$output_error_log->output_error_log($hook_name);
			}
		);
	}

	/**
	 * 指定のフックに何の関数がかかってるかのリストを返す
	 *
	 * @param $hook_name 指定のフック名
	 */
	private function get_filer_list ( $hook_name ) {
		global $wp_filter;
		if ( ! isset( $wp_filter[ $hook_name ] ) )
			return;
		return $wp_filter[ $hook_name ]->callbacks;
	}

	/**
	 * 各関数の情報を取得
	 *
	 * @param $functionData 関数名
	 */
	private function get_function_info ( $functionData ) {
		try{
			$is_class = is_array( $functionData );
			$rf = $is_class
				? new \ReflectionClass($functionData[0])
				: new \ReflectionFunction($functionData);

			$function_name = $is_class
				? $rf->getName() . '-&gt;'. $functionData[1]
				: ($rf->getClosureScopeClass() === null ?  ''
					: $rf->getClosureScopeClass()->getName() . '-&gt;') . $rf->getName();

			$start_line = $is_class
				? $rf->getMethod($functionData[1])->getStartLine()
				: $rf->getStartLine() ;

			$fnames = array_reverse( preg_split( "/[\\/\\\\]/" , $rf->getFileName()));
			$fname = '/' . $fnames[1] . "/" . $fnames[0];
			return $function_name  . ' [' . $fname .'(' . $start_line . ')]';
		} catch ( \Exception $e ) {
			return '例外エラー: '.  $e->getMessage();
		}
	}
}
