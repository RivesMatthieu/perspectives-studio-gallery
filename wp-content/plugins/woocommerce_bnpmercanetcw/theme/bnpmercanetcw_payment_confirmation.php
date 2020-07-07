<form action="<?php print $form_target_url; ?>" method="post" class="bnpmercanetcw-payment-form woocommerce" name="bnpmercanetcw-payment-form" id="bnpmercanetcw-payment-container" >

	<script type="text/javascript">
		var successCallback = function(valid){
			var formObject = document.getElementById("bnpmercanetcw-payment-container");
			//We can not call formObject.submit(), because submit could be an element with id "submit" an not the submit function
			formObject.constructor.prototype.submit.call(formObject);
		}

		var failureCallback = function(errors, valid){
			alert(errors[Object.keys(errors)[0]]);
			jQuery('#bnpmercanetcw-submit').prop("disabled", false);

		}
	
		var submitFunction = function(event) {
			jQuery('#bnpmercanetcw-submit').prop("disabled", true);
			event.preventDefault();
			event.stopImmediatePropagation();

			var validateFunctionName = 'cwValidateFields';
			var validateFunction = window[validateFunctionName];
			
			if (typeof validateFunction != 'undefined') {
				validateFunction(successCallback,failureCallback);
				return false;
			}
			successCallback([]);
			return false;
			
		};

		if (document.readyState == "complete") {
			(function(){ 
				jQuery('#bnpmercanetcw-payment-container').submit(function(event){
					submitFunction(event);}
				); 
			})(); 
		}
		else if (window.addEventListener) { 
			window.addEventListener("load", function(){
				jQuery('#bnpmercanetcw-payment-container').submit(function(event){
					submitFunction(event);}
				); 
			}, false); 
		} 
		else if (window.attachEvent) { 
			window.attachEvent("onload", function(){
				jQuery('#bnpmercanetcw-payment-container').submit(function(event){
					submitFunction(event);}
				);
			}); 
		} 
		else {
			window.onload = function(){
				jQuery('#bnpmercanetcw-payment-container').submit(function(event){
					submitFunction(event);}
				);
			};
		}
	</script>

	<?php if (isset($error_message) && !empty($error_message)): ?>
		<p class="payment-error woocommerce-error">
			<?php print $error_message; ?>
		</p>
	<?php endif; ?>

	<?php if (!empty($hidden_fields)): ?> 
		<?php echo $hidden_fields ?>
	<?php endif;?>	
	<?php if(!empty($cwalias)) :?>
		<input type="hidden" name="cwalias" value="<?php print $cwalias; ?>" />
	<?php endif; ?>
	<?php if(!empty($cwfail)) :?>
		<input type="hidden" name="cwfail" value="<?php print $cwfail; ?>" />
	<?php endif; ?>
	<?php if(!empty($cwfailtoken)) :?>
		<input type="hidden" name="cwfailtoken" value="<?php print $cwfailtoken; ?>" />
	<?php endif; ?>
	<?php if(!empty($cwsubmit)) :?>
		<input type="hidden" name="cwsubmit" value="true" />
	<?php endif; ?>
	
	<?php if (isset($visible_fields) && !empty($visible_fields)): ?>
		<fieldset>
			<h3><?php print $paymentMethod; ?></h3>
			<?php print $visible_fields; ?>
		</fieldset>
	<?php endif; ?>
	

	
	<input type="submit" class="button btn btn-success alt bnpmercanetcw-payment-form-confirm" name="submit" id="bnpmercanetcw-submit" value="<?php print __("I confirm my payment", "woocommerce_bnpmercanetcw"); ?>" />
</form>
<div id="bnpmercanetcw-back-to-checkout" class="bnpmercanetcw-back-to-checkout woocommerce">
	<a href="<?php
	
		$option = BNPMercanetCw_Util::getCheckoutUrlPageId();
		echo get_permalink(BNPMercanetCw_Util::getPermalinkIdModified($option));
	
	?>" class="button btn btn-danger"><?php print __("Change payment method", "woocommerce_bnpmercanetcw");?></a>
</div>