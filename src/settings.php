<?php

namespace shelob9\wp_front_end_ajax_api;

/**
 * Class settings
 * @package shelob9\wp_front_end_ajax_api
 */
class settings {
	public static $endpoint = 'jp-internal-api';

	public static $action_namespace = "\\shelob9\\internal\\api\\actions";

	public static $sanitize_cb = 'pods_sanitize';

}

