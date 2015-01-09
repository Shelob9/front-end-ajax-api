<?php
namespace shelob9\wp_front_end_ajax_api;
use shelob9\wp_front_end_ajax_api\settings;

/**
 * Class endpoints
 *
 * Adds endpoints for internal API
 *
 * @package shelob9\wp_front_end_ajax_api
 */
class endpoints {
	function __construct() {
		add_action( 'init', array( $this, 'add_endpoints' ) );
		add_action( 'template_redirect', array( route::init(), 'do_api' ) );
	}

	/**
	 * Add endpoints for the API
	 */
	function add_endpoints() {
		//add "action as a rewrite tag
		add_rewrite_tag( '%action%', '^[a-z0-9_\-]+$' );

		//add the endpoint
		$endpoint = settings::$endpoint;
		add_rewrite_rule( "{$endpoint}/^[a-z0-9_\-]+$/?", 'index.php?action=$matches[1]', 'top' );

	}

}
