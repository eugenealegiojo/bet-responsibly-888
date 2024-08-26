<?php
/**
 * Account left nav
 *
 * Exposed attributes:
 * $attributes = [
*       'nav_items' => [
*           'slug' => '',
*           'title' => '',
*       ]
 * ]
 */

?>
<ul>
	<?php
	if ( isset( $attributes['nav_items'] ) ) :
		foreach ( $attributes['nav_items'] as $key => $page ) :
			?>
			<li>
				<a href="<?php echo home_url( '/my-account/' . $page->slug ); ?>"><?php echo $page->title; ?></a>
			</li>
			<?php
		endforeach;
	endif;
	?>
</ul>
