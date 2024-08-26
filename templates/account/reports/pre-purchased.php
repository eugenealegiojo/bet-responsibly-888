<?php
/**
 * Pre-purchased
 */

$reports = \Parlay\Api\Account::get_account_reports();

?>

<div class="page-filter">
	
	<?php parlay_template( 'account/reports/date-filter' ); ?>

	<div class="input-field">
		<label><?php esc_html_e( 'Select Order', 'parlay-api' ); ?></label>
		<div class="input-field-wrap">
			<select name="select-date">
				<option value="order"><?php esc_html_e( 'Order Date', 'parlay-api' ); ?></option>
				<option value="game"><?php esc_html_e( 'Game Date', 'parlay-api' ); ?></option>
			</select>
		</div>
	</div>
	<div class="submitButton">
		<input type="submit" value="<?php esc_html_e( 'Send', 'parlay-api' ); ?>" class="">
	</div>
</div>

<div class="account-reports-heading">
	<p>
		<?php
		if ( isset( $_POST['from-date'] ) && isset( $_POST['to-date'] ) && ! empty( $_POST['from-date'] ) && ! empty( $_POST['to-date'] ) ) :
			printf(
				esc_html( sprintf(
					__( 'Displaying reports from %1$s to %2$s', 'parlay-api' ),
					esc_html( isset( $_POST['from-date'] ) ? $_POST['from-date'] : '' ),
					esc_html( isset( $_POST['to-date'] ) ? $_POST['to-date'] : '' )
				))
			);
		endif;
		?>
	</p>
</div>

<?php if ( ! empty( $reports ) ) : ?>
<table class="display custom-data-tables">
	<thead>
		<tr>
			<th><?php esc_html_e( 'Order ID', 'parlay-api' ); ?></th>
			<th><?php esc_html_e( 'Order Date', 'parlay-api' ); ?></th>
			<th><?php esc_html_e( 'Game Date', 'parlay-api' ); ?></th>
			<th><?php esc_html_e( 'Game Number', 'parlay-api' ); ?></th>
			<th><?php esc_html_e( 'Game Name', 'parlay-api' ); ?></th>
			<th><?php esc_html_e( 'Game Type', 'parlay-api' ); ?></th>
			<th><?php esc_html_e( 'Your Tickets', 'parlay-api' ); ?></th>
			<th><?php esc_html_e( 'Game Cost', 'parlay-api' ); ?></th>
			<th><?php esc_html_e( 'Room', 'parlay-api' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $reports as $report ) : ?>
			<tr>
				<td><?php echo esc_html( $report['orderId'] ); ?></td>
				<td><?php echo date( 'F j, Y, g:i a', strtotime( $report['orderDate'] ) ); ?></td>
				<td><?php echo date( 'F j, Y, g:i a', strtotime( $report['gameDate'] ) ); ?></td>
				<td><?php echo esc_html( $report['gameNumber'] ); ?></td>
				<td><?php echo esc_html( $report['gameName'] ); ?></td>
				<td><?php echo esc_html( $report['gameType'] ); ?></td>
				<td><?php echo esc_html( $report['ticketType'] ); ?></td>
				<td><?php echo esc_html( $report['gameCost'] ); ?></td>
				<th>
					<?php echo ( isset( $report['room'] ) && isset( $report['room']['name'] ) ) ? esc_html( $report['room']['name'] ) : ''; ?>
				</th>
			</tr>  
		<?php endforeach; ?>
	</tbody>
</table>
<?php else : ?>
	<p><?php echo __( 'No reports found.', 'parlay-api' ); ?></p>
<?php endif; ?>
