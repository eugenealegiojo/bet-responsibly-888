<?php
/**
 * Template for displaying account transactions
 */

$reports = \Parlay\Api\Account::get_account_reports();

$transaction_types = [
	'manual-debits'           => __( 'Manual Debits', 'parlay-api' ),
	'manual-credits'          => __( 'Manual Credits', 'parlay-api' ),
	'ecom-deposits'           => __( 'E-commerce Deposits', 'parlay-api' ),
	'ecom-payouts'            => __( 'E-commerce Payouts', 'parlay-api' ),
	'ecom-cancelled-declined' => __( 'E-commerce Cancelled/Declined', 'parlay-api' ),
	'bonus'                   => __( 'Bonus', 'parlay-api' ),
	'referrals'               => __( 'Referrals', 'parlay-api' ),
	'transfers'               => __( 'Transfers', 'parlay-api' ),
	'refunds'                 => __( 'Refunds', 'parlay-api' ),
];
?>

<div class="page-filter">
	<?php parlay_template( 'account/reports/date-filter' ); ?>

	<div class="input-field">
		<label><?php _e( 'Transaction Categories', 'parlay-api' ); ?></label>
		<div class="input-field-wrap">
			<ul class="checkbox-list">
				<?php foreach ( $transaction_types as $key => $value ) : ?>
					<li class="indent"><input type="checkbox" id="option-<?php echo $key; ?>" name="transaction_types[]" value="<?php echo $key; ?>">
					<label for="option-<?php echo $key; ?>"><?php echo $value; ?></label></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
	<div class="input-field">
		<label class="top"><?php _e( 'Currencies', 'parlay-api' ); ?></label>
		<div class="input-field-wrap">
			
			<div class="two-column-list">
				<ul class="checkbox-list">
					<?php
					$currencies = array(
						'AUD' => 'Australian Dollar',
						'BRL' => 'Brazilian Real',
						'CAD' => 'Canadian Dollar',
						'CNY' => 'Chinese Yuan',
						'DKK' => 'Danish Krone',
						'EUR' => 'Euro',
						'RUB' => 'Russian Ruble',
						'USD' => 'United States Dollar',
					);
					foreach ( $currencies as $key => $value ) :
						?>
						<li class="indent"><input type="checkbox" id="option-<?php echo $key; ?>" name="currencies[]" value="<?php echo $key; ?>">
						<label for="option-<?php echo $key; ?>"><?php echo $value; ?></label></li>
						<?php
					endforeach;
					?>
				</ul>
			</div>
		</div>
	</div>
	<div class="submitButton">
		<input type="submit" value="<?php echo esc_attr__( 'Send', 'parlay-api' ); ?>" class="">
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
			<th><?php esc_html_e( 'ID', 'parlay-api' ); ?></th>
			<th><?php esc_html_e( 'Date', 'parlay-api' ); ?></th>
			<th><?php esc_html_e( 'Amount', 'parlay-api' ); ?></th>
			<th><?php esc_html_e( 'Account Type', 'parlay-api' ); ?></th>
			<th><?php esc_html_e( 'Account Amount', 'parlay-api' ); ?></th>
			<th><?php esc_html_e( 'Currency', 'parlay-api' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $reports as $report ) : ?>
			<tr>
				<td><?php echo $report['id']; ?></td>
				<td><?php echo date( 'F j, Y, g:i a', strtotime( $report['date'] ) ); ?></td>
				<td><?php echo $report['amount']; ?></td>
				<td><?php echo $report['accountType']; ?></td>
				<td><?php echo $report['accountAmount']; ?></td>
				<td><?php echo $report['currency']; ?></td>	
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php else : ?>
	<p><?php echo __( 'No reports found.', 'parlay-api' ); ?></p>
<?php endif; ?>
