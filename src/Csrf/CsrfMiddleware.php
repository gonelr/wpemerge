<?php

namespace WPEmerge\Csrf;

use Closure;
use WPEmerge\Facades\Csrf as CsrfService;
use WPEmerge\Middleware\MiddlewareInterface;

/**
 * Store current request data and clear old request data
 */
class CsrfMiddleware implements MiddlewareInterface {
	/**
	 * {@inheritDoc}
	 */
	public function handle( $request, Closure $next ) {
		$old_token = CsrfService::getTokenForRequest( $request );

		if ( ! CsrfService::isValidToken( $old_token ) ) {
			CsrfService::die();
		}

		CsrfService::generateToken();

		return $next( $request );
	}
}