<?php
/**
 * @package   WPEmerge
 * @author    Atanas Angelov <atanas.angelov.dev@gmail.com>
 * @copyright 2018 Atanas Angelov
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0
 * @link      https://wpemerge.com/
 */

namespace WPEmerge\View;

interface HasContextInterface {
	/**
	 * Get context values.
	 *
	 * @param  string|null $key
	 * @param  mixed|null  $default
	 * @return mixed
	 */
	public function getContext( $key = null, $default = null );

	/**
	 * Add context values.
	 *
	 * @param  string|array $key
	 * @param  mixed        $value
	 * @return self         $this
	 */
	public function with( $key, $value = null );
}
