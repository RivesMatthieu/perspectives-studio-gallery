<?php
add_filter( 'run_wptexturize', '__return_false' );
?>
<div class="woocommerce bnpmercanetcw">
	<?php echo __('Redirecting... Please Wait ', 'woocommerce_bnpmercanetcw'); ?>
	<script type="text/javascript"> 
		top.location.href = '<?php echo $url; ?>';
	</script>
	

	<noscript>
		<a class="button btn btn-success bnpmercanetcw-continue-button" href="<?php echo $url; ?>" target="_top"><?php echo __('If you are not redirected shortly, click here.', 'woocommerce_bnpmercanetcw'); ?></a>
	</noscript>
</div>