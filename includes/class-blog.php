<?php

declare(strict_types=1);

namespace SagiDaniel\Probafeladat;

use WP_Post;

defined( 'ABSPATH' ) || exit;

final class Blog {

	public const string POST_TYPE     = 'blog';
	public const string DELETE_ACTION = 'sd_blog_torles';

	public static function init(): void {
		add_action( 'init', [ self::class, 'register_post_type' ] );
		add_shortcode( 'sd_blog_lista', [ self::class, 'render_list' ] );
		add_action( 'admin_post_' . self::DELETE_ACTION, [ self::class, 'handle_delete' ] );
	}

	public static function activate(): void {
		self::register_post_type();
		flush_rewrite_rules();
	}

	public static function register_post_type(): void {
		register_post_type(
			self::POST_TYPE,
			[
				'labels'       => [
					'name'          => 'Blog',
					'singular_name' => 'Blog bejegyzés',
					'add_new'       => 'Új bejegyzés',
					'add_new_item'  => 'Új blog bejegyzés',
					'edit_item'     => 'Blog bejegyzés szerkesztése',
					'all_items'     => 'Összes blog bejegyzés',
					'menu_name'     => 'Blog',
				],
				'public'       => true,
				'has_archive'  => true,
				'rewrite'      => [ 'slug' => self::POST_TYPE ],
				'menu_icon'    => 'dashicons-welcome-write-blog',
				'supports'     => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
				'show_in_rest' => true,
			]
		);
	}

	public static function render_list(): string {
		query_posts(
			[
				'post_type'      => self::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'date',
				'order'          => 'DESC',
			]
		);

		ob_start();

		if ( isset( $_GET['sd_torolve'] ) ) {
			echo '<p class="sd-uzenet">A bejegyzés törölve, az adminisztrátor e-mailt kapott róla.</p>';
		}

		if ( have_posts() ) {
			echo '<ul class="sd-blog-lista">';

			while ( have_posts() ) {
				the_post();
				self::render_item( get_the_ID() );
			}

			echo '</ul>';
		} else {
			echo '<p>Még nincs blog bejegyzés.</p>';
		}

		wp_reset_query();

		return (string) ob_get_clean();
	}

	private static function render_item( int $post_id ): void {
		?>
		<li class="sd-blog-elem">
			<strong class="sd-blog-cim"><?php echo esc_html( get_the_title( $post_id ) ); ?></strong>
			<time class="sd-blog-datum" datetime="<?php echo esc_attr( get_the_date( 'c', $post_id ) ); ?>"><?php echo esc_html( get_the_date( '', $post_id ) ); ?></time>

			<?php if ( current_user_can( 'delete_post', $post_id ) ) : ?>
				<form class="sd-blog-torles" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="<?php echo esc_attr( self::DELETE_ACTION ); ?>">
					<input type="hidden" name="post_id" value="<?php echo esc_attr( (string) $post_id ); ?>">
					<?php wp_nonce_field( self::DELETE_ACTION . '_' . $post_id ); ?>
					<button type="submit" onclick="return confirm('Biztosan törlöd ezt a bejegyzést?');">Törlés</button>
				</form>
			<?php endif; ?>
		</li>
		<?php
	}

	public static function handle_delete(): void {
		$post_id = absint( $_POST['post_id'] ?? 0 );

		check_admin_referer( self::DELETE_ACTION . '_' . $post_id );

		$post = get_post( $post_id );

		if ( ! $post || self::POST_TYPE !== $post->post_type || ! current_user_can( 'delete_post', $post_id ) ) {
			wp_die( 'Nincs jogosultságod a bejegyzés törléséhez.', 'Hiba', [ 'response' => 403 ] );
		}

		if ( ! wp_trash_post( $post_id ) ) {
			wp_die( 'A törlés nem sikerült.', 'Hiba', [ 'response' => 500 ] );
		}

		self::notify_admin( $post );

		wp_safe_redirect( add_query_arg( 'sd_torolve', '1', remove_query_arg( [ 'sd_torolve', 'sd_mentve' ], wp_get_referer() ?: home_url( '/' ) ) ) );
		exit;
	}

	private static function notify_admin( WP_Post $post ): void {
		$user = wp_get_current_user();
		$site = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );

		$message = implode(
			"\n",
			[
				'Egy blog bejegyzést töröltek a frontendről.',
				'',
				'Cím: ' . $post->post_title,
				'Azonosító: ' . $post->ID,
				'Törölte: ' . $user->display_name . ' (' . $user->user_email . ')',
				'Időpont: ' . wp_date( 'Y-m-d H:i:s' ),
				'',
				'A bejegyzés a kukába került, az admin felületen visszaállítható.',
			]
		);

		wp_mail(
			(string) get_option( 'admin_email' ),
			sprintf( '[%s] Blog bejegyzés törölve: %s', $site, $post->post_title ),
			$message
		);
	}
}
