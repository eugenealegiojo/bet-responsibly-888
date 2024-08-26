<?php
/**
 * Reconcile Report
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
		<tr>
			<th><?php echo esc_html_x( 'Transaction ID', 'table header', 'parlay-api' ); ?></th>
			<th><?php echo esc_html_x( 'Date', 'table header', 'parlay-api' ); ?></th>
			<th><?php echo esc_html_x( 'Game type', 'table header', 'parlay-api' ); ?></th>
			<th><?php echo esc_html_x( 'Account Type', 'table header', 'parlay-api' ); ?></th>
			<th><?php echo esc_html_x( 'Amount', 'table header', 'parlay-api' ); ?></th>
			<th><?php echo esc_html_x( 'Balance', 'table header', 'parlay-api' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $reports as $report ) : ?>
			<tr>
				<td><?php echo esc_html( $report['id'] ); ?></td>
				<td><?php echo esc_html( $report['date'] ); ?></td>
				<td><?php echo esc_html( $report['gameType'] ); ?></td>
				<td><?php echo esc_html( $report['accountType'] ); ?></td>
				<td><?php echo esc_html( $report['accountAmount'] ); ?></td>
				<td><?php echo esc_html( $report['accountBalance'] ); ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php else : ?>
	<p><?php echo __( 'No reports found.', 'parlay-api' ); ?></p>
<?php endif; ?>
