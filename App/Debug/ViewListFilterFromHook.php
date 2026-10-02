<?php
/**
 * 指定のフィルターに何の関数がかかっているかチェック
 *
 * @package ruijinen-debug-helper
 * @author mgn
 * @license GPL-2.0+
 */

namespace Ruijinen\DebugHelper\Debug;

/**
 * 指定のフィルターに何の関数がかかっているかチェック
 */
class ViewListFilterFromHook {

	/**
	 * Constructor.
	 */
	public function __construct() {
		if ( ! shortcode_exists( 'list_filter_from_hook' ) ) {
			add_shortcode( 'list_filter_from_hook', array( $this, 'shortcode_list_filter' ) );
		}
	}

	/**
	 * 指定のフックに何の関数がかかっているかを表示するショートコード（管理者権限のみ表示）
	 * ex. [list_filter_from_hook hook_name="wp_head"]
	 *
	 * @param array|string $atts ショートコードの引数.
	 * @return string
	 */
	public function shortcode_list_filter( $atts ) {
		// サーバー上のファイルパスなどを含むため、管理者以外には表示しない.
		if ( ! current_user_can( 'manage_options' ) ) {
			return '';
		}

		$atts      = shortcode_atts(
			array(
				'hook_name' => '',
			),
			$atts
		);
		$hook_name = $atts['hook_name'];
		$lists     = $this->get_filter_list( $hook_name );
		if ( ! $lists ) {
			return '';
		}

		$html = '<h2>' . esc_html( $hook_name ) . 'を使用してる関数・メソッド等</h2><ul>';
		foreach ( $lists as $priority => $callbacks ) {
			foreach ( $callbacks as $value ) {
				$html .= '<li>' . esc_html( $priority . ', ' . $this->get_function_info( $value['function'] ) ) . '</li>';
			}
		}
		$html .= '</ul>';
		return $html;
	}

	/**
	 * 指定のフックに何の関数がかかっているかをerror_logに出力する
	 *
	 * @param string $hook_name 出力したいフック名.
	 * @param bool   $reset     出力前にファイルを空にするか.
	 * @param string $file      出力先のファイルパス（空の場合は Output_error_log のデフォルト）.
	 */
	public function error_log_list_filter( $hook_name = '', $reset = false, $file = '' ) {
		add_action(
			'wp_footer',
			function () use ( $hook_name, $reset, $file ) {
				$lists = $this->get_filter_list( $hook_name );
				if ( ! $lists ) {
					return;
				}
				$output = '**** ' . $hook_name . 'を使用してる関数・メソッド等 ****' . "\n";
				foreach ( $lists as $priority => $callbacks ) {
					foreach ( $callbacks as $value ) {
						$output .= $priority . ', ' . $this->get_function_info( $value['function'] ) . "\n";
					}
				}
				$output .= '**** ここまで ****' . "\n";

				$output_error_log = new Output_error_log();
				if ( $file ) {
					$output_error_log->file = $file;
				}
				if ( $reset ) {
					$output_error_log->reset = $reset;
				}
				$output_error_log->output_error_log( $output );
			}
		);
	}

	/**
	 * 指定のフックに何の関数がかかってるかのリストを返す
	 *
	 * @param string $hook_name 指定のフック名.
	 * @return array|null
	 */
	private function get_filter_list( $hook_name ) {
		global $wp_filter;
		if ( ! isset( $wp_filter[ $hook_name ] ) ) {
			return null;
		}
		return $wp_filter[ $hook_name ]->callbacks;
	}

	/**
	 * 各関数の情報（名前・定義ファイル・行番号）を取得
	 *
	 * @param callable $function_data フックに登録されたコールバック.
	 * @return string
	 */
	private function get_function_info( $function_data ) {
		try {
			// 'Class::method' 形式の文字列は配列形式にそろえる.
			if ( is_string( $function_data ) && false !== strpos( $function_data, '::' ) ) {
				$function_data = explode( '::', $function_data, 2 );
			}

			if ( is_array( $function_data ) ) {
				// クラスメソッド.
				$reflection    = new \ReflectionMethod( $function_data[0], $function_data[1] );
				$function_name = $reflection->getDeclaringClass()->getName() . '->' . $reflection->getName();
			} elseif ( is_object( $function_data ) && ! ( $function_data instanceof \Closure ) ) {
				// __invoke を持つオブジェクト.
				$reflection    = new \ReflectionMethod( $function_data, '__invoke' );
				$function_name = get_class( $function_data ) . '->__invoke';
			} else {
				// 関数・クロージャ.
				$reflection    = new \ReflectionFunction( $function_data );
				$scope_class   = $reflection->getClosureScopeClass();
				$function_name = ( $scope_class ? $scope_class->getName() . '->' : '' ) . $reflection->getName();
			}

			$file_name = $reflection->getFileName();
			if ( false === $file_name ) {
				// PHPの組み込み関数はファイル情報を持たない.
				return $function_name . ' [PHP internal]';
			}
			$paths = array_reverse( preg_split( '/[\/\\\\]/', $file_name ) );
			$fname = '/' . ( isset( $paths[1] ) ? $paths[1] . '/' : '' ) . $paths[0];
			return $function_name . ' [' . $fname . '(' . $reflection->getStartLine() . ')]';
		} catch ( \Throwable $e ) {
			return '例外エラー: ' . $e->getMessage();
		}
	}
}
