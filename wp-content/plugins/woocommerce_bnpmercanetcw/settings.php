<?php

require_once 'BNPMercanetCw/BackendFormRenderer.php';
require_once 'Customweb/Util/Url.php';
require_once 'Customweb/Payment/Authorization/DefaultInvoiceItem.php';
require_once 'Customweb/Payment/BackendOperation/Adapter/Service/ICapture.php';
require_once 'Customweb/Form/Control/IEditableControl.php';
require_once 'Customweb/Payment/BackendOperation/Adapter/Service/ICancel.php';
require_once 'Customweb/IForm.php';
require_once 'Customweb/Form.php';
require_once 'Customweb/Core/Http/ContextRequest.php';
require_once 'Customweb/Form/Control/MultiControl.php';
require_once 'Customweb/Util/Currency.php';
require_once 'Customweb/Payment/Authorization/IInvoiceItem.php';
require_once 'Customweb/Payment/BackendOperation/Adapter/Service/IRefund.php';
require_once 'Customweb/Licensing/BNPMercanetCw/License.php';



// Make sure we don't expose any info if called directly        			 				    			
if (!function_exists('add_action')) {
	echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
	exit();
}

// Add some CSS and JS for admin        			 				    			
function woocommerce_bnpmercanetcw_admin_add_setting_styles_scripts($hook){
	if($hook != 'post.php' && $hook != 'dashboard_page_wpsc-purchase-logs' && stripos($hook,'woocommerce-bnpmercanetcw') === false){
		return;
	}
	wp_register_style('woocommerce_bnpmercanetcw_admin_styles', plugins_url('resources/css/settings.css', __FILE__));
	wp_enqueue_style('woocommerce_bnpmercanetcw_admin_styles');
	
	wp_register_script('woocommerce_bnpmercanetcw_admin_js', plugins_url('resources/js/settings.js', __FILE__));
	wp_enqueue_script('woocommerce_bnpmercanetcw_admin_js');
}
add_action('admin_enqueue_scripts', 'woocommerce_bnpmercanetcw_admin_add_setting_styles_scripts');

function woocommerce_bnpmercanetcw_admin_notice_handler(){
	if (get_transient(get_current_user_id() . '_bnpmercanetcw_am') !== false) {
		
		foreach (get_transient(get_current_user_id() . '_bnpmercanetcw_am') as $message) {
			$cssClass = '';
			if (strtolower($message['type']) == 'error') {
				$cssClass = 'error';
			}
			else if (strtolower($message['type']) == 'info') {
				$cssClass = 'updated';
			}
			
			echo '<div class="' . $cssClass . '">';
			echo '<p>BNP Paribas Mercanet: ' . $message['message'] . '</p>';
			echo '</div>';
		}
		delete_transient(get_current_user_id() . '_bnpmercanetcw_am');
	}
}
add_action('admin_notices', 'woocommerce_bnpmercanetcw_admin_notice_handler');

function woocommerce_bnpmercanetcw_admin_show_message($message, $type){
	$existing = array();
	if (get_transient(get_current_user_id() . '_bnpmercanetcw_am') === false) {
		$existing = get_transient(get_current_user_id() . '_bnpmercanetcw_am');
	}
	$existing[] = array(
		'message' => $message,
		'type' => $type 
	);
	set_transient(get_current_user_id() . '_bnpmercanetcw_am', $existing);
}

/**
 * Add the configuration menu
 */
function woocommerce_bnpmercanetcw_menu(){
	add_menu_page('BNP Paribas Mercanet', __('BNP Paribas Mercanet', 'woocommerce_bnpmercanetcw'), 
			'manage_woocommerce', 'woocommerce-bnpmercanetcw', 'woocommerce_bnpmercanetcw_options');
	
	if (isset($_REQUEST['page']) && strpos($_REQUEST['page'], 'woocommerce-bnpmercanetcw') !== false) {
		$container = BNPMercanetCw_Util::createContainer();
		if ($container->hasBean('Customweb_Payment_BackendOperation_Form_IAdapter')) {
			$adapter = $container->getBean('Customweb_Payment_BackendOperation_Form_IAdapter');
			foreach ($adapter->getForms() as $form) {
				add_submenu_page('woocommerce-bnpmercanetcw', 'BNP Paribas Mercanet ' . $form->getTitle(), $form->getTitle(), 
						'manage_woocommerce', 'woocommerce-bnpmercanetcw-' . $form->getMachineName(), 
						'woocommerce_bnpmercanetcw_extended_options');
			}
		}
	}
	
	add_submenu_page(null, 'BNP Paribas Mercanet Capture', 'BNP Paribas Mercanet Capture', 'manage_woocommerce', 
			'woocommerce-bnpmercanetcw_capture', 'woocommerce_bnpmercanetcw_render_capture');
	add_submenu_page(null, 'BNP Paribas Mercanet Cancel', 'BNP Paribas Mercanet Cancel', 'manage_woocommerce', 
			'woocommerce-bnpmercanetcw_cancel', 'woocommerce_bnpmercanetcw_render_cancel');
	add_submenu_page(null, 'BNP Paribas Mercanet Refund', 'BNP Paribas Mercanet Refund', 'manage_woocommerce', 
			'woocommerce-bnpmercanetcw_refund', 'woocommerce_bnpmercanetcw_render_refund');
}
add_action('admin_menu', 'woocommerce_bnpmercanetcw_menu');

function woocommerce_bnpmercanetcw_render_cancel(){
	
	
	
	

	$request = Customweb_Core_Http_ContextRequest::getInstance();
	$query = $request->getParsedQuery();
	$post = $request->getParsedBody();
	$transactionId = $query['cwTransactionId'];
	
	if (empty($transactionId)) {
		wp_redirect(get_option('siteurl') . '/wp-admin');
		exit();
	}
	
	$transaction = BNPMercanetCw_Util::getTransactionById($transactionId);
	$orderId = $transaction->getPostId();
	$url = str_replace('>orderId', $orderId, get_admin_url() . 'post.php?post=>orderId&action=edit');
	if ($request->getMethod() == 'POST') {
		if (isset($post['cancel'])) {
			$adapter = BNPMercanetCw_Util::createContainer()->getBean('Customweb_Payment_BackendOperation_Adapter_Service_ICancel');
			if (!($adapter instanceof Customweb_Payment_BackendOperation_Adapter_Service_ICancel)) {
				throw new Exception("No adapter with interface 'Customweb_Payment_BackendOperation_Adapter_Service_ICancel' provided.");
			}
			
			try {
				$adapter->cancel($transaction->getTransactionObject());
				woocommerce_bnpmercanetcw_admin_show_message(
						__("Successfully cancelled the transaction.", 'woocommerce_bnpmercanetcw'), 'info');
			}
			catch (Exception $e) {
				woocommerce_bnpmercanetcw_admin_show_message($e->getMessage(), 'error');
			}
			BNPMercanetCw_Util::getEntityManager()->persist($transaction);
		}
		wp_redirect($url);
		exit();
	}
	else {
		if (!$transaction->getTransactionObject()->isCancelPossible()) {
			woocommerce_bnpmercanetcw_admin_show_message(__('Cancel not possible', 'woocommerce_bnpmercanetcw'), 'info');
			wp_redirect($url);
			exit();
		}
		if (isset($_GET['noheader'])) {
			require_once (ABSPATH . 'wp-admin/admin-header.php');
		}
		
		echo '<div class="wrap">';
		echo '<form method="POST" class="bnpmercanetcw-line-item-grid" id="cancel-form">';
		echo '<table class="list">
				<tbody>';
		echo '<tr>
				<td class="left-align">' . __('Are you sure you want to cancel this transaction?', 'woocommerce_bnpmercanetcw') . '</td>
			</tr>';
		echo '<tr>
				<td colspan="1" class="left-align"><a class="button" href="' . $url . '">' . __('No', 'woocommerce_bnpmercanetcw') . '</a></td>
				<td colspan="1" class="right-align">
					<input class="button" type="submit" name="cancel" value="' . __('Yes', 'woocommerce_bnpmercanetcw') . '" />
				</td>
			</tr>
								</tfoot>
			</table>
		</form>';
		
		echo '</div>';
	}
	
	
}

function woocommerce_bnpmercanetcw_render_capture(){
	
	
	
	$request = Customweb_Core_Http_ContextRequest::getInstance();
	$query = $request->getParsedQuery();
	$post = $request->getParsedBody();
	$transactionId = $query['cwTransactionId'];
	
	if (empty($transactionId)) {
		wp_redirect(get_option('siteurl') . '/wp-admin');
		exit();
	}
	
	$transaction = BNPMercanetCw_Util::getTransactionById($transactionId);
	$orderId = $transaction->getPostId();
	$url = str_replace('>orderId', $orderId, get_admin_url() . 'post.php?post=>orderId&action=edit');
	if ($request->getMethod() == 'POST') {
		
		if (isset($post['quantity'])) {
			
			$captureLineItems = array();
			$lineItems = $transaction->getTransactionObject()->getUncapturedLineItems();
			foreach ($post['quantity'] as $index => $quantity) {
				if (isset($post['price_including'][$index]) && floatval($post['price_including'][$index]) != 0) {
					$originalItem = $lineItems[$index];
					if ($originalItem->getType() == Customweb_Payment_Authorization_IInvoiceItem::TYPE_DISCOUNT) {
						$priceModifier = -1;
					}
					else {
						$priceModifier = 1;
					}
					$captureLineItems[$index] = new Customweb_Payment_Authorization_DefaultInvoiceItem($originalItem->getSku(), 
							$originalItem->getName(), $originalItem->getTaxRate(), $priceModifier * floatval($post['price_including'][$index]), 
							$quantity, $originalItem->getType());
				}
			}
			if (count($captureLineItems) > 0) {
				$adapter = BNPMercanetCw_Util::createContainer()->getBean('Customweb_Payment_BackendOperation_Adapter_Service_ICapture');
				if (!($adapter instanceof Customweb_Payment_BackendOperation_Adapter_Service_ICapture)) {
					throw new Exception("No adapter with interface 'Customweb_Payment_BackendOperation_Adapter_Service_ICapture' provided.");
				}
				
				$close = false;
				if (isset($post['close']) && $post['close'] == 'on') {
					$close = true;
				}
				try {
					$adapter->partialCapture($transaction->getTransactionObject(), $captureLineItems, $close);
					woocommerce_bnpmercanetcw_admin_show_message(
							__("Successfully added a new capture.", 'woocommerce_bnpmercanetcw'), 'info');
				}
				catch (Exception $e) {
					woocommerce_bnpmercanetcw_admin_show_message($e->getMessage(), 'error');
				}
				BNPMercanetCw_Util::getEntityManager()->persist($transaction);
			}
		}
		
		wp_redirect($url);
		exit();
	}
	else {
		if (!$transaction->getTransactionObject()->isPartialCapturePossible()) {
			woocommerce_bnpmercanetcw_admin_show_message(__('Capture not possible', 'woocommerce_bnpmercanetcw'), 'info');
			
			wp_redirect($url);
			exit();
		}
		if (isset($_GET['noheader'])) {
			require_once (ABSPATH . 'wp-admin/admin-header.php');
		}
		
		echo '<div class="wrap">';
		echo '<form method="POST" class="bnpmercanetcw-line-item-grid" id="capture-form">';
		echo '<input type="hidden" id="bnpmercanetcw-decimal-places" value="' .
				 Customweb_Util_Currency::getDecimalPlaces($transaction->getTransactionObject()->getCurrencyCode()) . '" />';
		echo '<input type="hidden" id="bnpmercanetcw-currency-code" value="' . strtoupper($transaction->getTransactionObject()->getCurrencyCode()) .
				 '" />';
		echo '<table class="list">
					<thead>
						<tr>
						<th class="left-align">' . __('Name', 'woocommerce_bnpmercanetcw') . '</th>
						<th class="left-align">' . __('SKU', 'woocommerce_bnpmercanetcw') . '</th>
						<th class="left-align">' . __('Type', 'woocommerce_bnpmercanetcw') . '</th>
						<th class="left-align">' . __('Tax Rate', 'woocommerce_bnpmercanetcw') . '</th>
						<th class="right-align">' . __('Quantity', 
				'woocommerce_bnpmercanetcw') . '</th>
						<th class="right-align">' . __('Total Amount (excl. Tax)', 'woocommerce_bnpmercanetcw') . '</th>
						<th class="right-align">' . __('Total Amount (incl. Tax)', 'woocommerce_bnpmercanetcw') . '</th>
						</tr>
				</thead>
				<tbody>';
		foreach ($transaction->getTransactionObject()->getUncapturedLineItems() as $index => $item) {
			
			$amountExcludingTax = Customweb_Util_Currency::formatAmount($item->getAmountExcludingTax(), 
					$transaction->getTransactionObject()->getCurrencyCode());
			$amountIncludingTax = Customweb_Util_Currency::formatAmount($item->getAmountIncludingTax(), 
					$transaction->getTransactionObject()->getCurrencyCode());
			if ($item->getType() == Customweb_Payment_Authorization_IInvoiceItem::TYPE_DISCOUNT) {
				$amountExcludingTax = $amountExcludingTax * -1;
				$amountIncludingTax = $amountIncludingTax * -1;
			}
			echo '<tr id="line-item-row-' . $index . '" class="line-item-row" data-line-item-index="' . $index, '" >
						<td class="left-align">' . $item->getName() . '</td>
						<td class="left-align">' . $item->getSku() . '</td>
						<td class="left-align">' . $item->getType() . '</td>
						<td class="left-align">' . round($item->getTaxRate(), 2) . ' %<input type="hidden" class="tax-rate" value="' . $item->getTaxRate() . '" /></td>
						<td class="right-align"><input type="text" class="line-item-quantity" name="quantity[' . $index . ']" value="' . $item->getQuantity() . '" /></td>
						<td class="right-align"><input type="text" class="line-item-price-excluding" name="price_excluding[' . $index . ']" value="' .
					 $amountExcludingTax . '" /></td>
						<td class="right-align"><input type="text" class="line-item-price-including" name="price_including[' . $index . ']" value="' .
					 $amountIncludingTax . '" /></td>
					</tr>';
		}
		echo '</tbody>
				<tfoot>
					<tr>
						<td colspan="6" class="right-align">' . __('Total Capture Amount', 'woocommerce_bnpmercanetcw') . ':</td>
						<td id="line-item-total" class="right-align">' . Customweb_Util_Currency::formatAmount(
				$transaction->getTransactionObject()->getCapturableAmount(), $transaction->getTransactionObject()->getCurrencyCode()) .
				 strtoupper($transaction->getTransactionObject()->getCurrencyCode()) . '
					</tr>';
		
		if ($transaction->getTransactionObject()->isCaptureClosable()) {
			
			echo '<tr>
					<td colspan="7" class="right-align">
						<label for="close-transaction">' . __('Close transaction for further captures', 'woocommerce_bnpmercanetcw') . '</label>
						<input id="close-transaction" type="checkbox" name="close" value="on" />
					</td>
				</tr>';
		}
		
		echo '<tr>
				<td colspan="2" class="left-align"><a class="button" href="' . $url . '">' . __('Back', 'woocommerce_bnpmercanetcw') . '</a></td>
				<td colspan="5" class="right-align">
					<input class="button" type="submit" value="' . __('Capture', 'woocommerce_bnpmercanetcw') . '" />
				</td>
			</tr>
			</tfoot>
			</table>
		</form>';
		
		echo '</div>';
	}
	
	
}

function woocommerce_bnpmercanetcw_render_refund(){
	
	
	
	$request = Customweb_Core_Http_ContextRequest::getInstance();
	$query = $request->getParsedQuery();
	$post = $request->getParsedBody();
	$transactionId = $query['cwTransactionId'];
	
	if (empty($transactionId)) {
		wp_redirect(get_option('siteurl') . '/wp-admin');
		exit();
	}
	
	$transaction = BNPMercanetCw_Util::getTransactionById($transactionId);
	$orderId = $transaction->getPostId();
	$url = str_replace('>orderId', $orderId, get_admin_url() . 'post.php?post=>orderId&action=edit');
	if ($request->getMethod() == 'POST') {
		
		if (isset($post['quantity'])) {
			
			$refundLineItems = array();
			$lineItems = $transaction->getTransactionObject()->getNonRefundedLineItems();
			foreach ($post['quantity'] as $index => $quantity) {
				if (isset($post['price_including'][$index]) && floatval($post['price_including'][$index]) != 0) {
					$originalItem = $lineItems[$index];
					if ($originalItem->getType() == Customweb_Payment_Authorization_IInvoiceItem::TYPE_DISCOUNT) {
						$priceModifier = -1;
					}
					else {
						$priceModifier = 1;
					}
					$refundLineItems[$index] = new Customweb_Payment_Authorization_DefaultInvoiceItem($originalItem->getSku(), 
							$originalItem->getName(), $originalItem->getTaxRate(), $priceModifier * floatval($post['price_including'][$index]), 
							$quantity, $originalItem->getType());
				}
			}
			if (count($refundLineItems) > 0) {
				$adapter = BNPMercanetCw_Util::createContainer()->getBean('Customweb_Payment_BackendOperation_Adapter_Service_IRefund');
				if (!($adapter instanceof Customweb_Payment_BackendOperation_Adapter_Service_IRefund)) {
					throw new Exception("No adapter with interface 'Customweb_Payment_BackendOperation_Adapter_Service_IRefund' provided.");
				}
				
				$close = false;
				if (isset($post['close']) && $post['close'] == 'on') {
					$close = true;
				}
				try {
					$adapter->partialRefund($transaction->getTransactionObject(), $refundLineItems, $close);
					woocommerce_bnpmercanetcw_admin_show_message(
							__("Successfully added a new refund.", 'woocommerce_bnpmercanetcw'), 'info');
				}
				catch (Exception $e) {
					woocommerce_bnpmercanetcw_admin_show_message($e->getMessage(), 'error');
				}
				BNPMercanetCw_Util::getEntityManager()->persist($transaction);
			}
		}
		wp_redirect($url);
		exit();
	}
	else {
		if (!$transaction->getTransactionObject()->isPartialRefundPossible()) {
			woocommerce_bnpmercanetcw_admin_show_message(__('Refund not possible', 'woocommerce_bnpmercanetcw'), 'info');
			wp_redirect($url);
			exit();
		}
		if (isset($query['noheader'])) {
			require_once (ABSPATH . 'wp-admin/admin-header.php');
		}
		
		echo '<div class="wrap">';
		echo '<form method="POST" class="bnpmercanetcw-line-item-grid" id="refund-form">';
		echo '<input type="hidden" id="bnpmercanetcw-decimal-places" value="' .
				 Customweb_Util_Currency::getDecimalPlaces($transaction->getTransactionObject()->getCurrencyCode()) . '" />';
		echo '<input type="hidden" id="bnpmercanetcw-currency-code" value="' . strtoupper($transaction->getTransactionObject()->getCurrencyCode()) .
				 '" />';
		echo '<table class="list">
					<thead>
						<tr>
						<th class="left-align">' . __('Name', 'woocommerce_bnpmercanetcw') . '</th>
						<th class="left-align">' . __('SKU', 'woocommerce_bnpmercanetcw') . '</th>
						<th class="left-align">' . __('Type', 'woocommerce_bnpmercanetcw') . '</th>
						<th class="left-align">' . __('Tax Rate', 'woocommerce_bnpmercanetcw') . '</th>
						<th class="right-align">' . __('Quantity', 
				'woocommerce_bnpmercanetcw') . '</th>
						<th class="right-align">' . __('Total Amount (excl. Tax)', 'woocommerce_bnpmercanetcw') . '</th>
						<th class="right-align">' . __('Total Amount (incl. Tax)', 'woocommerce_bnpmercanetcw') . '</th>
						</tr>
				</thead>
				<tbody>';
		foreach ($transaction->getTransactionObject()->getNonRefundedLineItems() as $index => $item) {
			$amountExcludingTax = Customweb_Util_Currency::formatAmount($item->getAmountExcludingTax(), 
					$transaction->getTransactionObject()->getCurrencyCode());
			$amountIncludingTax = Customweb_Util_Currency::formatAmount($item->getAmountIncludingTax(), 
					$transaction->getTransactionObject()->getCurrencyCode());
			if ($item->getType() == Customweb_Payment_Authorization_IInvoiceItem::TYPE_DISCOUNT) {
				$amountExcludingTax = $amountExcludingTax * -1;
				$amountIncludingTax = $amountIncludingTax * -1;
			}
			echo '<tr id="line-item-row-' . $index . '" class="line-item-row" data-line-item-index="' . $index, '" >
					<td class="left-align">' . $item->getName() . '</td>
					<td class="left-align">' . $item->getSku() . '</td>
					<td class="left-align">' . $item->getType() . '</td>
					<td class="left-align">' . round($item->getTaxRate(), 2) . ' %<input type="hidden" class="tax-rate" value="' . $item->getTaxRate() . '" /></td>
					<td class="right-align"><input type="text" class="line-item-quantity" name="quantity[' . $index . ']" value="' . $item->getQuantity() . '" /></td>
					<td class="right-align"><input type="text" class="line-item-price-excluding" name="price_excluding[' . $index . ']" value="' .
					 $amountExcludingTax . '" /></td>
					<td class="right-align"><input type="text" class="line-item-price-including" name="price_including[' . $index . ']" value="' .
					 $amountIncludingTax . '" /></td>
				</tr>';
		}
		echo '</tbody>
				<tfoot>
					<tr>
						<td colspan="6" class="right-align">' . __('Total Refund Amount', 'woocommerce_bnpmercanetcw') . ':</td>
						<td id="line-item-total" class="right-align">' . Customweb_Util_Currency::formatAmount(
				$transaction->getTransactionObject()->getRefundableAmount(), $transaction->getTransactionObject()->getCurrencyCode()) .
				 strtoupper($transaction->getTransactionObject()->getCurrencyCode()) . '
						</tr>';
		
		if ($transaction->getTransactionObject()->isRefundClosable()) {
			echo '<tr>
					<td colspan="7" class="right-align">
						<label for="close-transaction">' . __('Close transaction for further refunds', 'woocommerce_bnpmercanetcw') . '</label>
						<input id="close-transaction" type="checkbox" name="close" value="on" />
					</td>
				</tr>';
		}
		
		echo '<tr>
				<td colspan="2" class="left-align"><a class="button" href="' . $url . '">' . __('Back', 'woocommerce_bnpmercanetcw') . '</a></td>
				<td colspan="5" class="right-align">
					<input class="button" type="submit" value="' . __('Refund', 'woocommerce_bnpmercanetcw') . '" />
				</td>
			</tr>
		</tfoot>
		</table>
		</form>';
		
		echo '</div>';
	}
	
	
}

function woocommerce_bnpmercanetcw_extended_options(){
	$container = BNPMercanetCw_Util::createContainer();
	$request = Customweb_Core_Http_ContextRequest::getInstance();
	$query = $request->getParsedQuery();
	$formName = substr($query['page'], strlen('woocommerce-bnpmercanetcw-'));
	
	$renderer = new BNPMercanetCw_BackendFormRenderer();
	
	if ($container->hasBean('Customweb_Payment_BackendOperation_Form_IAdapter')) {
		$adapter = $container->getBean('Customweb_Payment_BackendOperation_Form_IAdapter');
		
		foreach ($adapter->getForms() as $form) {
			if ($form->getMachineName() == $formName) {
				$currentForm = $form;
				break;
			}
		}
		if ($currentForm === null) {
			if (isset($query['noheader'])) {
				require_once (ABSPATH . 'wp-admin/admin-header.php');
			}
			return;
		}
		
		if ($request->getMethod() == 'POST') {
			
			$pressedButton = null;
			$body = stripslashes_deep($request->getParsedBody());
			foreach ($form->getButtons() as $button) {
				
				if (array_key_exists($button->getMachineName(), $body['button'])) {
					$pressedButton = $button;
					break;
				}
			}
			$formData = array();
			foreach ($form->getElements() as $element) {
				$control = $element->getControl();
				if (!($control instanceof Customweb_Form_Control_IEditableControl)) {
					continue;
				}
				$dataValue = $control->getFormDataValue($body);
				if ($control instanceof Customweb_Form_Control_MultiControl) {
					foreach (woocommerce_bnpmercanetcw_array_flatten($dataValue) as $key => $value) {
						$formData[$key] = $value;
					}
				}
				else {
					$nameAsArray = $control->getControlNameAsArray();
					if (count($nameAsArray) > 1) {
						$tmpArray = array(
							$nameAsArray[count($nameAsArray) - 1] => $dataValue 
						);
						$iterator = count($nameAsArray) - 2;
						while ($iterator > 0) {
							$tmpArray = array(
								$nameAsArray[$iterator] => $tmpArray 
							);
							$iterator--;
						}
						if (isset($formData[$nameAsArray[0]])) {
							$formData[$nameAsArray[0]] = array_merge_recursive($formData[$nameAsArray[0]], $tmpArray);
						}
						else {
							$formData[$nameAsArray[0]] = $tmpArray;
						}
					}
					else {
						$formData[$control->getControlName()] = $dataValue;
					}
				}
			}
			$adapter->processForm($currentForm, $pressedButton, $formData);
			wp_redirect(Customweb_Util_Url::appendParameters(get_admin_url(null,'admin.php'), $request->getParsedQuery()));
			die();
		}
		
		if (isset($query['noheader'])) {
			require_once (ABSPATH . 'wp-admin/admin-header.php');
		}
		
		$currentForm = null;
		foreach ($adapter->getForms() as $form) {
			if ($form->getMachineName() == $formName) {
				$currentForm = $form;
				break;
			}
		}
		
		if ($currentForm->isProcessable()) {
			$currentForm = new Customweb_Form($currentForm);
			$currentForm->setRequestMethod(Customweb_IForm::REQUEST_METHOD_POST);
			$currentForm->setTargetUrl(
					Customweb_Util_Url::appendParameters(get_admin_url(null,'admin.php'), 
							array_merge($request->getParsedQuery(), array(
								'noheader' => 'true' 
							))));
		}
		echo '<div class="wrap">';
		echo $renderer->renderForm($currentForm);
		echo '</div>';
	}
}

function woocommerce_bnpmercanetcw_array_flatten($array){
	$return = array();
	foreach ($array as $key => $value) {
		if (is_array($value)) {
			$return = array_merge($return, woocommerce_bnpmercanetcw_array_flatten($value));
		}
		else {
			$return[$key] = $value;
		}
	}
	return $return;
}

/**
 * Setup the configuration page with the callbacks to the configuration API.
 */
function woocommerce_bnpmercanetcw_options(){
	if (!current_user_can('manage_woocommerce')) {
		wp_die(__('You do not have sufficient permissions to access this page.'));
	}
	require_once 'Customweb/Licensing/BNPMercanetCw/License.php';
Customweb_Licensing_BNPMercanetCw_License::run('smup86amjhdaqfpi');
	echo '<div class="wrap">';
	
	echo '<form method="post" action="options.php" enctype="multipart/form-data">';
	settings_fields('woocommerce-bnpmercanetcw');
	do_settings_sections('woocommerce-bnpmercanetcw');
	
	echo '<p class="submit">';
	echo '<input type="submit" name="submit" id="submit" class="button-primary" value="' . __('Save Changes') . '" />';
	echo '</p>';
	
	echo '</form>';
	echo '</div>';
}



/**
 * Register Settings
 */
function woocommerce_bnpmercanetcw_admin_init(){
	add_settings_section('woocommerce_bnpmercanetcw', 'BNP Paribas Mercanet Basics', 
			'woocommerce_bnpmercanetcw_section_callback', 'woocommerce-bnpmercanetcw');
	register_setting('woocommerce-bnpmercanetcw', 'woocommerce_bnpmercanetcw_operation_mode');
	
	add_settings_field('woocommerce_bnpmercanetcw_operation_mode', __("Operation Mode", 'woocommerce_bnpmercanetcw'), 'woocommerce_bnpmercanetcw_option_callback_operation_mode', 'woocommerce-bnpmercanetcw', 'woocommerce_bnpmercanetcw');
	register_setting('woocommerce-bnpmercanetcw', 'woocommerce_bnpmercanetcw_live_merchant_id');
	
	add_settings_field('woocommerce_bnpmercanetcw_live_merchant_id', __("Live Merchant ID", 'woocommerce_bnpmercanetcw'), 'woocommerce_bnpmercanetcw_option_callback_live_merchant_id', 'woocommerce-bnpmercanetcw', 'woocommerce_bnpmercanetcw');
	register_setting('woocommerce-bnpmercanetcw', 'woocommerce_bnpmercanetcw_live_secret_key');
	
	add_settings_field('woocommerce_bnpmercanetcw_live_secret_key', __("Live Secret Key", 'woocommerce_bnpmercanetcw'), 'woocommerce_bnpmercanetcw_option_callback_live_secret_key', 'woocommerce-bnpmercanetcw', 'woocommerce_bnpmercanetcw');
	register_setting('woocommerce-bnpmercanetcw', 'woocommerce_bnpmercanetcw_live_secret_key_version');
	
	add_settings_field('woocommerce_bnpmercanetcw_live_secret_key_version', __("Live Secret Key Version", 'woocommerce_bnpmercanetcw'), 'woocommerce_bnpmercanetcw_option_callback_live_secret_key_version', 'woocommerce-bnpmercanetcw', 'woocommerce_bnpmercanetcw');
	register_setting('woocommerce-bnpmercanetcw', 'woocommerce_bnpmercanetcw_test_merchant_id');
	
	add_settings_field('woocommerce_bnpmercanetcw_test_merchant_id', __("Test Merchant ID", 'woocommerce_bnpmercanetcw'), 'woocommerce_bnpmercanetcw_option_callback_test_merchant_id', 'woocommerce-bnpmercanetcw', 'woocommerce_bnpmercanetcw');
	register_setting('woocommerce-bnpmercanetcw', 'woocommerce_bnpmercanetcw_test_secret_key');
	
	add_settings_field('woocommerce_bnpmercanetcw_test_secret_key', __("Test Secret Key", 'woocommerce_bnpmercanetcw'), 'woocommerce_bnpmercanetcw_option_callback_test_secret_key', 'woocommerce-bnpmercanetcw', 'woocommerce_bnpmercanetcw');
	register_setting('woocommerce-bnpmercanetcw', 'woocommerce_bnpmercanetcw_test_secret_key_version');
	
	add_settings_field('woocommerce_bnpmercanetcw_test_secret_key_version', __("Test Secret Key Version", 'woocommerce_bnpmercanetcw'), 'woocommerce_bnpmercanetcw_option_callback_test_secret_key_version', 'woocommerce-bnpmercanetcw', 'woocommerce_bnpmercanetcw');
	register_setting('woocommerce-bnpmercanetcw', 'woocommerce_bnpmercanetcw_simulation_merchant_id');
	
	add_settings_field('woocommerce_bnpmercanetcw_simulation_merchant_id', __("Simulation Merchant ID", 'woocommerce_bnpmercanetcw'), 'woocommerce_bnpmercanetcw_option_callback_simulation_merchant_id', 'woocommerce-bnpmercanetcw', 'woocommerce_bnpmercanetcw');
	register_setting('woocommerce-bnpmercanetcw', 'woocommerce_bnpmercanetcw_simulation_secret_key');
	
	add_settings_field('woocommerce_bnpmercanetcw_simulation_secret_key', __("Simulation Secret Key", 'woocommerce_bnpmercanetcw'), 'woocommerce_bnpmercanetcw_option_callback_simulation_secret_key', 'woocommerce-bnpmercanetcw', 'woocommerce_bnpmercanetcw');
	register_setting('woocommerce-bnpmercanetcw', 'woocommerce_bnpmercanetcw_simulation_secret_key_version');
	
	add_settings_field('woocommerce_bnpmercanetcw_simulation_secret_key_version', __("Simulation Secret Key Version", 'woocommerce_bnpmercanetcw'), 'woocommerce_bnpmercanetcw_option_callback_simulation_secret_key_version', 'woocommerce-bnpmercanetcw', 'woocommerce_bnpmercanetcw');
	register_setting('woocommerce-bnpmercanetcw', 'woocommerce_bnpmercanetcw_intermediate_service_provider_id');
	
	add_settings_field('woocommerce_bnpmercanetcw_intermediate_service_provider_id', __("Intermediate Service Provider Id", 'woocommerce_bnpmercanetcw'), 'woocommerce_bnpmercanetcw_option_callback_intermediate_service_provider_id', 'woocommerce-bnpmercanetcw', 'woocommerce_bnpmercanetcw');
	register_setting('woocommerce-bnpmercanetcw', 'woocommerce_bnpmercanetcw_transaction_reference_schema');
	
	add_settings_field('woocommerce_bnpmercanetcw_transaction_reference_schema', __("Transaction Reference Prefix", 'woocommerce_bnpmercanetcw'), 'woocommerce_bnpmercanetcw_option_callback_transaction_reference_schema', 'woocommerce-bnpmercanetcw', 'woocommerce_bnpmercanetcw');
	register_setting('woocommerce-bnpmercanetcw', 'woocommerce_bnpmercanetcw_template_name');
	
	add_settings_field('woocommerce_bnpmercanetcw_template_name', __("Template Name", 'woocommerce_bnpmercanetcw'), 'woocommerce_bnpmercanetcw_option_callback_template_name', 'woocommerce-bnpmercanetcw', 'woocommerce_bnpmercanetcw');
	register_setting('woocommerce-bnpmercanetcw', 'woocommerce_bnpmercanetcw_review_input_form');
	
	add_settings_field('woocommerce_bnpmercanetcw_review_input_form', __("Review Input Form", 'woocommerce_bnpmercanetcw'), 'woocommerce_bnpmercanetcw_option_callback_review_input_form', 'woocommerce-bnpmercanetcw', 'woocommerce_bnpmercanetcw');
	register_setting('woocommerce-bnpmercanetcw', 'woocommerce_bnpmercanetcw_order_identifier');
	
	add_settings_field('woocommerce_bnpmercanetcw_order_identifier', __("Order Identifier", 'woocommerce_bnpmercanetcw'), 'woocommerce_bnpmercanetcw_option_callback_order_identifier', 'woocommerce-bnpmercanetcw', 'woocommerce_bnpmercanetcw');
	register_setting('woocommerce-bnpmercanetcw', 'woocommerce_bnpmercanetcw_log_level');
	
	add_settings_field('woocommerce_bnpmercanetcw_log_level', __("Log Level", 'woocommerce_bnpmercanetcw'), 'woocommerce_bnpmercanetcw_option_callback_log_level', 'woocommerce-bnpmercanetcw', 'woocommerce_bnpmercanetcw');
	
}
add_action('admin_init', 'woocommerce_bnpmercanetcw_admin_init');

function woocommerce_bnpmercanetcw_section_callback(){}



function woocommerce_bnpmercanetcw_option_callback_operation_mode() {
	echo '<select name="woocommerce_bnpmercanetcw_operation_mode">';
		echo '<option value="live"';
		 if (get_option('woocommerce_bnpmercanetcw_operation_mode', "simulation") == "live"){
			echo ' selected="selected" ';
		}
	echo '>' . __("Live Mode", 'woocommerce_bnpmercanetcw'). '</option>';
	echo '<option value="test"';
		 if (get_option('woocommerce_bnpmercanetcw_operation_mode', "simulation") == "test"){
			echo ' selected="selected" ';
		}
	echo '>' . __("Test Mode", 'woocommerce_bnpmercanetcw'). '</option>';
	echo '<option value="simulation"';
		 if (get_option('woocommerce_bnpmercanetcw_operation_mode', "simulation") == "simulation"){
			echo ' selected="selected" ';
		}
	echo '>' . __("Simulation Mode", 'woocommerce_bnpmercanetcw'). '</option>';
	echo '</select>';
	echo '<br />';
	echo __("In simulation mode the general merchant ID on the simulation plattform is used. In simulation mode the test cards must be used. After you received the merchant ID you should switch to live mode.", 'woocommerce_bnpmercanetcw');
}

function woocommerce_bnpmercanetcw_option_callback_live_merchant_id() {
	echo '<input type="text" name="woocommerce_bnpmercanetcw_live_merchant_id" value="' . htmlspecialchars(get_option('woocommerce_bnpmercanetcw_live_merchant_id', ''),ENT_QUOTES) . '" />';
	
	echo '<br />';
	echo __("Enter here the live merchant ID. This merchant ID is used only when the live operation mode is used.", 'woocommerce_bnpmercanetcw');
}

function woocommerce_bnpmercanetcw_option_callback_live_secret_key() {
	echo '<input type="password" name="woocommerce_bnpmercanetcw_live_secret_key" value="' . htmlspecialchars(get_option('woocommerce_bnpmercanetcw_live_secret_key', ''),ENT_QUOTES) . '" />';
	
	echo '<br />';
	echo __("Enter here the live secret key. You can get the secret key from the extranet. This secret key is used only when the live operation mode is used. The secret key is used to sign the request done to BNP Paribas Mercanet.", 'woocommerce_bnpmercanetcw');
}

function woocommerce_bnpmercanetcw_option_callback_live_secret_key_version() {
	echo '<input type="text" name="woocommerce_bnpmercanetcw_live_secret_key_version" value="' . htmlspecialchars(get_option('woocommerce_bnpmercanetcw_live_secret_key_version', '1'),ENT_QUOTES) . '" />';
	
	echo '<br />';
	echo __("Enter here the live secret key version. You can get the secret key version from the extranet. This secret key is used only when the live operation mode is used.", 'woocommerce_bnpmercanetcw');
}

function woocommerce_bnpmercanetcw_option_callback_test_merchant_id() {
	echo '<input type="text" name="woocommerce_bnpmercanetcw_test_merchant_id" value="' . htmlspecialchars(get_option('woocommerce_bnpmercanetcw_test_merchant_id', ''),ENT_QUOTES) . '" />';
	
	echo '<br />';
	echo __("Enter here the test merchant ID. This merchant ID is used only when the test operation mode is used.", 'woocommerce_bnpmercanetcw');
}

function woocommerce_bnpmercanetcw_option_callback_test_secret_key() {
	echo '<input type="password" name="woocommerce_bnpmercanetcw_test_secret_key" value="' . htmlspecialchars(get_option('woocommerce_bnpmercanetcw_test_secret_key', ''),ENT_QUOTES) . '" />';
	
	echo '<br />';
	echo __("Enter here the test secret key. You can get the secret key from the extranet. This secret key is used only when the test operation mode is used. The secret key is used to sign the request done to BNP Paribas Mercanet.", 'woocommerce_bnpmercanetcw');
}

function woocommerce_bnpmercanetcw_option_callback_test_secret_key_version() {
	echo '<input type="text" name="woocommerce_bnpmercanetcw_test_secret_key_version" value="' . htmlspecialchars(get_option('woocommerce_bnpmercanetcw_test_secret_key_version', '1'),ENT_QUOTES) . '" />';
	
	echo '<br />';
	echo __("Enter here the test secret key version. You can get the secret key version from the extranet. This secret key is used only when the test operation mode is used.", 'woocommerce_bnpmercanetcw');
}

function woocommerce_bnpmercanetcw_option_callback_simulation_merchant_id() {
	echo '<input type="text" name="woocommerce_bnpmercanetcw_simulation_merchant_id" value="' . htmlspecialchars(get_option('woocommerce_bnpmercanetcw_simulation_merchant_id', ''),ENT_QUOTES) . '" />';
	
	echo '<br />';
	echo __("Enter here the simulation merchant ID. This merchant ID is used only when the simulation operation mode is used.", 'woocommerce_bnpmercanetcw');
}

function woocommerce_bnpmercanetcw_option_callback_simulation_secret_key() {
	echo '<input type="password" name="woocommerce_bnpmercanetcw_simulation_secret_key" value="' . htmlspecialchars(get_option('woocommerce_bnpmercanetcw_simulation_secret_key', ''),ENT_QUOTES) . '" />';
	
	echo '<br />';
	echo __("Enter here the simulation secret key. You can get the secret key from the extranet. This secret key is used only when the simulation operation mode is used. The secret key is used to sign the request done to BNP Paribas Mercanet.", 'woocommerce_bnpmercanetcw');
}

function woocommerce_bnpmercanetcw_option_callback_simulation_secret_key_version() {
	echo '<input type="text" name="woocommerce_bnpmercanetcw_simulation_secret_key_version" value="' . htmlspecialchars(get_option('woocommerce_bnpmercanetcw_simulation_secret_key_version', '1'),ENT_QUOTES) . '" />';
	
	echo '<br />';
	echo __("Enter here the simulation secret key version. You can get the secret key version from the extranet. This secret key is used only when the simulation operation mode is used.", 'woocommerce_bnpmercanetcw');
}

function woocommerce_bnpmercanetcw_option_callback_intermediate_service_provider_id() {
	echo '<input type="text" name="woocommerce_bnpmercanetcw_intermediate_service_provider_id" value="' . htmlspecialchars(get_option('woocommerce_bnpmercanetcw_intermediate_service_provider_id', ''),ENT_QUOTES) . '" />';
	
	echo '<br />';
	echo __("You may operate multiple stores using the same merchant id and secret key from BNP Paribas Mercanet. For this they will supply with you different Intermediate Service Provider Ids which are used to differentiate between them.", 'woocommerce_bnpmercanetcw');
}

function woocommerce_bnpmercanetcw_option_callback_transaction_reference_schema() {
	echo '<input type="text" name="woocommerce_bnpmercanetcw_transaction_reference_schema" value="' . htmlspecialchars(get_option('woocommerce_bnpmercanetcw_transaction_reference_schema', '{id}'),ENT_QUOTES) . '" />';
	
	echo '<br />';
	echo __("Here you can insert an transaction reference prefix. The prefix allows you to change the order number that is transmitted to BNP Paribas Mercanet. The prefix must contain the tag {id}. It will then be replaced by the order number (e.g. name_{id}).", 'woocommerce_bnpmercanetcw');
}

function woocommerce_bnpmercanetcw_option_callback_template_name() {
	echo '<input type="text" name="woocommerce_bnpmercanetcw_template_name" value="' . htmlspecialchars(get_option('woocommerce_bnpmercanetcw_template_name', ''),ENT_QUOTES) . '" />';
	
	echo '<br />';
	echo __("If you created a special template at BNP Paribas Mercanet. It will then be used for the payment page.", 'woocommerce_bnpmercanetcw');
}

function woocommerce_bnpmercanetcw_option_callback_review_input_form() {
	echo '<select name="woocommerce_bnpmercanetcw_review_input_form">';
		echo '<option value="active"';
		 if (get_option('woocommerce_bnpmercanetcw_review_input_form', "active") == "active"){
			echo ' selected="selected" ';
		}
	echo '>' . __("Activate input form in review pane.", 'woocommerce_bnpmercanetcw'). '</option>';
	echo '<option value="deactivate"';
		 if (get_option('woocommerce_bnpmercanetcw_review_input_form', "active") == "deactivate"){
			echo ' selected="selected" ';
		}
	echo '>' . __("Deactivate input form in review pane.", 'woocommerce_bnpmercanetcw'). '</option>';
	echo '</select>';
	echo '<br />';
	echo __("Should the input form for credit card data rendered in the review pane? To work the user must have JavaScript activated. In case the browser does not support JavaScript a fallback is provided. This feature is not supported by all payment methods.", 'woocommerce_bnpmercanetcw');
}

function woocommerce_bnpmercanetcw_option_callback_order_identifier() {
	echo '<select name="woocommerce_bnpmercanetcw_order_identifier">';
		echo '<option value="postid"';
		 if (get_option('woocommerce_bnpmercanetcw_order_identifier', "ordernumber") == "postid"){
			echo ' selected="selected" ';
		}
	echo '>' . __("Post ID of the order", 'woocommerce_bnpmercanetcw'). '</option>';
	echo '<option value="ordernumber"';
		 if (get_option('woocommerce_bnpmercanetcw_order_identifier', "ordernumber") == "ordernumber"){
			echo ' selected="selected" ';
		}
	echo '>' . __("Order number", 'woocommerce_bnpmercanetcw'). '</option>';
	echo '</select>';
	echo '<br />';
	echo __("Set which identifier should be sent to the payment service provider. If a plugin modifies the order number and can not guarantee it's uniqueness, select Post Id.", 'woocommerce_bnpmercanetcw');
}

function woocommerce_bnpmercanetcw_option_callback_log_level() {
	echo '<select name="woocommerce_bnpmercanetcw_log_level">';
		echo '<option value="error"';
		 if (get_option('woocommerce_bnpmercanetcw_log_level', "error") == "error"){
			echo ' selected="selected" ';
		}
	echo '>' . __("Error", 'woocommerce_bnpmercanetcw'). '</option>';
	echo '<option value="info"';
		 if (get_option('woocommerce_bnpmercanetcw_log_level', "error") == "info"){
			echo ' selected="selected" ';
		}
	echo '>' . __("Info", 'woocommerce_bnpmercanetcw'). '</option>';
	echo '<option value="debug"';
		 if (get_option('woocommerce_bnpmercanetcw_log_level', "error") == "debug"){
			echo ' selected="selected" ';
		}
	echo '>' . __("Debug", 'woocommerce_bnpmercanetcw'). '</option>';
	echo '</select>';
	echo '<br />';
	echo __("Messages of this or a higher level will be logged.", 'woocommerce_bnpmercanetcw');
}

