<?php

declare(strict_types=1);

namespace SagiDaniel\Probafeladat;

defined( 'ABSPATH' ) || exit;

final class Sample_Content {

	public const string SLUG   = 'sd-teszt-bejegyzesek';
	public const string ACTION = 'sd_teszt_bejegyzesek';

	private const array POSTS = [
		'Teszt blog bejegyzés 1' => 'Ez az első teszt blog bejegyzés, amelyet a próbafeladat bővítmény hozott létre.',
		'Teszt blog bejegyzés 2' => 'Ez a második teszt blog bejegyzés, amelyet a próbafeladat bővítmény hozott létre.',
	];

	public static function init(): void {
		add_action( 'admin_menu', [ self::class, 'add_page' ] );
		add_action( 'admin_post_' . self::ACTION, [ self::class, 'handle_create' ] );
	}

	public static function add_page(): void {
		add_submenu_page(
			'edit.php?post_type=' . Blog::POST_TYPE,
			'Teszt bejegyzések',
			'Teszt bejegyzések',
			'manage_options',
			self::SLUG,
			[ self::class, 'render_page' ]
		);
	}

	public static function render_page(): void {
		?>
		<div class="wrap">
			<h1>Teszt bejegyzések</h1>

			<?php if ( isset( $_GET['sd_letrehozva'] ) ) : ?>
				<div class="notice notice-success is-dismissible">
					<p>
						<?php echo esc_html( absint( $_GET['sd_letrehozva'] ) . ' teszt blog bejegyzés létrehozva.' ); ?>
						<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=' . Blog::POST_TYPE ) ); ?>">Blog bejegyzések megtekintése</a>
					</p>
				</div>
			<?php endif; ?>

			<p>A gombra kattintva a bővítmény 2 egyszerű teszt blog bejegyzést hoz létre ezen az oldalon.</p>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="<?php echo esc_attr( self::ACTION ); ?>">
				<?php wp_nonce_field( self::ACTION ); ?>
				<?php submit_button( 'Teszt bejegyzések hozzáadása', 'primary', 'submit', false ); ?>
			</form>
		</div>
		<?php
	}

	public static function handle_create(): void {
		check_admin_referer( self::ACTION );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Nincs jogosultságod a teszt bejegyzések létrehozásához.', 'Hiba', [ 'response' => 403 ] );
		}

		$created = 0;

		foreach ( self::POSTS as $title => $content ) {
			$post_id = wp_insert_post(
				[
					'post_type'    => Blog::POST_TYPE,
					'post_status'  => 'publish',
					'post_title'   => $title,
					'post_content' => $content,
				]
			);

			if ( $post_id ) {
				++$created;
			}
		}

		wp_safe_redirect(
			add_query_arg(
				[
					'post_type'     => Blog::POST_TYPE,
					'page'          => self::SLUG,
					'sd_letrehozva' => $created,
				],
				admin_url( 'edit.php' )
			)
		);
		exit;
	}
}
