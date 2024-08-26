<?php
/**
 * View ticket details
 */

$activity = Parlay\Api\Account::get_activity_details();

if ( empty( $activity ) ) {
	return;
}

?>
<?php if ( isset( $activity['summary'] ) ) : ?>
	<div class="form-wrap">
		<div class="form-fields">
			<h3><?php _e( 'Game Session Details', 'parlay-api' ); ?></h3>
			<div class="display-field">
				<label><?php _e( 'Game Session ID :', 'parlay-api' ); ?></label>
				<span><?php echo \Parlay\Api\Account::get_data_id(); ?></span>
			</div>
			<div class="display-field">
				<label><?php _e( 'Game Type :', 'parlay-api' ); ?></label>
				<span><?php echo $activity['summary']['gameType']; ?></span>
			</div>
			<div class="display-field">
				<label><?php _e( 'Config ID :', 'parlay-api' ); ?></label>
				<span><?php echo $activity['summary']['configId']; ?></span>
			</div>
			<div class="display-field">
				<label><?php _e( 'Wagered :', 'parlay-api' ); ?></label>
				<span><?php echo $activity['summary']['wagered']; ?></span>
			</div>
			<div class="display-field">
				<label><?php _e( 'Won :', 'parlay-api' ); ?></label>
				<span><?php echo $activity['summary']['won']; ?></span>
			</div>
			<div class="display-field">
				<label><?php _e( 'Pushed :', 'parlay-api' ); ?></label>
				<span><?php echo $activity['summary']['pushed']; ?></span>
			</div>
		</div>
	</div>
<?php endif; ?>

<?php if ( ! empty( $activity['data'] ) ) : ?>
<table class="display custom-data-tables">
	<thead>
		<tr>
			<th><?php _e( 'Date & Time', 'parlay-api' ); ?></th>
			<th><?php _e( 'Round', 'parlay-api' ); ?></th>
			<th><?php _e( 'Amount', 'parlay-api' ); ?></th>
			<th><?php _e( 'Event', 'parlay-api' ); ?></th>
			<th><?php _e( 'Detail', 'parlay-api' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $activity['data'] as $data ) : ?>
		<tr>
			<td><?php echo date( 'F j, Y, g:i a', strtotime( $data['timestamp'] ) ); ?></td>
			<td><?php echo $data['round']; ?></td>
			<td><?php echo $data['amount']; ?></td>
			<td><?php echo $data['event']; ?></td>
			<td><?php echo $data['detail']; ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php endif; ?>
