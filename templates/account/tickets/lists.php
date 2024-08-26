<?php
/**
 * Tickets list
 */

use Parlay\Api\HelpDesk;
use Parlay\Api\Account;

$tickets = Account::get_account_tickets();

$no_results_message = '';

?>
<div class="page-filter">
	<div class="input-field">
		<label></label>
		<div class="input-field-wrap quick-link text-right">
			<a class="sc_button sc_button_size_small" href="<?php echo parlay_account_url('/create-ticket'); ?>">
				<?php esc_html_e( 'Open Ticket', 'parlay-api' ); ?> &raquo;
			</a>
		</div>
	</div>
	<?php parlay_template( 'account/reports/date-filter' ); ?>
	<div class="input-field">
		<label><?php _e( 'Status', 'parlay-api' ); ?></label>
		<div class="input-field-wrap">
			<select name="status">
				<?php foreach ( HelpDesk::get_status_list() as $key => $value ) : ?>
					<option value="<?php echo $key; ?>"><?php echo $value; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	</div>
	<div class="submitButton">
		<input type="submit" value="<?php esc_attr_e( 'Filter', 'parlay-api' ); ?>" class="">
	</div>
</div>

<div class="account-reports-heading">
	<p>
		<?php
		if ( isset( $_POST['from-date'] ) && isset( $_POST['to-date'] ) && ! empty( $_POST['from-date'] ) && ! empty( $_POST['to-date'] ) ) :
			printf(
				esc_html( sprintf(
					__( 'Displaying tickets from %1$s to %2$s', 'parlay-api' ),
					esc_html( isset( $_POST['from-date'] ) ? $_POST['from-date'] : '' ),
					esc_html( isset( $_POST['to-date'] ) ? $_POST['to-date'] : '' )
				))
			);

			$no_results_message = __( 'Information unavailable. Modify the time range and try again.', 'parlay-api' );
		endif;
		?>
	</p>
</div>

<?php if ( ! empty( $tickets ) ) : ?>
<table class="display standard-table">
	<thead>
		<tr>
			<th><?php esc_html_e( 'Ticket ID', 'parlay-api' ); ?></th>
			<th><?php esc_html_e( 'Name', 'parlay-api' ); ?></th>
			<th><?php esc_html_e( 'Status', 'parlay-api' ); ?></th>
			<th><?php esc_html_e( 'Date', 'parlay-api' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $tickets as $ticket ) : ?>
			<tr>
				<td><a href="<?php echo home_url( '/' . Account::SLUG . '/ticket/' . $ticket['ticket'] ); ?>"><?php echo $ticket['ticket']; ?></a></td>
				<td><?php echo $ticket['name']; ?></td>
				<td><?php echo HelpDesk::get_status_list( $ticket['status'] ); ?></td>
				<td><?php echo $ticket['created']; ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php else : ?>
	<p><?php echo $no_results_message; ?></p>
<?php endif; ?>