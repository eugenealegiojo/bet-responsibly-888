<?php
/**
 * Admin settings template
 */
use Parlay\Api\Admin;

// @codingStandardsIgnoreFile 
?>
<div class="wrap">

	<h1 class="pgi-settings-heading">
		<?php Admin::render_page_heading(); ?>
	</h1>

	<?php Admin::render_update_message(); ?>

	<div class="pgi-settings-nav">
		<ul>
			<?php Admin::render_nav_items(); ?>
		</ul>
	</div>

	<div class="pgi-settings-content">
		<?php Admin::render_forms(); ?>
	</div>
</div>
