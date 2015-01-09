<?php
namespace shelob9\api\internal\action;
/**
 * Interface action
 *
 * Interface for internal API action classes
 *
 * @package shelob9\api\internal
 */
interface action {

	/**
	 * Will be called by API router. Must return the response.
	 *
	 * @param array $params An array of params, defined by self::args()
	 *
	 * @return mixed
	 */
	public static function act( $params );

	/**
	 * Params for this route
	 *
	 * Add an array of the names of GET or POST vars to pass into self::act()
	 *
	 * @return array
	 */
	public static function args();

	/**
	 * Define if this action should use GET or POST.
	 *
	 * This method should either be:
	 *
	 * return "GET"; or return "POST";
	 *
	 * @return string
	 */
	public static function method();

}
