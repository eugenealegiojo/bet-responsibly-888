<?php
/**
 * General template for games
 *
 * $attributes
 */

use Parlay\Api\DataManager;

$games     = DataManager::get_games( $attributes );
$columns   = $attributes['columns'];
$category  = $attributes['category'];
$col_class = '';

if ( $games ) {
	$col_class = 'sc_item_columns_' . $columns;
}

?>
<div class="games-wrap<?php echo true === $attributes['games_filter'] ? ' has-games-filter' : ''; ?>">
	<div
		class="sc_services column sc_services_backward sc_services_featured_top sc_services_popup sc_post_details_popup inited">
		<?php
		if ( true === $attributes['games_filter'] ) :
			parlay_template( 'games-filter' );
		endif;
		?>
		<div id="games-list"
			class="sc_services_columns_wrap <?php echo esc_attr( $col_class ); ?> sc_item_columns sc_item_posts_container trx_addons_columns_wrap columns_padding_bottom columns_in_single_row">
			<?php
			if ( ! empty( $games ) ) :

				foreach ( $games as $g ) :

					$name      = isset( $g['name'] ) ? $g['name'] : '';
					$game_id   = 'casino' === $category ? $g['gameId'] : $g['id'];
					$style     = isset( $g['style'] ) ? $g['style'] : '';
					$thumb_url = '';
					$game_attrs[] = 'data-game-name="'. $name .'"';

					if ( isset( $g['thumbnail'] ) ) {
						$thumb_url = $g['thumbnail'];
					} elseif ( isset( $g['thumbnailURL'] ) ) {
						$thumb_url = $g['thumbnailURL'];
					}

					
					$thumb = parlay_game_thumbnail( $game_id, $style, $thumb_url, $attributes['fallback_thumbnail'] );

					// Search prefix gfp, gfl or gfc from tags array then return value
					if ( isset( $g['tags'] ) && count((array) $g['tags']) > 0 ) {

						$prefixed_tags = [
							'provider' => 'gfp',
							'lines'    => 'gfl',
							'category' => 'gfc',
						];

						foreach( $g['tags'] as $tag ) {
							foreach( $prefixed_tags as $type => $prefix ) {
								if ( substr( $tag, 0, strlen( $prefix ) ) === $prefix ) {
									$game_attrs[] = 'data-game-' . $type . '="' . $tag . '"';
									break;
								}
							}
						}
					}
	
					?>
					<div class="game trx_addons_column-1_<?php echo $columns; ?>" <?php echo implode( ' ', $game_attrs ) ?>>
						<div
							class="sc_services_item sc_item_container post_container no_links without_content with_image sc_services_item_featured_top post-23779 cpt_services type-cpt_services status-publish has-post-thumbnail hentry cpt_services_group-featured-games cpt_services_group-keno"
							data-post_type="cpt_services">
							<div class="sc_services_item_info">
								<div class="sc_services_item_header">
									<h4 class="sc_services_item_title entry-title">
									<?php echo $name; ?>
									</h4>
								</div>
							</div>
							<div
								class="post_featured with_thumb hover_link sc_services_item_thumb">
								<a
									href="javascript:void(0)"
									aria-hidden="false"
									data-game-id="<?php echo $game_id; ?>"
									data-category="<?php echo $category; ?>"
									class="popup-game"
								>
								<img
									fetchpriority="high"
									decoding="async"
									src="<?php echo $thumb; ?>"
									class="attachment-medium_large size-medium_large wp-post-image"
									alt=""
									>
								<div class="mask"></div>
								
								</a>
							</div>
						</div>
					</div>
					<?php
					$game_attrs = [];
					endforeach;
				else :
					?>
					<div class="sc_services_item sc_item_container post_container no_links without_content with_image sc_services_item_featured_top type-cpt_services status-publish has-post-thumbnail hentry cpt_services_group-featured-games cpt_services_group-keno"
						data-post_type="cpt_services">
						<p><?php _e( 'No games found.', 'parlay-api' ); ?></p>
					</div>
			
					<?php
				endif;
				?>
		</div> <!-- /.sc_services_columns_wrap -->
	</div> <!-- /.sc_services -->
</div> <!-- /.games-wrap -->
		
		
