<?php
/**
 * Template for game filters
 */

use Parlay\Api\DataManager;

?>
<div class="filter-sidebar">
	<h2 class="filters-heading"><?php echo __( 'Filters', 'parlay-api' ); ?></h2>
	<form id="game-filter-form" action="#" method="GET" data-inited-validation="1">
		<div class="custom-nuts-filter game-search">
			<div class="field-wrap">
				<input type="text" name="game" id="game-name" placeholder="<?php echo __( 'Name', 'parlay-api' ); ?>">
			</div>
			<div class="field-wrap">
				<select name="lines" id="game-lines">
					<option value="" disabled selected>-<?php echo __( 'Line', 'parlay-api' ); ?> -</option>
					<option value="0"><?php echo __( 'All', 'parlay-api' ); ?></option>
					<?php
						foreach( DataManager::get_tags('lines') as $key => $tag ) :
					?>
						<option value="<?php echo $tag['name']; ?>"><?php echo $tag['display_name']; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="field-wrap">
				<select name="provider" id="game-provider">
					<option value="" disabled selected>- <?php echo __( 'Provider', 'parlay-api' ); ?> -</option>
					<option value="0"><?php echo __( 'All', 'parlay-api' ); ?></option>
					<?php
						foreach( DataManager::get_tags('provider') as $key => $tag ) :
					?>
						<option value="<?php echo $tag['name']; ?>"><?php echo $tag['display_name']; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="field-wrap">
				<select name="category" id="game-category">
					<option value="" disabled selected>- <?php echo __( 'Category', 'parlay-api' ); ?> -</option>
					<option value="0"><?php echo __( 'All', 'parlay-api' ); ?></option>
					<?php
						foreach( DataManager::get_tags('category') as $key => $tag ) :
					?>
						<option value="<?php echo $tag['name']; ?>"><?php echo $tag['display_name']; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
	</form>
</div>

