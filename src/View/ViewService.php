<?php

namespace WPEmerge\View;

use Closure;
use ViewEngine;
use WPEmerge\Helpers\Handler;
use WPEmerge\Helpers\Mixed;
use WPEmerge\Helpers\Path;
use WPEmerge\Support\Arr;

/**
 * Provide general view-related functionality
 */
class ViewService {
	/**
	 * Global variables
	 *
	 * @var array
	 */
	protected $globals = [];

	/**
	 * View composers
	 *
	 * @var array
	 */
	protected $composers = [];

	/**
	 * Get global variables
	 *
	 * @return array
	 */
	public function getGlobals() {
		return $this->globals;
	}

	/**
	 * Set a global variable
	 *
	 * @param  string $key
	 * @param  mixed  $value
	 * @return void
	 */
	public function addGlobal( $key, $value ) {
		$this->globals[ $key ] = $value;
	}

	/**
	 * Set an array of global variables
	 *
	 * @param  array $globals
	 * @return void
	 */
	public function addGlobals( $globals ) {
		foreach ( $globals as $key => $value ) {
			$this->addGlobal( $key, $value );
		}
	}

	/**
	 * Get view composer
	 *
	 * @param  string    $view
	 * @return Handler[]
	 */
	public function getComposersForView( $view ) {
		$view = ViewEngine::canonical( $view );

		$composers = [];

		foreach ( $this->composers as $composer ) {
			if ( in_array( $view, $composer['views'] ) ) {
				$composers[] = $composer['composer'];
			}
		}

		return $composers;
	}

	/**
	 * Add view composer
	 *
	 * @param string|string[] $views
	 * @param string|Closure  $composer
	 * @return void
	 */
	public function addComposer( $views, $composer ) {
		$views = array_map( function( $view ) {
			return ViewEngine::canonical( $view );
		}, Mixed::toArray( $views ) );
		$handler = new Handler( $composer, 'compose' );

		$this->composers[] = [
			'views' => $views,
			'composer' => $handler,
		];
	}

	/**
	 * Get the composed context for a view.
	 * Passes all arguments to the composer.
	 *
	 * @param  ViewInterface $view
	 * @return void
	 */
	public function compose( ViewInterface $view ) {
		$context = [];
		$composers = $this->getComposersForView( $view->getName() );

		foreach ( $composers as $composer ) {
			$composer->execute( $view );
		}
	}
}