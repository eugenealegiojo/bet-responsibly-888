<?php
/**
 * Main template for account page
 *
 * Exposed: $attributes from elementor widget
 */

use Parlay\Api\Account;

// Default page title from elementor widget
$title = isset( $attributes['title'] ) ? $attributes['title'] : __( 'My Profile', 'parlay-api' );

$current_page = Account::get_page_data();
$title        = ! empty( $current_page ) ? $current_page->title : $title;
$is_form      = ! empty( $current_page ) ? isset( $current_page->is_form ) && $current_page->is_form : false;
$slug         = ! empty( $current_page ) ? $current_page->slug : 'account';

?>
<div class="dashboard-page">
	<div class="page-dashboard-wrap">
		<div class="inner-sidebar">
			<div class="sidebar-wrap">
				<?php Account::render_nav(); ?>
			</div>
		</div>
		<div class="inner-content">
			<div class="inner-content-wrap">
				<h2><?php echo $title; ?></h2>

				<?php if ( $is_form ) : ?>
					<form id="pgs-account-form" action="<?php echo Account::get_current_page_url(); ?>" method="POST">
				<?php endif; ?>	

					<?php Account::render_page( $attributes ?? [] ); ?>

				<?php
				if ( $is_form ) :
						wp_nonce_field( 'pgs-account-' . $slug, 'pgs-account-nonce' );
					?>
					</form>
				<?php endif; ?>

			</div> <!-- .inner-content-wrap -->
		</div> <!-- .inner-content -->
	</div>
</div>
