<?php
/**
 * Template for launching the game
 *
 * @param string $launch_url
 *
 */
// if ($launch_url) {
//     header("Location: {$launch_url}");
//     exit();
// }
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php esc_html_e( 'Loading...', 'parlay-api' ); ?></title>
	<style>
		body {
			display: flex;
			justify-content: center;
			align-items: center;
			height: 100vh;
			background-color: #06020c;
			color: #fff;
		}
		/* Style the loader */
		.loader {
			border: 16px solid #f3f3f3; /* Light grey */
			border-top: 16px solid #3498db; /* Blue */
			border-radius: 50%;
			width: 80px;
			height: 80px;
			animation: spin 2s linear infinite;
		}

		@keyframes spin {
			0% { transform: rotate(0deg); }
			100% { transform: rotate(360deg); }
		}
	</style>
</head>
<body>
	<div class="loader"></div>
	<script>
		// JavaScript to handle the redirect after displaying the loader
		setTimeout(function() {
			window.location.href = '<?php echo $launch_url; ?>';
		});
	</script>
</body>
</html>
