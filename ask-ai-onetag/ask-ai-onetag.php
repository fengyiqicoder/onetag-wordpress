<?php
/**
 * Plugin Name:       Ask AI - OneTag
 * Plugin URI:        https://onetagai.com/?ref=wordpress
 * Description:       Adds an Ask AI box to your site that answers visitor questions from your own pages. Uses the OneTag AI hosted widget.
 * Version:           0.1.0
 * Requires at least: 6.3
 * Requires PHP:      7.4
 * Author:            OneTag AI
 * Author URI:        https://onetagai.com/?ref=wordpress
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ask-ai-onetag
 *
 * @package AskAiOnetag
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ASK_AI_ONETAG_VERSION', '0.1.0' );
define( 'ASK_AI_ONETAG_OPTION', 'ask_ai_onetag_settings' );
define( 'ASK_AI_ONETAG_HANDLE', 'ask-ai-onetag' );
define( 'ASK_AI_ONETAG_SRC', 'https://onetagai.com/ask-docs.js' );
define( 'ASK_AI_ONETAG_SIGNUP_URL', 'https://onetagai.com/?ref=wordpress#get-key' );

/**
 * Default settings.
 *
 * @return array<string, string>
 */
function ask_ai_onetag_defaults() {
	return array(
		'enabled'  => '1',
		'key'      => '',
		'label'    => '',
		'position' => 'auto',
		'color'    => '',
		'lang'     => '',
	);
}

/**
 * Allowed values for the select fields.
 *
 * @return array<string, array<string, string>>
 */
function ask_ai_onetag_choices() {
	return array(
		'position' => array(
			'auto'  => __( 'Automatic (next to the search box when found, else floating)', 'ask-ai-onetag' ),
			'float' => __( 'Floating button, bottom right', 'ask-ai-onetag' ),
			'left'  => __( 'Floating button, bottom left', 'ask-ai-onetag' ),
			'none'  => __( 'No button (open it from your own button or link)', 'ask-ai-onetag' ),
		),
		'lang'     => array(
			''      => __( 'Same as the page', 'ask-ai-onetag' ),
			'en'    => __( 'English', 'ask-ai-onetag' ),
			'zh-CN' => __( 'Simplified Chinese', 'ask-ai-onetag' ),
			'zh-TW' => __( 'Traditional Chinese', 'ask-ai-onetag' ),
		),
	);
}

/**
 * Saved settings merged with the defaults.
 *
 * @return array<string, string>
 */
function ask_ai_onetag_settings() {
	$saved = get_option( ASK_AI_ONETAG_OPTION, array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}
	return array_merge( ask_ai_onetag_defaults(), array_intersect_key( $saved, ask_ai_onetag_defaults() ) );
}

/**
 * Sanitize the settings array before it is saved.
 *
 * @param mixed $input Raw form input.
 * @return array<string, string>
 */
function ask_ai_onetag_sanitize( $input ) {
	$input   = is_array( $input ) ? $input : array();
	$choices = ask_ai_onetag_choices();
	$out     = ask_ai_onetag_defaults();

	$out['enabled'] = empty( $input['enabled'] ) ? '0' : '1';

	$key = isset( $input['key'] ) ? sanitize_text_field( wp_unslash( $input['key'] ) ) : '';
	$key = trim( $key );
	if ( '' !== $key && ! preg_match( '/^ot_[A-Za-z0-9_-]{8,80}$/', $key ) ) {
		add_settings_error( ASK_AI_ONETAG_OPTION, 'ask-ai-onetag-key', __( 'The site key should start with ot_. It was not saved.', 'ask-ai-onetag' ) );
		$key = '';
	}
	$out['key'] = $key;

	$label        = isset( $input['label'] ) ? sanitize_text_field( wp_unslash( $input['label'] ) ) : '';
	$out['label'] = mb_substr( $label, 0, 40 );

	$position        = isset( $input['position'] ) ? sanitize_key( $input['position'] ) : 'auto';
	$out['position'] = array_key_exists( $position, $choices['position'] ) ? $position : 'auto';

	$color        = isset( $input['color'] ) ? sanitize_hex_color( '#' . ltrim( sanitize_text_field( wp_unslash( $input['color'] ) ), '#' ) ) : '';
	$out['color'] = $color ? ltrim( $color, '#' ) : '';

	$lang        = isset( $input['lang'] ) ? sanitize_text_field( wp_unslash( $input['lang'] ) ) : '';
	$out['lang'] = array_key_exists( $lang, $choices['lang'] ) ? $lang : '';

	return $out;
}

/**
 * Register the setting and its fields with the Settings API.
 */
function ask_ai_onetag_register_settings() {
	register_setting(
		'ask_ai_onetag',
		ASK_AI_ONETAG_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'ask_ai_onetag_sanitize',
			'default'           => ask_ai_onetag_defaults(),
			'show_in_rest'      => false,
		)
	);

	add_settings_section( 'ask_ai_onetag_main', '', '__return_false', 'ask-ai-onetag' );

	$fields = array(
		'enabled'  => __( 'Show the Ask AI box', 'ask-ai-onetag' ),
		'key'      => __( 'Site key', 'ask-ai-onetag' ),
		'label'    => __( 'Button label', 'ask-ai-onetag' ),
		'position' => __( 'Position', 'ask-ai-onetag' ),
		'color'    => __( 'Color', 'ask-ai-onetag' ),
		'lang'     => __( 'Language', 'ask-ai-onetag' ),
	);
	foreach ( $fields as $id => $title ) {
		add_settings_field(
			'ask_ai_onetag_' . $id,
			$title,
			'ask_ai_onetag_field',
			'ask-ai-onetag',
			'ask_ai_onetag_main',
			array(
				'id'        => $id,
				'label_for' => 'ask-ai-onetag-' . $id,
			)
		);
	}
}
add_action( 'admin_init', 'ask_ai_onetag_register_settings' );

/**
 * Render one settings field.
 *
 * @param array<string, string> $args Field arguments.
 */
function ask_ai_onetag_field( $args ) {
	$s       = ask_ai_onetag_settings();
	$id      = $args['id'];
	$name    = ASK_AI_ONETAG_OPTION . '[' . $id . ']';
	$html_id = 'ask-ai-onetag-' . $id;
	$choices = ask_ai_onetag_choices();

	switch ( $id ) {
		case 'enabled':
			printf(
				'<label><input type="checkbox" id="%1$s" name="%2$s" value="1" %3$s> %4$s</label>',
				esc_attr( $html_id ),
				esc_attr( $name ),
				checked( '1', $s['enabled'], false ),
				esc_html__( 'Load the Ask AI box on the public site', 'ask-ai-onetag' )
			);
			break;

		case 'key':
			printf(
				'<input type="text" class="regular-text code" id="%1$s" name="%2$s" value="%3$s" placeholder="ot_..." autocomplete="off" spellcheck="false">',
				esc_attr( $html_id ),
				esc_attr( $name ),
				esc_attr( $s['key'] )
			);
			printf(
				'<p class="description">%1$s <a href="%2$s" target="_blank" rel="noopener noreferrer">%3$s</a></p>',
				esc_html__( 'Your public OneTag AI site key. It is meant to be visible in your page source, like an analytics ID.', 'ask-ai-onetag' ),
				esc_url( ASK_AI_ONETAG_SIGNUP_URL ),
				esc_html__( 'Get a free key', 'ask-ai-onetag' )
			);
			break;

		case 'label':
			printf(
				'<input type="text" class="regular-text" id="%1$s" name="%2$s" value="%3$s" maxlength="40" placeholder="%4$s">',
				esc_attr( $html_id ),
				esc_attr( $name ),
				esc_attr( $s['label'] ),
				esc_attr__( 'Ask AI', 'ask-ai-onetag' )
			);
			break;

		case 'color':
			printf(
				'<input type="text" class="small-text code" id="%1$s" name="%2$s" value="%3$s" maxlength="7" placeholder="2563eb" pattern="#?[0-9A-Fa-f]{3}([0-9A-Fa-f]{3})?">',
				esc_attr( $html_id ),
				esc_attr( $name ),
				esc_attr( $s['color'] )
			);
			echo '<p class="description">' . esc_html__( 'Hex color without #, for example 2563eb. Leave empty to match your theme.', 'ask-ai-onetag' ) . '</p>';
			break;

		case 'position':
		case 'lang':
			printf( '<select id="%1$s" name="%2$s">', esc_attr( $html_id ), esc_attr( $name ) );
			foreach ( $choices[ $id ] as $value => $text ) {
				printf(
					'<option value="%1$s" %2$s>%3$s</option>',
					esc_attr( $value ),
					selected( $value, $s[ $id ], false ),
					esc_html( $text )
				);
			}
			echo '</select>';
			break;
	}
}

/**
 * Add Settings > Ask AI.
 */
function ask_ai_onetag_menu() {
	add_options_page(
		__( 'Ask AI', 'ask-ai-onetag' ),
		__( 'Ask AI', 'ask-ai-onetag' ),
		'manage_options',
		'ask-ai-onetag',
		'ask_ai_onetag_page'
	);
}
add_action( 'admin_menu', 'ask_ai_onetag_menu' );

/**
 * Render the settings page.
 */
function ask_ai_onetag_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<p>
			<?php esc_html_e( 'The Ask AI box answers visitor questions from the pages of this site. Questions and the text of the relevant pages are sent to OneTag AI to write the answer.', 'ask-ai-onetag' ); ?>
		</p>
		<?php settings_errors( ASK_AI_ONETAG_OPTION ); ?>
		<form action="options.php" method="post">
			<?php
			settings_fields( 'ask_ai_onetag' );
			do_settings_sections( 'ask-ai-onetag' );
			submit_button();
			?>
		</form>
		<p>
			<?php
			printf(
				/* translators: 1: terms link, 2: privacy link */
				esc_html__( 'By adding a key you agree to the OneTag AI %1$s and %2$s.', 'ask-ai-onetag' ),
				'<a href="' . esc_url( 'https://onetagai.com/terms?ref=wordpress' ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'terms', 'ask-ai-onetag' ) . '</a>',
				'<a href="' . esc_url( 'https://onetagai.com/privacy?ref=wordpress' ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'privacy policy', 'ask-ai-onetag' ) . '</a>'
			);
			?>
		</p>
	</div>
	<?php
}

/**
 * Settings link on the Plugins screen.
 *
 * @param array<int|string, string> $links Existing links.
 * @return array<int|string, string>
 */
function ask_ai_onetag_action_links( $links ) {
	$url = admin_url( 'options-general.php?page=ask-ai-onetag' );
	array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'ask-ai-onetag' ) . '</a>' );
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'ask_ai_onetag_action_links' );

/**
 * Admin notice while no key is set.
 */
function ask_ai_onetag_admin_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$s = ask_ai_onetag_settings();
	if ( '' !== $s['key'] || '1' !== $s['enabled'] ) {
		return;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( $screen && ! in_array( $screen->id, array( 'dashboard', 'plugins', 'settings_page_ask-ai-onetag' ), true ) ) {
		return;
	}
	printf(
		'<div class="notice notice-warning"><p>%1$s <a href="%2$s" target="_blank" rel="noopener noreferrer">%3$s</a> %4$s <a href="%5$s">%6$s</a></p></div>',
		esc_html__( 'Ask AI is not showing on your site yet: it needs a site key.', 'ask-ai-onetag' ),
		esc_url( ASK_AI_ONETAG_SIGNUP_URL ),
		esc_html__( 'Get a free key', 'ask-ai-onetag' ),
		esc_html__( 'then paste it in', 'ask-ai-onetag' ),
		esc_url( admin_url( 'options-general.php?page=ask-ai-onetag' ) ),
		esc_html__( 'Settings > Ask AI', 'ask-ai-onetag' )
	);
}
add_action( 'admin_notices', 'ask_ai_onetag_admin_notice' );

/**
 * Data attributes for the widget script tag.
 *
 * @return array<string, string>
 */
function ask_ai_onetag_attributes() {
	$s     = ask_ai_onetag_settings();
	$attrs = array(
		'data-key' => $s['key'],
		'data-ref' => 'wordpress',
	);
	if ( '' !== $s['label'] ) {
		$attrs['data-label'] = $s['label'];
	}
	if ( 'auto' !== $s['position'] ) {
		$attrs['data-position'] = $s['position'];
	}
	if ( '' !== $s['color'] ) {
		$attrs['data-color'] = $s['color'];
	}
	if ( '' !== $s['lang'] ) {
		$attrs['data-lang'] = $s['lang'];
	}
	return $attrs;
}

/**
 * Enqueue the widget on the public site, only when it is on and has a key.
 */
function ask_ai_onetag_enqueue() {
	$s = ask_ai_onetag_settings();
	if ( '1' !== $s['enabled'] || '' === $s['key'] ) {
		return;
	}
	wp_enqueue_script(
		ASK_AI_ONETAG_HANDLE,
		ASK_AI_ONETAG_SRC,
		array(),
		null, // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- hosted file, versioned by the service.
		array(
			'in_footer' => true,
			'strategy'  => 'async',
		)
	);
}
add_action( 'wp_enqueue_scripts', 'ask_ai_onetag_enqueue' );

/**
 * Add the data attributes to our script tag.
 *
 * @param string $tag    The script tag HTML.
 * @param string $handle The script handle.
 * @return string
 */
function ask_ai_onetag_script_tag( $tag, $handle ) {
	if ( ASK_AI_ONETAG_HANDLE !== $handle ) {
		return $tag;
	}
	$p = new WP_HTML_Tag_Processor( $tag );
	while ( $p->next_tag( 'script' ) ) {
		$src = $p->get_attribute( 'src' );
		if ( ! is_string( $src ) || 0 !== strpos( $src, ASK_AI_ONETAG_SRC ) ) {
			continue;
		}
		$p->set_attribute( 'async', true );
		foreach ( ask_ai_onetag_attributes() as $name => $value ) {
			$p->set_attribute( $name, $value );
		}
	}
	return $p->get_updated_html();
}
add_filter( 'script_loader_tag', 'ask_ai_onetag_script_tag', 10, 2 );
