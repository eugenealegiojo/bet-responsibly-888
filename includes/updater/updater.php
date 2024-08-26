<?php
/* Only run if not already setup and not using a repo version. */
if ( ! class_exists( 'ParlayUpdater' ) ) {

	/* Defines */
	define( 'PARLAY_UPDATER_DIR', trailingslashit( dirname( __FILE__ ) ) );

	/* Classes */
	require_once PARLAY_UPDATER_DIR . 'classes/class-parlay-updater.php';

	/* Initialize the updater. */
	new ParlayUpdater();
}
