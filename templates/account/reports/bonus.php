<?php
/**
 * Bonus Report
 */

$reports = \Parlay\Api\Account::get_account_reports();

?>
<div class="page-filter">
	<?php parlay_template( 'account/reports/date-filter' ); ?>
	<div class="submitButton">
		<input type="submit" value="<?php esc_attr_e( 'Send', 'parlay-api' ); ?>" class="">
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
		<tr class="small-headings">
			<th><?php esc_html_e( 'Bonus Date', 'parlay-api' ); ?></th>
			<th><?php esc_html_e( 'Amount', 'parlay-api' ); ?></th>
			<th><?php esc_html_e( 'Account Type', 'parlay-api' ); ?></th>
			<th><?php esc_html_e( 'Deposit Amount', 'parlay-api' ); ?></th>
			<th><?php esc_html_e( 'Win Cap Amount', 'parlay-api' ); ?></th>
			<th><?php esc_html_e( 'Win Amount', 'parlay-api' ); ?></th>
			<th><?php esc_html_e( 'VIP Level', 'parlay-api' ); ?></th>
			<th><?php esc_html_e( 'Bonus Status', 'parlay-api' ); ?></th>
			<th><?php esc_html_e( 'Wager Required', 'parlay-api' ); ?></th>
			<th><?php esc_html_e( 'Wager Amount', 'parlay-api' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $reports as $report ) : ?>
		<tr>
			<td><?php echo date( 'F j, Y, g:i a', strtotime( $report['date'] ) ); ?></td>
			<td><?php echo esc_html( $report['amount'] ); ?></td>
			<td><?php echo esc_html( $report['accountType'] ); ?></td>
			<td><?php echo esc_html( $report['depositAmount'] ); ?></td>
			<td><?php echo esc_html( $report['winCapAmount'] ); ?></td>
			<td><?php echo esc_html( $report['winAmount'] ); ?></td>
			<td><?php echo esc_html( $report['vipLevel'] ); ?></td>
			<td><?php echo esc_html( $report['status'] ); ?></td>
			<td><?php echo esc_html( $report['wagerRequired'] ); ?></td>
			<td><?php echo esc_html( $report['wagerAmount'] ); ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php else : ?>
	<p><?php echo __( 'No reports found.', 'parlay-api' ); ?></p>
<?php endif; ?>