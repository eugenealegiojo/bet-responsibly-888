<?php
/**
 * Redirects to ecommerce
 *
 */
$ecomm_data = isset( $_POST['data'] ) ? json_decode( stripslashes( $_POST['data'] ), true ) : [];
$ecomm_url  = isset( $_POST['url'] ) ? $_POST['url'] : '';

if ( empty( $ecomm_url ) || empty( $ecomm_data ) ) {
	header( 'HTTP/1.0 403 Forbidden' );
	exit; // Exit if accessed directly
}
?>

<form action="<?php echo $ecomm_url; ?>" name="redirectForm" id="redirectForm" method="post">
	<?php
	foreach ( $ecomm_data as $name => $value ) {
		?>
		<input type="hidden" name="<?php echo $name; ?>" value="<?php echo $value; ?>">
		<?php
	}
	?>
</form>
<script>document.getElementById("redirectForm").submit();</script>
