<?php
/**
 * Plugin Name: DLX Ratings Nag
 * Plugin URI: https://dlxpplugins.com/plugins/
 * Description: Demonstrates how to add a ratings admin notice to your plugin.
 * Author: DLX Plugins
 * Version: 0.0.1
 * Requires at least: 5.1
 * Requires PHP: 7.2
 * Author URI: https://dlxplugins.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package DLXRatingsNag
 */

namespace DLXPlugins\DLXRatingsNag;

// Register an activation hook to set an option to track how long the plugin has been installed.
register_activation_hook( __FILE__, __NAMESPACE__ . '\set_install_date' );

/**
 * Set the install date option.
 *
 * @return void
 */
function set_install_date() {
	update_option( 'dlx_ratings_nag_install_date', time() );
}

// Register an admin panel callback.
add_action( 'admin_menu', __NAMESPACE__ . '\add_admin_menu' );

/**
 * Add an admin menu item.
 *
 * @return void
 */
function add_admin_menu() {
	add_options_page(
		__( 'DLX Ratings Nag', 'dlx-ratings-nag' ),
		__( 'DLX Ratings Nag', 'dlx-ratings-nag' ),
		'manage_options',
		'dlx-ratings-nag',
		__NAMESPACE__ . '\ratings_nag_page'
	);
}

/**
 * Display the ratings nag page.
 *
 * @return void
 */
function ratings_nag_page() {
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'DLX Ratings Nag', 'dlx-ratings-nag' ); ?></h1>
	</div>
	<?php
}

// Register an admin notice callback.
add_action( 'admin_notices', __NAMESPACE__ . '\ratings_nag' );

/**
 * Display the ratings nag.
 */
function ratings_nag() {
	// Skip ratings nag if WP_DEBUG is enabled.
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		return false;
	}

	// Get the current user ID.
	$user_id               = get_current_user_id();
	$ratings_nag_dismissed = (bool) get_user_meta( $user_id, 'dlx_ratings_nag_dismissed', true );
	if ( true === $ratings_nag_dismissed ) {
		return;
	}

	$screen = get_current_screen();
	if ( 'settings_page_dlx-ratings-nag' !== $screen->id ) {
		return;
	}

	// Get the install date. Exit if it doesn't exist.
	$install_date = get_option( 'dlx_ratings_nag_install_date', false );
	if ( ! $install_date ) {
		return;
	}

	// Get the number of days since the plugin was installed.
	$days_installed = round( ( time() - $install_date ) / DAY_IN_SECONDS );

	// If less than 14 days, exit.
	if ( $days_installed < 14 ) {
		return;
	}

	// Display the ratings nag.
	?>
	<div class="notice notice-info notice-rating is-dismissible">
		<p>
			<?php
			printf(
				/* translators: %s: Plugin name */
				esc_html__( 'You have been using %1$s for %2$d days. Would you like to leave a review?', 'dlx-ratings-nag' ),
				'<strong>' . esc_html__( 'DLX Ratings Nag', 'dlx-ratings-nag' ) . '</strong>',
				$days_installed
			);
			?>
		</p>
		<p>
			<a href="https://wordpress.org/support/plugin/dlx-ratings-nag/reviews/#new-post" class="button button-primary yes-button" target="_blank" rel="noopener noreferrer">
				<?php esc_html_e( 'Yes, I\'d love to!', 'dlx-ratings-nag' ); ?>
			</a>
			<a href="<?php echo esc_url( admin_url( 'options-general.php?page=dlx-ratings-nag' ) ); ?>" class="button button-secondary dismissable-button">
				<?php esc_html_e( 'No, thanks.', 'dlx-ratings-nag' ); ?>
			</a>
		</p>
	</div>
	<?php
}

// Set up the ajax action for dismissing the ratings nag.
add_action( 'wp_ajax_dlx_ratings_nag_dismiss', __NAMESPACE__ . '\dismiss_ratings_nag' );

/**
 * Dismiss the ratings nag.
 *
 * @return void
 */
function dismiss_ratings_nag() {
	// Check the nonce.
	check_ajax_referer( 'dlx-ratings-nag', 'nonce' );

	// Get the current user ID.
	$user_id = get_current_user_id();

	// Set user meta to dismiss the ratings nag.
	update_user_meta( $user_id, 'dlx_ratings_nag_dismissed', true );

	// Return a success message.
	wp_send_json_success();
}

add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\enqueue_scripts' );

/**
 * Enqueue scripts.
 *
 * @return void
 */
function enqueue_scripts() {
	$screen = get_current_screen();
	if ( 'settings_page_dlx-ratings-nag' !== $screen->id ) {
		return;
	}

	wp_enqueue_script(
		'dlx-ratings-nag',
		plugins_url( 'js/ratings-dismiss.js', __FILE__ ),
		array(),
		'0.0.1',
		true
	);

	wp_localize_script(
		'dlx-ratings-nag',
		'dlxRatingsNag',
		array(
			'nonce' => wp_create_nonce( 'dlx-ratings-nag' ),
		)
	);
}
