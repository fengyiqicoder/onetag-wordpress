<?php
/**
 * Removes the plugin settings when the plugin is deleted.
 *
 * @package AskAiOnetag
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'ask_ai_onetag_settings' );
delete_site_option( 'ask_ai_onetag_settings' );
