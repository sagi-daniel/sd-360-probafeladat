<?php
/**
 * Plugin Name:       360 Marketing próbafeladat – Blog CPT és városválasztó
 * Description:       "blog" egyedi bejegyzéstípus frontend listával és törléssel, admin e-mail értesítéssel.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      8.3
 * Author:            Sági Dániel
 * License:           GPL-2.0-or-later
 */

defined( 'ABSPATH' ) || exit;

define( 'SD_PROBAFELADAT_FILE', __FILE__ );
define( 'SD_PROBAFELADAT_DIR', plugin_dir_path( __FILE__ ) );
define( 'SD_PROBAFELADAT_MIN_PHP', '8.3' );
define( 'SD_PROBAFELADAT_MIN_WP', '6.5' );

function sd_probafeladat_requirements_met() {
	return version_compare( PHP_VERSION, SD_PROBAFELADAT_MIN_PHP, '>=' )
		&& version_compare( get_bloginfo( 'version' ), SD_PROBAFELADAT_MIN_WP, '>=' );
}

function sd_probafeladat_requirements_message() {
	return sprintf(
		'A „360 Marketing próbafeladat” bővítményhez legalább PHP %1$s és WordPress %2$s szükséges. Jelenleg: PHP %3$s, WordPress %4$s.',
		SD_PROBAFELADAT_MIN_PHP,
		SD_PROBAFELADAT_MIN_WP,
		PHP_VERSION,
		get_bloginfo( 'version' )
	);
}

function sd_probafeladat_block_activation() {
	deactivate_plugins( plugin_basename( SD_PROBAFELADAT_FILE ) );
	wp_die(
		esc_html( sd_probafeladat_requirements_message() ),
		'A bővítmény nem aktiválható',
		array( 'back_link' => true )
	);
}

function sd_probafeladat_requirements_notice() {
	printf( '<div class="notice notice-error"><p>%s</p></div>', esc_html( sd_probafeladat_requirements_message() ) );
}

if ( ! sd_probafeladat_requirements_met() ) {
	register_activation_hook( __FILE__, 'sd_probafeladat_block_activation' );
	add_action( 'admin_notices', 'sd_probafeladat_requirements_notice' );
	return;
}

require_once SD_PROBAFELADAT_DIR . 'includes/class-blog.php';
require_once SD_PROBAFELADAT_DIR . 'includes/class-city-form.php';
require_once SD_PROBAFELADAT_DIR . 'includes/class-sample-content.php';

register_activation_hook( __FILE__, array( 'SagiDaniel\Probafeladat\Blog', 'activate' ) );
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );

\SagiDaniel\Probafeladat\Blog::init();
\SagiDaniel\Probafeladat\City_Form::init();
\SagiDaniel\Probafeladat\Sample_Content::init();