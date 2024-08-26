<?php
/**
 * View ticket details
 */

$ticket = Parlay\Api\Account::get_ticket_details();

?>
<div class="form-wrap">
	<div class="form-fields quick-link">
		<div class="display-field text-right">
			<label></label>
			<a class="sc_button sc_button_size_small" href="<?php echo parlay_account_url('/tickets'); ?>">
				<?php _e( 'View Tickets', 'parlay-api' ); ?> &raquo;
			</a>
		</div>
	</div>
	<div class="form-fields">
		<div class="display-field">
			<label><?php _e( 'ID :', 'parlay-api' ); ?></label>
			<span><?php echo $ticket['ticket_id']; ?></span>
		</div>
		<div class="display-field">
			<label><?php _e( 'Status :', 'parlay-api' ); ?></label>
			<span><?php echo $ticket['status']; ?></span>
		</div>
		<div class="display-field">
			<label><?php _e( 'Date Created :', 'parlay-api' ); ?></label>
			<span><?php echo date( 'F j, Y, g:i a', strtotime( $ticket['date'] ) ); ?></span>
		</div>
		<div class="display-field">
			<label><?php _e( 'Issue :', 'parlay-api' ); ?></label>
			<span><?php echo $ticket['issue_name']; ?></span>
		</div>
	</div>
</div>

<?php if ( ! empty( $ticket['comments'] ) ) : ?>
<table class="display standard-table">
	<thead>
		<tr>
			<th><?php _e( 'Date', 'parlay-api' ); ?></th>
			<th><?php _e( 'Comments', 'parlay-api' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $ticket['comments'] as $comment ) : ?>
		<tr>
			<td><?php echo date( 'F j, Y, g:i a', strtotime( $comment['created'] ) ); ?></td>
			<td>
				<p><?php echo wp_kses_post( stripslashes( $comment['comment'] ) ); ?></p>
				<p><?php _e( 'By:', 'parlay-api' ); ?> <?php echo $comment['from']; ?></p>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php endif; ?>

<a class="add-interaction">+ <?php _e( 'Add Interaction', 'parlay-api' ); ?></a>

<div class="form-wrap">
	<div class="form-fields ticket-comment-field" style="display: none">
		<p>
			<label><?php _e( 'Comments', 'parlay-api' ); ?></label>
			<textarea class="field" name="comment"></textarea>
		<p>
		<div class="button-wrap">
			<label></label>
			<input type="submit" value="<?php esc_attr_e( 'Submit', 'parlay-api' ); ?>" class="">
		</div>
	</div>
<div>

