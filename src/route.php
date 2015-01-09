<?php

namespace shelob9\wp_front_end_ajax_api;

/**
 * Class route
 *
 * Dispatch and respond to requests to internal API
 *
 * @package shelob9\wp_front_end_ajax_api
 */
class route {

	/**
	 * Main router for internal API.
	 *
	 * Checks permission, and dispatches and returns, or returns error.
	 *
	 */
	public static function do_api() {

		global $wp_query;

		//get action, and if set, possibly act
		$action = $wp_query->get( 'action' );


		if ( $action && strpos( $_SERVER[ 'REQUEST_URI'], settings::$endpoint ) ) {
			//get class to process with and proceed or return 501 if its invalid
			$action_class = self::action_class( $action );

			$auth_error = $response = false;
			if ( $action_class  ) {
				if ( false == auth::check() ) {
					$auth_error = true;
				}else {
					$params        = self::get_args( $action_class::args(), $action_class::method() );
					if ( is_array( $params ) ) {
						$response = $action_class::act( $params );
					} else {
						$auth_error = true;
					}
				}
			} else {
				$auth_error = true;
			}

			if ( ! $auth_error ) {
				return self::respond( $response, 200 );
			} else {
				return self::respond( false, 401 );
			}

		}

	}

	/**
	 * Get a static class object, by action.
	 *
	 * Does not check if class exists. Use only for those allowed by self::action_allowed()
	 *
	 * @access protected
	 *
	 * @param string $action Action name.
	 *
	 * @param $action
	 *
	 * @return object The class object.
	 */
	protected static function action_class( $action ) {

		//define namespacing
		$namespace = settings::$action_namespace."\\";

		//get namespace class name
		$class = $namespace . $action;

		//return class name if it exists and implements the action interface
		if ( class_exists( $class ) && $namespace . 'action' == class_implements( $class, false ) ) {
			return $class;

		}

	}

	/**
	 * Returned an array of the specified args from GET or POST data
	 *
	 * @param array $accept_args Arguments to allow
	 * @param string $method HTTP method to use GET|POST
	 *
	 * @access protected
	 *
	 * @return bool|array
	 */
	protected function get_args( $accept_args, $method = 'GET') {
		$method = strtoupper( $method );
		switch ( $method ) {
			case "GET":
				$input = $_GET;
				break;
			case "POST":
				$input = $_POST;
				break;
			default:
				return false;
		}

		return self::sanitize( $input, $accept_args );

	}

	/**
	 * Sanitize incoming POST or GET var for accepted args.
	 *
	 * @param array $input The GET or POST data
	 * @param array $accept_args Array of args to sanitize and return.
	 *
	 * @access protected
	 *
	 * @return bool|array
	 */
	protected static function sanitize( $input, $accept_args ) {
		$output = false;
		foreach ( $input as $key => $val ) {
			if ( in_array( $key, $accept_args ) ) {

				//return if its a number
				if ( is_int( $val ) || is_float( $val ) || empty( $val ) ) {
					$output[ $key ] = $val;
				}

				$value = self::sanitize_cb( $val, $key );

				if ( $value ) {
					$output[ $key ] = $value;
				}

			}

		}

		return $output;

	}

	/**
	 * Does actual sanitization.
	 *
	 * Uses settings::$sanitize_cb if that function exists, if not, uses wp_kses()
	 *
	 * @param mixed $input The value to sanitize.
	 * @param string|int $key The key being sanitized.
	 *
	 * @return mixed|string
	 */
	protected function sanitize_cb( $input, $key ) {
		if ( function_exists( settings::$sanitize_cb ) ) {
			return call_user_func( settings::$sanitize_cb, $input );

		}else{
			$allowed_tags = apply_filters( 'wp_front_end_ajax_api_kses_allowed_tags', wp_kses_allowed_html( 'post' ), $input, $key );
			return wp_kses( $input, $allowed_tags );
		}
	}


	/**
	 * Send the response
	 *
	 * @access protected
	 *
	 * @param string|array|integer $response Response to send. Will be encoded as JSON if is array. If is integer and greater than 1,
	 * @param int|null $status_code Optional. Status code to set for the response. if is the default of null, and not set by $response then success code 200 is used.
	 *
	 * @return string
	 */
	protected static function respond( $response, $status_code = null ) {
		if ( empty( $response ) ) {
			$status_code = 204;
		}

		if ( is_int( $response ) &&  $response > 1 ) {

			$response = false;
			$status_code = $response;

		}

		if ( ! is_null( $status_code ) ) {
			$status_code = 200;
		}

		status_header( $status_code );
		if ( is_array( $response ) ) {
			wp_send_json_success( $response );
			die();
		}
		else{
			echo $response;
			die();
		}

	}

	/**
	 * Holds the instance of this class.
	 *
	 * @access private
	 * @var    object
	 */
	private static $instance;

	/**
	 * Returns an instance of this class.
	 *
	 * @access public
	 *
	 * @return route|object
	 */
	public static function init() {

		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;

	}

}
