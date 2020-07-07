<?php

/**
 *  * You are allowed to use this API in your web application.
 *
 * Copyright (C) 2018 by customweb GmbH
 *
 * This program is licenced under the customweb software licence. With the
 * purchase or the installation of the software in your application you
 * accept the licence agreement. The allowed usage is outlined in the
 * customweb software licence which can be found under
 * http://www.sellxed.com/en/software-license-agreement
 *
 * Any modification or distribution is strictly forbidden. The license
 * grants you the installation in one application. For multiuse you will need
 * to purchase further licences at http://www.sellxed.com/shop.
 *
 * See the customweb software licence agreement for more details.
 *
 */
require_once 'Customweb/Core/Stream/Input/File.php';
require_once 'Customweb/Payment/Authorization/Iframe/IAdapter.php';
require_once 'BNPMercanetCw/Entity/Transaction.php';
require_once 'Customweb/Payment/Authorization/PaymentPage/IAdapter.php';
require_once 'BNPMercanetCw/TransactionContext.php';
require_once 'Customweb/Util/Html.php';
require_once 'Customweb/Payment/Authorization/Recurring/IAdapter.php';
require_once 'BNPMercanetCw/RecurringOrderContext.php';
require_once 'Customweb/Core/Http/ContextRequest.php';
require_once 'Customweb/Payment/Authorization/Ajax/IAdapter.php';
require_once 'BNPMercanetCw/OrderContext.php';
require_once 'Customweb/Payment/Authorization/Hidden/IAdapter.php';
require_once 'Customweb/Payment/Authorization/Widget/IAdapter.php';
require_once 'BNPMercanetCw/Util.php';
require_once 'Customweb/Payment/Authorization/Server/IAdapter.php';
require_once 'BNPMercanetCw/CartOrderContext.php';
require_once 'Customweb/Form/Renderer.php';
require_once 'BNPMercanetCw/PaymentMethodWrapper.php';
require_once 'BNPMercanetCw/RecurringTransactionContext.php';
require_once 'Customweb/Payment/Authorization/IPaymentMethod.php';
require_once 'BNPMercanetCw/ConfigurationAdapter.php';


/**
 *        			 				    			
 * This class handlers the main payment interaction with the
 * BNPMercanetCw server.
 */
class BNPMercanetCw_PaymentMethod extends WC_Payment_Gateway implements
	Customweb_Payment_Authorization_IPaymentMethod {
	
	public $class_name;
	public $id;
	public $title;
	public $chosen;
	public $has_fields = FALSE;
	public $countries;
	public $availability;
	public $enabled = 'no';
	public $icon;
	public $description;

	protected function getMethodSettings(){
		return array();
	}

	public function __construct(){
		$this->class_name = substr(get_class($this), 0, 39);
		
		$this->id = $this->class_name;
		$this->method_title = $this->admin_title;
		
		// Load the form fields.
		$this->form_fields = $this->createMethodFormFields();
		
		// Load the settings.
		$this->init_settings();
			
		$title = $this->getPaymentMethodConfigurationValue('title');
		if (!empty($title)) {
			$this->title = $title;
		}
		
		$this->description = $this->getPaymentMethodConfigurationValue('description');

		add_action('woocommerce_update_options_payment_gateways_' . $this->id, array(
			$this,
			'process_admin_options' 
		));
		
		
		if ($this->getPaymentMethodConfigurationValue('enabled') == 'yes') {
			try{
				$adapter = BNPMercanetCw_Util::getAuthorizationAdapter(
						Customweb_Payment_Authorization_Recurring_IAdapter::AUTHORIZATION_METHOD_NAME);
				if ($adapter->isPaymentMethodSupportingRecurring($this)) {
					$this->supports = array(
						'subscriptions',
						'products',
						'subscription_cancellation',
						'subscription_reactivation',
						'subscription_suspension',
						'subscription_amount_changes',
						'subscription_date_changes',
						'multiple_subscriptions',
						'product_variation' ,
					);
				}
			}catch(Customweb_Payment_Authorization_Method_PaymentMethodResolutionException $e){
				
			}
		}
		add_action('woocommerce_scheduled_subscription_payment_' . $this->id, array(
			$this,
			'scheduledSubscriptionPayment' 
		), 10, 2);
		
	}
	
	public function getPaymentMethodName(){
		return $this->machineName;
	}
	
	public function getPaymentMethodDisplayName(){
		return $this->title;
	}
	
	public function getBackendDescription(){
		return __('The configuration values for BNP Paribas Mercanet can be set under:', 'woocommerce_bnpmercanetcw') .
		' <a href="options-general.php?page=woocommerce-bnpmercanetcw">' .
		__('BNP Paribas Mercanet Settings', 'woocommerce_bnpmercanetcw') . '</a>';
	}
	
	public function isAliasManagerActive(){
		$result = false;
		
		$result = ($this->getPaymentMethodConfigurationValue('alias_manager') == 'active');
		
		return $result;
	}
	
	public function getCurrentSelectedAlias(){
		$aliasTransactionId = null;
		
		if (isset($_REQUEST[$this->getAliasHTMLFieldName()])) {
			$aliasTransactionId = $_REQUEST[$this->getAliasHTMLFieldName()];
		}
		else if (isset($_POST['post_data'])) {
			$data = array();
			parse_str($_POST['post_data'], $data);
			if (isset($data[$this->getAliasHTMLFieldName()])) {
				$aliasTransactionId = $data[$this->getAliasHTMLFieldName()];
			}
		}
		
		return $aliasTransactionId;
	}
	
	protected function showError($errorMessage){
		echo '<div class="woocommerce-error">' . $errorMessage . '</div>';
		die();
	}

	public function getPaymentMethodConfigurationValue($key, $languageCode = null){
		$settingsArray = array_merge($this->createMethodFormFields(), $this->getMethodSettings());
		if (!isset($settingsArray[$key])) {
			return null;
		}
		if (isset($settingsArray[$key]['cwType']) && $settingsArray[$key]['cwType'] == 'file') {
			$value = $this->settings[$key];
			if (isset($value['path']) && file_exists($value['path'])) {
				return new Customweb_Core_Stream_Input_File($value['path']);
			}
			else {
				$resolver = BNPMercanetCw_Util::getAssetResolver();
				if (!empty($value)) {
					return $resolver->resolveAssetStream($value);
				}
			}
		}
		elseif (isset($settingsArray[$key]['cwType']) && $settingsArray[$key]['cwType'] == 'multiselect') {
			if (isset($this->settings[$key])) {
				$value = $this->settings[$key];
				if (empty($value)) {
					return array();
				}
				return $value;
			}
			if(isset($settingsArray[$key]['default'])){
				return $settingsArray[$key]['default'];
			}
			return array();
		}
		elseif (isset($this->settings[$key])) {
			return $this->settings[$key];
		}
		else {
			if(isset($settingsArray[$key]['default'])){
				return $settingsArray[$key]['default'];
			}			
			return null;
		}
	}

	public function existsPaymentMethodConfigurationValue($key, $languageCode = null){
		$settingsArray = array_merge($this->createMethodFormFields(), $this->getMethodSettings());
		if (isset($settingsArray[$key])) {
			
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * Generate the HTML output for the settings form.
	 */
	public function admin_options(){
		$output = '<h3>' . __($this->admin_title, 'woocommerce_bnpmercanetcw') . '</h3>';
		$output .= '<p>' . $this->getBackendDescription() . '</p>';
		
		$output .= '<table class="form-table">';
		
		echo $output;
		
		$this->generate_settings_html();
		
		echo '</table>';
	}
	
	/**
	 * This method generates a HTML form for each payment method.
	 */
	public function createMethodFormFields(){
		return array(
			'enabled' => array(
				'title' => __('Enable/Disable', 'woocommerce_bnpmercanetcw'),
				'type' => 'checkbox',
				'label' => sprintf(__('Enable %s', 'woocommerce_bnpmercanetcw'), $this->admin_title),
				'default' => 'no'
			),
			'title' => array(
				'title' => __('Title', 'woocommerce_bnpmercanetcw'),
				'type' => 'text',
				'description' => __('This controls the title which the user sees during checkout.', 'woocommerce_bnpmercanetcw'),
				'default' => __($this->title, 'woocommerce_bnpmercanetcw')
			),
			'description' => array(
				'title' => __('Description', 'woocommerce_bnpmercanetcw'),
				'type' => 'textarea',
				'description' => __('This controls the description which the user sees during checkout.', 'woocommerce_bnpmercanetcw'),
				'default' => sprintf(
						__("Pay with %s over the interface of BNP Paribas Mercanet.", 'woocommerce_bnpmercanetcw'),
						$this->title)
			),
			'min_total' => array(
				'title' => __('Minimal Order Total', 'woocommerce_bnpmercanetcw'),
				'type' => 'text',
				'description' => __(
						'Set here the minimal order total for which this payment method is available. If it is set to zero, it is always available.',
						'woocommerce_bnpmercanetcw'),
				'default' => 0
			),
			'max_total' => array(
				'title' => __('Maximal Order Total', 'woocommerce_bnpmercanetcw'),
				'type' => 'text',
				'description' => __(
						'Set here the maximal order total for which this payment method is available. If it is set to zero, it is always available.',
						'woocommerce_bnpmercanetcw'),
				'default' => 0
			)
		);
	}
	

	function generate_select_html($key, $data){
		// We need to override this method, because we need to get
		// the order status, after we defined the form fields. The
		// terms are not accessible before.
		if (isset($data['is_order_status']) && $data['is_order_status'] == true) {
			if (isset($data['options']) && is_array($data['options'])) {
				$data['options'] = $this->getOrderStatusOptions($data['options']);
			}
			else {
				$data['options'] = $this->getOrderStatusOptions();
			}
		}
		return parent::generate_select_html($key, $data);
	}
	
	
	public function scheduledSubscriptionPayment($amountToCharge, $order){
		$GLOBALS['bnpmercanetcw_recurring_process_failure'] = null;
		$orderId = $order->get_id();
		if(BNPMercanetCw_Util::getAuthorizedTransactionByPostId($orderId) != null){
			return;	
		}
		try {			
			$adapter = BNPMercanetCw_Util::getAuthorizationAdapter(
					Customweb_Payment_Authorization_Recurring_IAdapter::AUTHORIZATION_METHOD_NAME);
			
			$orderContext = new BNPMercanetCw_RecurringOrderContext($order, new BNPMercanetCw_PaymentMethodWrapper($this), 
					$amountToCharge);
			update_post_meta($orderId, '_bnpmercanetcw_pending_state', 'yes' );

			$dbTransaction = $this->newDatabaseTransaction($orderContext);
			$transactionContext = new BNPMercanetCw_RecurringTransactionContext($dbTransaction, $orderContext);
			$transaction = $adapter->createTransaction($transactionContext);
			$dbTransaction->setTransactionObject($transaction);
			BNPMercanetCw_Util::getEntityManager()->persist($dbTransaction);
		}
		catch (Exception $e) {
			$errorMessage = __('Subscription Payment Failed with error:', 'woocommerce_bnpmercanetcw') . $e->getMessage();
			$GLOBALS['bnpmercanetcw_recurring_process_failure'] = $errorMessage;
			$subscriptions = wcs_get_subscriptions_for_order($orderId, array(
				'order_type' => array(
					'parent',
					'renewal' 
				) 
			));
			foreach ($subscriptions as $subscription) {
				if (wcs_is_subscription($subscription->id)) {
					$subscription->payment_failed();
				}
			}
			return;
		}
		try {
			$adapter->process($transaction);
			if (!$transaction->isAuthorized()) {
				$message = current($transaction->getErrorMessages());
				throw new Exception($message);
			}
			BNPMercanetCw_Util::getTransactionHandler()->persistTransactionObject($transaction);
		}
		catch (Exception $e) {
			$errorMessage = __('Subscription Payment Failed with error:', 'woocommerce_bnpmercanetcw') . $e->getMessage();
			$GLOBALS['bnpmercanetcw_recurring_process_failure'] = $errorMessage;
			$subscriptions = wcs_get_subscriptions_for_order($orderId, 
					array(
						'order_type' => array(
							'parent',
							'renewal' 
						) 
					));
			foreach ($subscriptions as $subscription) {
				if (wcs_is_subscription($subscription->id)) {
					$subscription->payment_failed();
				}
			}
			BNPMercanetCw_Util::getTransactionHandler()->persistTransactionObject($transaction);
		}
	}
	
	
	public function process_admin_options(){
		$GLOBALS['woocommerce_bnpmercanetcw_isProcesssing'];
		if (!empty($GLOBALS['woocommerce_bnpmercanetcw_isProcesssing'])){
			return true;
		}
		$GLOBALS['woocommerce_bnpmercanetcw_isProcesssing'] = true;
		$result = parent::process_admin_options();
		if($result){
			//So WPML adds the title and description to the string translations
			apply_filters( 'woocommerce_settings_api_sanitized_fields_' . strtolower( str_replace( 'WC_Gateway_', '', $this->id ) ), $this->settings );
		}
		return $result;
	}

	public function validate_file_field($key){
		$value = $this->get_option($key);
		$settingsArray = $this->getMethodSettings();
		$setting = $settingsArray[$key];
		
		$filename = get_class($this) . '_' . $key;
		$fieldName = 'woocommerce_' . get_class($this) . '_' . $key;
		$parsedBody = Customweb_Core_Http_ContextRequest::getInstance()->getParsedBody();
		
		if (isset($parsedBody[$fieldName . '_reset']) && $parsedBody[$fieldName . '_reset'] == 'reset') {
			return $setting['default'];
		}
		
		if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] != 0) {
			return $value;
		}
		$upload_dir = wp_upload_dir();
		$name = basename($_FILES[$fieldName]['name']);
		
		$fileExtension = pathinfo($name, PATHINFO_EXTENSION);
		if (!file_exists($upload_dir['basedir'] . '/woocommerce_bnpmercanetcw')) {
			$oldmask = umask(0);
			mkdir($upload_dir['basedir'] . '/woocommerce_bnpmercanetcw', 0777, true);
			umask($oldmask);
		}
		$allowedFileExtensions = $setting['allowedFileExtensions'];
		
		if (!empty($allowedFileExtensions) && !in_array($fileExtension, $allowedFileExtensions)) {
			woocommerce_bnpmercanetcw_admin_show_message(
					'Only the following file extensions are allowed for setting "' . $setting['title'] . '": ' . implode(', ', $allowedFileExtensions), 
					'error');
			return $value;
		}
		$targetPath = $upload_dir['basedir'] . '/woocommerce_bnpmercanetcw/' . $filename . '.' . $fileExtension;
		$rs = move_uploaded_file($_FILES[$fieldName]['tmp_name'], $targetPath);
		if ($rs) {
			chmod($targetPath, 0777);
			return array(
				'name' => $name,
				'path' => $targetPath 
			);
		}
		else {
			woocommerce_bnpmercanetcw_admin_show_message('Unable to upload file for setting "' . $setting['title'] . '".', 'error');
			return $value;
		}
	}

	public function generate_file_html($key, $data){
		$field = $this->plugin_id . $this->id . '_' . $key;
		$defaults = array(
			'title' => '',
			'disabled' => false,
			'class' => '',
			'css' => '',
			'placeholder' => '',
			'type' => 'file',
			'desc_tip' => false,
			'description' => '',
			'custom_attributes' => array() 
		);
		
		$data = wp_parse_args($data, $defaults);
		
		ob_start();
		?>
<tr valign="top">
	<th scope="row" class="titledesc"><label
		for="<?php echo esc_attr( $field ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
					<?php echo $this->get_tooltip_html( $data ); ?>
				</th>
	<td class="forminp">
					<?php
		
		$value = $this->get_option($key);
		if (isset($value['name'])) {
			$filename = $value['name'];
		}
		else {
			
			$filename = $value;
		}
		echo __('Current File: ', 'woocommerce_bnpmercanetcw') . esc_attr($filename);
		?><br />
		<fieldset>
			<legend class="screen-reader-text">
				<span><?php echo wp_kses_post( $data['title'] ); ?></span>
			</legend>
			<input class="input-text regular-input <?php echo esc_attr( $data['class'] ); ?>" type="<?php echo esc_attr( $data['type'] ); ?>" name="<?php echo esc_attr( $field ); ?>" id="<?php echo esc_attr( $field ); ?>" style="<?php echo esc_attr( $data['css'] ); ?>" placeholder="<?php echo esc_attr( $data['placeholder'] ); ?>" <?php disabled( $data['disabled'], true ); ?> <?php echo $this->get_custom_attribute_html( $data ); ?> />
		</fieldset> <input type="checkbox"
		name="<?php echo esc_attr( $field.'_reset' ); ?>" value="reset" /><?php echo __('Reset', 'woocommerce_bnpmercanetcw'); ?><br />
	</td>
</tr>
<?php
		return ob_get_clean();
	}

	protected function getOrderStatusOptions($statuses = array()){
		$orderStatuses = wc_get_order_statuses();
		foreach ($statuses as $k => $value) {
			$orderStatuses[$k] = __($value, 'woocommerce_bnpmercanetcw');
		}
		return $orderStatuses;
	}

	protected function getCompatibilityFormFields(){
		require_once (ABSPATH . 'wp-admin/includes/plugin.php');
		$extra = '';
		if (is_plugin_active('woocommerce-german-market/WooCommerce-German-Market.php')) {
			$extra .= '<div class="bnpmercanetcw-requires-second-run"></div>';
		}
		return $extra;
	}
	
	
	public function needs_setup() {
		return true;
	}
	
	public function processShopPayment($orderPostId, $aliasTransactionId = null, $failedTransactionId = null, $failedValidate = null){
		require_once 'Customweb/Licensing/BNPMercanetCw/License.php';
		$arguments = array(
			'orderPostId' => $orderPostId,
 			'failedTransactionId' => $failedTransactionId,
 			'aliasTransactionId' => $aliasTransactionId,
 			'failedValidate' => $failedValidate,
 		);
		return Customweb_Licensing_BNPMercanetCw_License::run('s0dohcgaopaothlh', $this, $arguments);
	}

	final public function call_8sl9o2oe0u4vk1si() {
		$arguments = func_get_args();
		$method = $arguments[0];
		$call = $arguments[1];
		$parameters = array_slice($arguments, 2);
		if ($call == 's') {
			return call_user_func_array(array(get_class($this), $method), $parameters);
		}
		else {
			return call_user_func_array(array($this, $method), $parameters);
		}
		
		
	}
	
	
	public function processTransaction($orderPostId, $aliasTransactionId = null){
		require_once 'Customweb/Licensing/BNPMercanetCw/License.php';
		$arguments = array(
			'orderPostId' => $orderPostId,
 			'aliasTransactionId' => $aliasTransactionId,
 		);
		return Customweb_Licensing_BNPMercanetCw_License::run('sl0ri8445rp0j733', $this, $arguments);
	}

	final public function call_kf2tdhiis3n1btu7() {
		$arguments = func_get_args();
		$method = $arguments[0];
		$call = $arguments[1];
		$parameters = array_slice($arguments, 2);
		if ($call == 's') {
			return call_user_func_array(array(get_class($this), $method), $parameters);
		}
		else {
			return call_user_func_array(array($this, $method), $parameters);
		}
		
		
	}

	/**
	 * This method is called when the payment is submitted.
	 *
	 * @param int $order_id
	 */
	public function process_payment($order_id){
		global $woocommerce;
		
		$order = new WC_Order($order_id);
		
		// Bugfix to prevent the deletion of the cart, when the user goes back to the shop.
		
		if (isset($woocommerce)) {
			unset($woocommerce->session->order_awaiting_payment);
		}
		
		$order->add_order_note(
				__('The customer is now in the payment process of BNP Paribas Mercanet.', 'woocommerce_bnpmercanetcw'));
		update_post_meta($order_id, '_bnpmercanetcw_pending_state', 'yes' );
		
		$aliasTransactionId = $this->getCurrentSelectedAlias();
		if (is_ajax()) {
			try {
				$result = $this->processShopPayment($order_id, $aliasTransactionId);
				if (is_array($result)) {
					wp_send_json($result);
					die();
				}
				else {
					wp_send_json(
							array(
								'result' => 'success',
								'data' => $result .
										 "<script type=\"text/javascript\"> var backToCheckoutCw = jQuery('#bnpmercanetcw-back-to-checkout'); jQuery('form.checkout').replaceWith(jQuery('#bnpmercanetcw-payment-container')); jQuery('#bnpmercanetcw-payment-container').after(backToCheckoutCw); jQuery('.woocommerce-info').remove(); jQuery('.cw-external-checkouts').remove(); jQuery('html, body').animate({ scrollTop: (jQuery('#bnpmercanetcw-payment-container').offset().top-150) }, '1000');</script>" 
							));
					die();
				}
			}
			catch (Exception $e) {
				$this->showError($e->getMessage());
			}
		}
		else {
			wp_redirect(
					BNPMercanetCw_Util::getPluginUrl("payment", 
							array(
								'cwoid' => $order_id,
								'cwot' => BNPMercanetCw_Util::computeOrderValidationHash($order_id),
								'cwpmc' => get_class($this),
								'cwalias' => $aliasTransactionId 
							)));
			die();
		}
	}

	protected function destroyCheckoutId(){
		global $woocommerce;
		$sessionHandler = $woocommerce->session;
		if($sessionHandler != null){
			$sessionHandler->set('BNPMercanetCwCheckoutId', null);
		}
	}
	
	/**
	 *
	 * @return BNPMercanetCw_CartOrderContext
	 */
	protected function getCartOrderContext(){
		if (!isset($_POST['post_data'])) {
			return null;
		}
		$data = array();
		parse_str($_POST['post_data'], $data);
		
		return new BNPMercanetCw_CartOrderContext($data, new BNPMercanetCw_PaymentMethodWrapper($this));
	}
	
	public function payment_fields(){
		parent::payment_fields();
		
		
		if ($this->isAliasManagerActive()) {
			$userId = get_current_user_id();
			$aliases = BNPMercanetCw_Util::getAliasTransactions($userId, $this->getPaymentMethodName());
			
			if (count($aliases) > 0) {
				$selectedAlias = $this->getCurrentSelectedAlias();
				
				echo '<div class="bnpmercanetcw-alias-input-box"><div class="alias-field-description">' .
						__('You can choose a previous used card:', 'woocommerce_bnpmercanetcw') . '</div>';
						echo '<select name="' . $this->getAliasHTMLFieldName() . '">';
						echo '<option value="new"> -' . __('Select card', 'woocommerce_bnpmercanetcw') . '- </option>';
						foreach ($aliases as $aliasTransaction) {
							echo '<option value="' . $aliasTransaction->getTransactionId() . '"';
							if ($selectedAlias == $aliasTransaction->getTransactionId()) {
								echo ' selected="selected" ';
							}
							echo '>' . $aliasTransaction->getAliasForDisplay() . '</option>';
						}
						echo '</select></div>';
			}
			else {
				echo '<div class="bnpmercanetcw-alias-hidden-new"><input type="hidden" name="' . $this->getAliasHTMLFieldName() .
				'" value="new" /></div>';
			}
		}
		
		
		$orderContext = $this->getCartOrderContext();
		if ($orderContext !== null) {
			$aliasTransactionObject = null;
			
			if ($this->isAliasManagerActive()) {
				$aliasTransactionObject = "new";
				$selectedAlias = $this->getCurrentSelectedAlias();
				if ($selectedAlias !== null && $selectedAlias !== 'new') {
					$aliasTransaction = BNPMercanetCw_Util::getTransactionById($selectedAlias);
					if ($aliasTransaction !== null && $aliasTransaction->getCustomerId() == get_current_user_id()) {
						$aliasTransactionObject = $aliasTransaction->getTransactionObject();
					}
				}
			}
			
			
			echo $this->getReviewFormFields($orderContext, $aliasTransactionObject);
		}
	}
	
	
	public function getAliasHTMLFieldName(){
		return 'bnpmercanetcw_alias_' . $this->getPaymentMethodName();
	}
	
	
	public function has_fields(){
		$fields = parent::has_fields();
		
		if ($this->isAliasManagerActive()) {
			$userId = get_current_user_id();
			$aliases = BNPMercanetCw_Util::getAliasTransactions($userId, $this->getPaymentMethodName());
			
			if (count($aliases) > 0) {
				return true;
			}
		}
		
		$orderContext = $this->getCartOrderContext();
		if ($orderContext !== null) {
			$aliasTransactionObject = null;
			
			if ($this->isAliasManagerActive()) {
				$aliasTransactionObject = "new";
				$selectedAlias = $this->getCurrentSelectedAlias();
				if ($selectedAlias !== null && $selectedAlias !== 'new') {
					$aliasTransaction = BNPMercanetCw_Util::getTransactionById($selectedAlias);
					if ($aliasTransaction !== null && $aliasTransaction->getCustomerId() == get_current_user_id()) {
						$aliasTransactionObject = $aliasTransaction->getTransactionObject();
					}
				}
			}
			
			$generated = $this->getReviewFormFields($orderContext, $aliasTransactionObject);
			if (!empty($generated)) {
				return true;
			}
		}
		return $fields;
	}
		
	/**
	 * This method is invoked to check if the payment method is available for checkout.
	 */
	public function is_available(){
		global $woocommerce;
		
		$available = parent::is_available();
		
		if ($available !== true) {
			return false;
		}
		
		if (isset($woocommerce) && $woocommerce->cart != null) {
			if (isset($woocommerce->cart->disableValidationCw) && $woocommerce->cart->disableValidationCw) {
				return true;
			}
			if (!isset($woocommerce->cart->totalCalculatedCw)) {
				$woocommerce->cart->calculate_totals();
			}
			
			$orderTotal = $woocommerce->cart->total;
			if ($orderTotal < $this->getPaymentMethodConfigurationValue('min_total')) {
				return false;
			}
			if ($this->getPaymentMethodConfigurationValue('max_total') > 0 && $this->getPaymentMethodConfigurationValue('max_total') < $orderTotal) {
				return false;
			}
			
			$orderContext = $this->getCartOrderContext();
			if ($orderContext !== null) {
				$paymentContext = BNPMercanetCw_Util::getPaymentCustomerContext($orderContext->getCustomerId());
				
				$result = true;
				try {
					$adapter = BNPMercanetCw_Util::getAuthorizationAdapterByContext($orderContext);
					$adapter->preValidate($orderContext, $paymentContext);
				}
				catch (Exception $e) {
					$result = false;
				}
				BNPMercanetCw_Util::persistPaymentCustomerContext($paymentContext);
				return $result;
			}
		}
		return true;
	}
	
	public function validate(array $formData){
		$orderContext = new BNPMercanetCw_CartOrderContext($formData, new BNPMercanetCw_PaymentMethodWrapper($this));
		$paymentContext = BNPMercanetCw_Util::getPaymentCustomerContext($orderContext->getCustomerId());
		$adapter = BNPMercanetCw_Util::getAuthorizationAdapterByContext($orderContext);
		if($adapter instanceof Customweb_Payment_Authorization_Ajax_IAdapter || $adapter instanceof Customweb_Payment_Authorization_Hidden_IAdapter){
			//Do not validate hidden or ajax
			return;
		}
		// Validate transaction
		$errorMessage = null;
		try {
			if (BNPMercanetCw_ConfigurationAdapter::isReviewFormInputActive() && isset($_REQUEST['bnpmercanetcw-preview-fields'])) {
				$adapter->validate($orderContext, $paymentContext, $formData);
			}
		}
		catch (Exception $e) {
			$errorMessage = $e->getMessage();
		}
		BNPMercanetCw_Util::persistPaymentCustomerContext($paymentContext);
		
		if ($errorMessage !== null) {
			throw new Exception($errorMessage);
		}
	}
	
	protected function getReviewFormFields(Customweb_Payment_Authorization_IOrderContext $orderContext, $aliasTransaction){
		if (BNPMercanetCw_ConfigurationAdapter::isReviewFormInputActive()) {
			$paymentContext = BNPMercanetCw_Util::getPaymentCustomerContext($orderContext->getCustomerId());
			$adapter = BNPMercanetCw_Util::getAuthorizationAdapterByContext($orderContext);
			$fields = array();
			if (method_exists($adapter, 'getVisibleFormFields')) {
				$fields = $adapter->getVisibleFormFields($orderContext, $aliasTransaction, null, $paymentContext);
			}
			BNPMercanetCw_Util::persistPaymentCustomerContext($paymentContext);
			
			$result = '<div class="bnpmercanetcw-preview-fields';
			if (!($adapter instanceof Customweb_Payment_Authorization_Ajax_IAdapter ||
					$adapter instanceof Customweb_Payment_Authorization_Hidden_IAdapter)) {
						$result .= ' bnpmercanetcw-validate';
					}
					$result .= '">';
					
					$result .= $this->getCompatibilityFormFields();
					
					if ($fields !== null && count($fields) > 0) {
						$renderer = new Customweb_Form_Renderer();
						$renderer->setRenderOnLoadJs(false);
						$renderer->setNameSpacePrefix('bnpmercanetcw_' . $orderContext->getPaymentMethod()->getPaymentMethodName());
						$renderer->setCssClassPrefix('bnpmercanetcw-');
						
						$result .= $renderer->renderElements($fields) . '</div>';
					}
					else {
						$result .= '</div>';
					}
					return $result;
		}
		
		return '';
	}
	
	public function getFormActionUrl(BNPMercanetCw_OrderContext $orderContext){
		$adapter = BNPMercanetCw_Util::getAuthorizationAdapterByContext($orderContext);
		$identifiers = array(
			'cwoid' => $orderContext->getOrderPostId(),
			'cwot' => BNPMercanetCw_Util::computeOrderValidationHash($orderContext->getOrderPostId())
		);
		if ($adapter instanceof Customweb_Payment_Authorization_Iframe_IAdapter) {
			return BNPMercanetCw_Util::getPluginUrl('iframe', $identifiers);
		}
		if ($adapter instanceof Customweb_Payment_Authorization_Widget_IAdapter) {
			return BNPMercanetCw_Util::getPluginUrl('widget', $identifiers);
		}
		if ($adapter instanceof Customweb_Payment_Authorization_PaymentPage_IAdapter) {
			return BNPMercanetCw_Util::getPluginUrl('redirection', $identifiers);
		}
		if ($adapter instanceof Customweb_Payment_Authorization_Server_IAdapter) {
			return BNPMercanetCw_Util::getPluginUrl('authorize', $identifiers);
		}
	}
	
	protected function getCheckoutFormVaiables(BNPMercanetCw_OrderContext $orderContext, $aliasTransaction, $failedTransaction){
		$adapter = BNPMercanetCw_Util::getAuthorizationAdapterByContext($orderContext);
		
		$visibleFormFields = array();
		if (method_exists($adapter, 'getVisibleFormFields')) {
			
			$customerContext = BNPMercanetCw_Util::getPaymentCustomerContext($orderContext->getCustomerId());
			$visibleFormFields = $adapter->getVisibleFormFields($orderContext, $aliasTransaction, $failedTransaction, $customerContext);
			BNPMercanetCw_Util::persistPaymentCustomerContext($customerContext);
		}
		
		$html = '';
		if ($visibleFormFields !== null && count($visibleFormFields) > 0) {
			$renderer = new Customweb_Form_Renderer();
			$renderer->setCssClassPrefix('bnpmercanetcw-');
			$html = $renderer->renderElements($visibleFormFields);
		}
		
		if ($adapter instanceof Customweb_Payment_Authorization_Ajax_IAdapter) {
			$dbTransaction = $this->prepare($orderContext, $aliasTransaction, $failedTransaction);
			$ajaxScriptUrl = $adapter->getAjaxFileUrl($dbTransaction->getTransactionObject());
			$callbackFunction = $adapter->getJavaScriptCallbackFunction($dbTransaction->getTransactionObject());
			BNPMercanetCw_Util::getEntityManager()->persist($dbTransaction);
			return array(
				'visible_fields' => $html,
				'template_file' => 'payment_confirmation_ajax',
				'ajaxScriptUrl' => (string) $ajaxScriptUrl,
				'submitCallbackFunction' => $callbackFunction
			);
		}
		
		if ($adapter instanceof Customweb_Payment_Authorization_Hidden_IAdapter) {
			$dbTransaction = $this->prepare($orderContext, $aliasTransaction, $failedTransaction);
			$formActionUrl = $adapter->getFormActionUrl($dbTransaction->getTransactionObject());
			$hiddenFields = Customweb_Util_Html::buildHiddenInputFields($adapter->getHiddenFormFields($dbTransaction->getTransactionObject()));
			BNPMercanetCw_Util::getEntityManager()->persist($dbTransaction);
			return array(
				'form_target_url' => $formActionUrl,
				'hidden_fields' => $hiddenFields,
				'visible_fields' => $html,
				'template_file' => 'payment_confirmation'
			);
		}
		
		return array(
			'form_target_url' => $this->getFormActionUrl($orderContext),
			'visible_fields' => $html,
			'template_file' => 'payment_confirmation'
		);
	}
	
	/**
	 * This function creates a new Transaction
	 *
	 * @param BNPMercanetCw_OrderContext $order
	 * @return BNPMercanetCw_Entity_Transaction
	 */
	public function newDatabaseTransaction(BNPMercanetCw_OrderContext $orderContext){
		$dbTransaction = new BNPMercanetCw_Entity_Transaction();
		$this->destroyCheckoutId();
		$dbTransaction->setPostId($orderContext->getOrderPostId())->setOrderId($orderContext->getOrderNumber())->setCustomerId($orderContext->getCustomerId())->setPaymentClass(get_class($this))->setPaymentMachineName(
				$this->getPaymentMethodName());
		BNPMercanetCw_Util::getEntityManager()->persist($dbTransaction);
		return $dbTransaction;
	}
	
	/**
	 * This function creates a new Transaction and transaction object and persists them in the DB
	 *
	 * @param BNPMercanetCw_OrderContext $orderContext
	 * @param Customweb_Payment_Authorization_ITransactionContext | null $aliasTransaction
	 * @param Customweb_Payment_Authorization_ITransactionContext |null $failedTransaction
	 * @return BNPMercanetCw_Entity_Transaction
	 */
	public function prepare(BNPMercanetCw_OrderContext $orderContext, $aliasTransaction = null, $failedTransaction = null){
		$dbTransaction = $this->newDatabaseTransaction($orderContext);
		$transactionContext = new BNPMercanetCw_TransactionContext($dbTransaction, $orderContext, $aliasTransaction);
		$adapter = BNPMercanetCw_Util::getAuthorizationAdapterByContext($orderContext);
		$transaction = $adapter->createTransaction($transactionContext, $failedTransaction);
		$dbTransaction->setTransactionObject($transaction);
		return BNPMercanetCw_Util::getEntityManager()->persist($dbTransaction);
	}	
}
