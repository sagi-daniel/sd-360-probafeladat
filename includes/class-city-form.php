<?php

declare(strict_types=1);

namespace SagiDaniel\Probafeladat;

defined( 'ABSPATH' ) || exit;

final class City_Form {

	public const string OPTION  = 'sd_varos';
	public const string ACTION  = 'sd_varos_mentes';
	public const string DEFAULT = 'Budapest';
	public const array CITIES   = [ 'Debrecen', 'Budapest', 'Sopron' ];

	public static function init(): void {
		add_shortcode( 'sd_varos_form', [ self::class, 'render' ] );
		add_action( 'admin_post_' . self::ACTION, [ self::class, 'handle_save' ] );
	}

	public static function get_city(): string {
		$city = get_option( self::OPTION, self::DEFAULT );

		return in_array( $city, self::CITIES, true ) ? $city : self::DEFAULT;
	}

	public static function render(): string {
		$selected = self::get_city();

		ob_start();

		if ( isset( $_GET['sd_mentve'] ) ) {
			echo '<p class="sd-uzenet">A kiválasztott város elmentve.</p>';
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			echo '<p>Mentett város: <strong>' . esc_html( $selected ) . '</strong></p>';
			echo '<p>A módosításhoz adminisztrátorként kell bejelentkezni.</p>';

			return (string) ob_get_clean();
		}
		?>
		<form class="sd-varos-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="<?php echo esc_attr( self::ACTION ); ?>">
			<?php wp_nonce_field( self::ACTION ); ?>

			<label for="sd-varos">Város</label>
			<select id="sd-varos" name="sd_varos">
				<?php foreach ( self::CITIES as $city ) : ?>
					<option value="<?php echo esc_attr( $city ); ?>" <?php selected( $selected, $city ); ?>><?php echo esc_html( $city ); ?></option>
				<?php endforeach; ?>
			</select>

			<button type="submit">Mentés</button>
		</form>
		<?php

		return (string) ob_get_clean();
	}

	public static function handle_save(): void {
		check_admin_referer( self::ACTION );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Nincs jogosultságod a mentéshez.', 'Hiba', [ 'response' => 403 ] );
		}

		$city = sanitize_text_field( wp_unslash( $_POST['sd_varos'] ?? '' ) );

		if ( ! in_array( $city, self::CITIES, true ) ) {
			wp_die( 'Érvénytelen város.', 'Hiba', [ 'response' => 400 ] );
		}

		update_option( self::OPTION, $city, false );

		wp_safe_redirect( add_query_arg( 'sd_mentve', '1', remove_query_arg( [ 'sd_torolve', 'sd_mentve' ], wp_get_referer() ?: home_url( '/' ) ) ) );
		exit;
	}
}
