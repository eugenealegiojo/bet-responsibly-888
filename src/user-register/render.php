<?php
/**
 * The following variables are exposed to the file:
 *     $attributes (array): The block attributes.
 *     $content (string): The block default content.
 *     $block (WP_Block): The block instance.
 */

?>
<div <?php echo get_block_wrapper_attributes(); ?>>
	<div id="parlay-registration-block">
		<h2>Register</h2>
		<form method="post">
			<input type="text" placeholder='Username'/>
			<input type="email" placeholder='Email' />
			<input type="password" placeholder='Password' />
			<button type="submit">Register</button>
		</form>
		<div className="registration-message">Registration messages will appear here.</div>
	</div>
</div>