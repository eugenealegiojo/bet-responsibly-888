<?php
/**
 * Open ticket
 *
 */

$subjects = \Parlay\Api\HelpDesk::get_ticket_issue_list();
?>
<div class="form-wrap">
	<div class="form-fields">
		<p>
			<label><?php esc_html_e( 'Subject', 'parlay-api' ); ?></label>
			<select class="field" name="subject_code">
				<?php foreach ( $subjects as $code => $subject ) : ?>
					<option value="<?php echo $code; ?>"><?php echo $subject; ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label><?php esc_html_e( 'Comment', 'parlay-api' ); ?></label>
			<textarea class="field" name="comment"></textarea>
		</p>
		<div class="button-wrap">
			<label></label>
			<input type="submit" value="<?php esc_attr_e( 'Submit', 'parlay-api' ); ?>" name="" class="sc_button sc_button_default ">
		</div>
	</div>
</div>
