<?php

if (!defined('ABSPATH')) exit; // just in case


function kpg_no_rc_img_control_2()  {

	if(!current_user_can('manage_options')) {
		die('Access Denied');
	}
	$options=kpg_nrci_get_options();
	extract($options);
	// check for update submit
	if (array_key_exists('kpg_nrci_update',$_POST)&&wp_verify_nonce($_POST['kpg_nrci_update'],'kpg_nrci_update')) { 
		// need to update replace
		if (array_key_exists('kpg_drag',$_POST)) {
			$drag=stripslashes($_POST['kpg_drag']);
		} else {
			$drag='N';
		}
		if (array_key_exists('kpg_touch',$_POST)) {
			$touch=stripslashes($_POST['kpg_touch']);
		} else {
			$touch='N';
		}
		if (array_key_exists('kpg_gesture',$_POST)) {
			$gesture=stripslashes($_POST['kpg_gesture']);
		} else {
			$gesture='N';
		}
		if (array_key_exists('kpg_allowforlogged',$_POST)) {
			$allowforlogged=stripslashes($_POST['kpg_allowforlogged']);
		} else {
			$allowforlogged='N';
		}
		if (array_key_exists('kpg_ios',$_POST)) {
			$ios=stripslashes($_POST['kpg_ios']);
		} else {
			$ios='N';
		}
		if (array_key_exists('kpg_admin',$_POST)) {
			$admin=stripslashes($_POST['kpg_admin']);
		} else {
			$admin='N';
		}
		// update options
		$options['drag']=$drag;
		$options['touch']=$touch;
		$options['gesture']=$gesture;
		$options['allowforlogged']=$allowforlogged;
		$options['ios']=$ios;
		$options['admin']=$admin;
		update_option('kpg_no_right_click_image',$options);

	}
	$nonce=wp_create_nonce('kpg_nrci_update');
	
	?>

	<div class="wrap" style="position:relative;">
	<h2>No Right Click Images Plugin</h2>
	<h4>The No Right Click Images Plugin is installed and working correctly.</h4>
	<div style="float:left;width:calc(100% - 245px);">
	<p>&nbsp;</p>
	<form method="post" action="">
	<input type="hidden" name="action" value="update" />
	<input type="hidden" name="kpg_nrci_update" value="<?php echo $nonce;?>" />
	<fieldset style="border:thin solid black;padding:6px;width:100%;">
	<legend> <span style="font-weight:bold;font-size:1.5em">Options</span> </legend>
	Allow Right Click for Logged Users:
	<input type="checkbox" <?php if ($allowforlogged=='Y') {echo 'checked="true"';} ?> value="Y" name="kpg_allowforlogged" />
	You may wish to allow logged in users to copy images. You can do this by checking this box. <br />
	Disable Dragging of images:
	<input type="checkbox" <?php if ($drag=='Y') {echo 'checked="true"';} ?> value="Y" name="kpg_drag" />
	This will prevent images from being dragged to the desktop or image software <br />
	Disable Touch events:
	<input type="checkbox" <?php if ($touch=='Y') {echo 'checked="true"';} ?> value="Y" name="kpg_touch" />
	Prevents touch events on images, but if images are used as links or buttons this may cause problems. <br />
	Disable Gesture events:
	<input type="checkbox" <?php if ($gesture=='Y') {echo 'checked="true"';} ?> value="Y" name="kpg_gesture" />
	Prevents some gestures. If you site uses image gestures for images this may cause problems. <br />
	Disable context menu on Apple devices:
	<input type="checkbox" <?php if ($ios=='Y') {echo 'checked="true"';} ?> value="Y" name="kpg_ios" />
	Adds a style to images on Apple IOS devices to prevent the context menu<br />
	Admin can always right click images:
	<input type="checkbox" <?php if ($admin=='Y') {echo 'checked="true"';} ?> value="Y" name="kpg_admin" />
	Admins can always right click images even if logged in users cannot.<br />
	</fieldset>
	<br>
	<input type="submit" name="Submit" class="button-primary" value="<?php echo("Save Changes"); ?>" />
	</form>
	<p>This plugin installs a little javascript in your pages. When your page finishes loading, the javascript sets properties the images to supress the context menu. This prevents casual users from using the copy function to grab an image.</p>
	<p>There are many ways to bypass this plugin and it is impossible to prevent a determined and resourceful user from stealing images, but this plugin will prevent casual users from glomming your images.</p>
	<p>The context menu is disabled on images and simple elements with background images, but will not work in some cases depending on which element receives the mouse click.</p>
	<p>If you have uploaded your images to WordPress so that the images from the gallery can be opened in their own window, then this plugin will not work on the clicked image.</p>
	<br>
	<fieldset style="border:thin solid black;padding:6px;width:100%;">
	<legend> <span style="font-weight:bold;font-size:1.5em">Support the Programmer</span> </legend>
	<p>If you feel that you"d like to encourage me, you could <a href="https://www.amazon.com/default/e/B004C64N24">buy one of my books</a>. I write short stories for fun and I have sold about 50 stories to various
	magazines. The books are cheap and very interesting.</p>
	<p>You can also donate a few dollars. Cash is a little short right now, so my wife convinced me to go the low road and ask for money. There are three levels of donations. 
	<br>First, at $2.50 you can support me. I like this level because it does not put any stress on you. I think everyone can afford this without any pain. 
	<br>Second, for those who think they owe a little more, I have a $9.99 level. This is for those who have money to burn and drive expensive sports cars. 
	<br>Last, there is the $29.99 level. I don"t expect anyone to use this level, but there are possibly a few sysops with a company credit card, and an unlimited budget who might sympathize with a fellow coder and click this button.</p>
	<p>You can pay using PayPal. All you need is a credit card. There is no account required. Just click and follow the instructions. You can request a refund and I will gladly comply.</p>
	<table style="border:grey solid thin;min-width:50%">
	<thead>
	<tr style="background-color:ivory;">
	<th>Support Level</th>
	<th>PayPal</th>
	</tr>
	</thead>
	<tbody>
	<tr>
	<td>Level 1) $2.50 <br />
	Grateful User</td>
	<td><form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	<input name="cmd" type="hidden" value="_s-xclick" />
	<input name="hosted_button_id" type="hidden" value="9V4ZE99S2VYQA" />
	<input alt="PayPal - The safer, easier way to pay online!" name="submit"
	src="https://www.paypalobjects.com/en_US/i/btn/btn_buynowCC_LG.gif" type="image" />
	<img src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" alt="" width="1" height="1" border="0" />
	</form></td>
	</tr>
	<tr>
	<td>Level 2) $9.99 <br />
	Generous Benefactor</td>
	<td><form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	<input name="cmd" type="hidden" value="_s-xclick" />
	<input name="hosted_button_id" type="hidden" value="2UCJBHR44HQAJ" />
	<input alt="PayPal - The safer, easier way to pay online!" name="submit"
	src="https://www.paypalobjects.com/en_US/i/btn/btn_buynowCC_LG.gif" type="image" />
	<img src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" alt="" width="1" height="1" border="0" />
	</form></td>
	</tr>
	<tr>
	<td>Level 3) $29.99 <br />
	Wealthy patron</td>
	<td><form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	<input name="cmd" type="hidden" value="_s-xclick" />
	<input name="hosted_button_id" type="hidden" value="EG83EZCTGYYQQ" />
	<input alt="PayPal - The safer, easier way to pay online!" name="submit"
	src="https://www.paypalobjects.com/en_US/i/btn/btn_buynowCC_LG.gif" type="image" />
	<img src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" alt="" width="1" height="1" border="0" />
	</form></td>
	</tr>
	</tbody>
	</table>
	</fieldset>
	</div>
	</div>
	<?php 
	} 

// end of module

?>
