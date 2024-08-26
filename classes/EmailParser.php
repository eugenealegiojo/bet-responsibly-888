<?php

namespace Parlay\Api;


class EmailParser {

	/**
	 * @var string $templateLocation
	 */
	private $templateLocation;

	/**
	 * @var array $data
	 */
	private $data = [];

	/**
	 * @param string $templateLocation
	 * @param array $data
	 */
	public function __construct( string $templateLocation, array $data ) {
		$this->templateLocation = $templateLocation;
		$this->data             = $data;
	}

	/**
	 * @return string
	 */
	public function parse(): string {
		$template = file_get_contents( $this->templateLocation );
		foreach ( $this->data as $key => $value ) {
			$template = str_replace( '{{' . $key . '}}', $value, $template );
		}
		return $template;
	}
}
