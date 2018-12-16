<?php
/**
 * Plugin Name: Auth0 - Setup Wizard, Part Deux
 * Description: Work on the setup wizard.
 * Version: 0.9.0
 * Author: Auth0
 * Author URI: https://auth0.com
 * Text Domain: wp-auth0-suw
 */

define( 'WPA0_SUW_VERSION', '0.9.0' );
define( 'WPA0_SUW_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPA0_SUW_PLUGIN_SLUG', basename(__FILE__, '.php') );
define( 'WPA0_SUW_ASSETS_URL', plugin_dir_url( __FILE__ ) . 'assets/' );

function wp_auth0_suw_admin_menu() {
	add_submenu_page(
		'wpa0',
		__( 'Setup Wizard, Part Deux', 'wp-auth0' ),
		__( 'Setup Wizard 2', 'wp-auth0' ),
		'manage_options',
		'wp-auth0-suw',
		'wp_auth0_suw_render_page'
	);
}
add_action( 'admin_menu', 'wp_auth0_suw_admin_menu', 100 );

function wp_auth0_suw_admin_enqueue_scripts() {
	if ( empty( $_GET['page'] ) || 'wp-auth0-suw' !== $_GET['page'] ) {
		return;
	}

	wp_enqueue_style( 'wp-auth0-suw', WPA0_SUW_ASSETS_URL . 'css/' . WPA0_SUW_PLUGIN_SLUG . '.css' );
	wp_enqueue_script(
		'wp-auth0-suw',
		WPA0_SUW_ASSETS_URL . 'js/' . WPA0_SUW_PLUGIN_SLUG . '.js',
		array( 'js-cookie' ),
		WPA0_SUW_VERSION,
		true
	);
	wp_enqueue_script('js-cookie', WPA0_SUW_ASSETS_URL . 'js/js.cookie.min.js' );
}
add_action( 'admin_enqueue_scripts', 'wp_auth0_suw_admin_enqueue_scripts' );

function wp_auth0_suw_do_magic() {
	if ( empty( $_POST['wp-auth0-suw-action'] ) ) {
		wp_auth0_suw_add_notice( 'No action' );
		return;
	}

	switch( $_POST['wp-auth0-suw-action'] ) {
		case 'check_token':
			wp_auth0_suw_add_notice( 'Checking token', true );
			break;
		default:
			wp_auth0_suw_add_notice( 'Invalid action' );
	}
}
add_action( 'admin_action_wp_auth0_suw_do_magic', 'wp_auth0_suw_do_magic' );

function wp_auth0_suw_render_page() {
	$opts = WP_Auth0_Options::Instance();
	$domain = $opts->get( 'domain' );
	$client_id = $opts->get( 'client_id' );
	?>
    <form class="wrap wp-auth0-suw" id="wp-auth0-suw" method="post" action="options.php">
		<?php settings_errors() ?>
        <input type="hidden" name="action" value="wp_auth0_suw_do_magic">
        <input type="hidden" name="wp-auth0-suw-action" id="wp-auth0-suw-action" value="">
        <input type="hidden" name="_wp_http_referer" value="<?php echo admin_url( 'admin.php?page=wp-auth0-suw' ) ?>">

        <h1>Setup Wizard, Part Deux</h1>
        <p>Welcome to the new setup wizard!</p>
        <h2>Auth0 Account</h2>
        <p>
            First, you need a free Auth0 account.
            <a href="https://auth0.com/signup" target="_blank">Sign up here</a>
        </p>
        <h2>API Token</h2>
        <p>
            We need a Management API token to check settings and make changes.
            <a href="https://auth0.com/docs/api/management/v2/tokens#get-a-token-manually" target="_blank">Instructions</a>
        </p>
        <p>
            <strong><label for="wp-auth0-suw-api-token">API Token</label></strong><br>
            <input
                    type="text"
                    id="wp-auth0-suw-api-token"
                    name="wp-auth0-suw-api-token"
                    class="large-text code">
        </p>
        <p>
            The token will need the following scopes:<br>
            <code><?php echo implode( '</code> <code>', wp_auth0_suw_token_scopes() ) ?></code>
        </p>
        <p>
            <strong><label for="wp-auth0-suw-domain">Domain</label></strong><br>
            <input type="text"
                   id="wp-auth0-suw-domain"
                   name="wp-auth0-suw-domain"
                   class="large-text code"
                   value="<?php echo esc_attr( $domain ) ?>">
        </p>
        <p>
            This will be your tenant name (top right of the Auth0 dashboard) + <code>.auth0.com</code>
        </p>
        <p>
            <button  type="button"
                     id="wp-auth0-suw-save-token"
                     class="button button-primary">Check API Token</button>
        </p>

        <div id="wp-auth0-suw-token-checks"></div>

        <h2>Application</h2>
        <p>Next, we need an Auth0 Application.</p>
		<?php if ( $client_id ) : ?>
            <p>
                It looks like you already have an Application saved for this site:
            </p>
            <p>
                <strong><label for="wp-auth0-suw-client-id">Client ID</label></strong><br>
                <input type="text"
                       id="wp-auth0-suw-client-id"
                       name="wp-auth0-suw-client-id"
                       class="large-text code"
                       value="<?php echo esc_attr( $client_id ) ?>">
            </p>
            <p>
                <button  type="button"
                         id="wp-auth0-suw-check-app"
                         class="button button-primary">Check Application</button>
            </p>
		<?php else : ?>
            <p>
                You can create an Application manually using
                <a href="https://auth0.com/docs/cms/wordpress/configuration">these steps</a>
                and paste it in the field below:
                <br><br>
                <strong><label for="wp-auth0-suw-client-id">Client ID</label></strong>
                <br>
                <input type="text"
                       id="wp-auth0-suw-client-id"
                       name="wp-auth0-suw-client-id"
                       class="large-text code">
            </p>
            <p>
                Or you can create one by clicking the button below:
                <br>
                <button type="button"
                        id="wp-auth0-suw-create-app"
                        class="button button-primary">Create Application</button>
            </p>
		<?php endif; ?>

        <div id="wp-auth0-suw-app-checks"></div>

        <h2>Database Connection</h2>
        <p>
            Now, we need a Database Connection to associate with WordPress users.
            This is not required if you're only using Social Connections.
        </p>
        <p>
            <button type="button"
                    id="wp-auth0-suw-search-connections"
                    class="button button-primary">Search Connections</button>
        </p>
        <p>
            <strong><label for="wp-auth0-suw-connection">Database Connections </label></strong><br>
            <select id="wp-auth0-suw-connection"
                    name="wp-auth0-suw-connection">
                <option value="" disabled>Select a Connection ...</option>
                <option value="" disabled>Click "Search Connections" above ...</option>
            </select>
        </p>

    </form>
	<?php
}

function wp_auth0_suw_token_scopes() {
	return array_merge(
		WP_Auth0_Api_Client::ConsentRequiredScopes(),
		array(
			'read:clients', 'read:client_grants'
		)
	);
}

function wp_auth0_suw_add_notice( $msg, $gtg = false ) {
	add_settings_error( 'wp_auth0_suw_wizard', 'wp_auth0_suw_wizard', $msg, $gtg ? 'updated' : 'error' );
}
