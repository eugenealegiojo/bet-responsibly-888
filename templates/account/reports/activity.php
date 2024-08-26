<?php
/**
 * Account activity report
 */

$reports = \Parlay\Api\Account::get_account_reports();

$game_types = [
	'BA' => esc_html_e( 'Baccarat', 'parlay-api' ),
	'JE' => esc_html_e( 'Bejeweled', 'parlay-api' ),
	'BI' => esc_html_e( 'Bingo', 'parlay-api' ),
	'BB' => esc_html_e( 'Bingo Bets', 'parlay-api' ),
	'BS' => esc_html_e( 'Bingo Social', 'parlay-api' ),
	'BJ' => esc_html_e( 'Blackjack', 'parlay-api' ),
	'CB' => esc_html_e( 'Caribbean Poker', 'parlay-api' ),
	'CG' => esc_html_e( 'Community Games', 'parlay-api' ),
	'CR' => esc_html_e( 'Craps', 'parlay-api' ),
	'HL' => esc_html_e( 'Hi-Lo', 'parlay-api' ),
	'KE' => esc_html_e( 'Keno', 'parlay-api' ),
	'LB' => esc_html_e( 'Live Bingo', 'parlay-api' ),
	'BL' => esc_html_e( 'Lobby', 'parlay-api' ),
	'LD' => esc_html_e( 'Lotto', 'parlay-api' ),
	'MA' => esc_html_e( 'Match', 'parlay-api' ),
	'PS' => esc_html_e( 'Multiplayer Slots', 'parlay-api' ),
	'PT' => esc_html_e( 'Pull Tabs', 'parlay-api' ),
	'QZ' => esc_html_e( 'Quiz', 'parlay-api' ),
	'RL' => esc_html_e( 'Roulette', 'parlay-api' ),
	'SC' => esc_html_e( 'Scratch Card', 'parlay-api' ),
	'SG' => esc_html_e( 'Sit N\' Go', 'parlay-api' ),
	'SB' => esc_html_e( 'Skill Bingo', 'parlay-api' ),
	'SL' => esc_html_e( 'Slots', 'parlay-api' ),
	'SO' => esc_html_e( 'Social', 'parlay-api' ),
	'PK' => esc_html_e( 'Video/Table Poker', 'parlay-api' ),
	'VB' => esc_html_e( 'Video Bingo', 'parlay-api' ),
];
?>

<div class="page-filter">
	<?php parlay_template( 'account/reports/date-filter' ); ?>
	<div class="input-field">
		<label><?php _e( 'Game Type', 'parlay-api' ); ?></label>
		<div class="input-field-wrap">
			<select name="game-type[]" multiple>
				<option value=""><?php _e( 'All', 'parlay-api' ); ?></option>
				<?php foreach ( $game_types as $type => $name ) : ?>
					<option value="<?php echo $type; ?>"><?php echo $name; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	</div>
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

<?php if ( $reports ) : ?>
<table class="display custom-data-tables">
	<thead>
		<tr>
			<th><?php _e( 'ID', 'parlay-api' ); ?></th>
			<th><?php _e( 'From', 'parlay-api' ); ?></th>
			<th><?php _e( 'To', 'parlay-api' ); ?></th>
			<th><?php _e( 'Game type', 'parlay-api' ); ?></th>
			<th><?php _e( 'Won', 'parlay-api' ); ?></th>
			<th><?php _e( 'Currency', 'parlay-api' ); ?></th>
			<th><?php _e( 'Status', 'parlay-api' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $reports as $report ) : ?>
			<tr>
				<td><a href="<?php echo home_url( '/' . \Parlay\Api\Account::SLUG . '/activity/' . $report['gameSessionId'] ); ?>">
					<?php echo $report['gameSessionId']; ?></a>
				</td>
				<td><?php echo date( 'F j, Y, g:i a', strtotime( $report['from'] ) ); ?></td>
				<td><?php echo date( 'F j, Y, g:i a', strtotime( $report['to'] ) ); ?></td>
				<td><?php echo esc_html( $report['gameType'] ); ?></td>
				<td><?php echo esc_html( $report['won'] ); ?></td>
				<td><?php echo esc_html( $report['currency'] ); ?></td>
				<td><?php echo esc_html( $report['status'] ); ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php else : ?>
	<p><?php echo __( 'No reports found.', 'parlay-api' ); ?></p>
<?php endif; ?>
