<?php
/**
 * * You are allowed to use this API in your web application.
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
require_once 'Customweb/Payment/IConfigurationAdapter.php';


/**
 *
 */
abstract class BNPMercanetCw_AbstractConfigurationAdapter implements Customweb_Payment_IConfigurationAdapter
{
	
	protected $settingsMap=array(
		'operation_mode' => array(
			'id' => 'worldline-sips-operation-mode-setting',
 			'machineName' => 'operation_mode',
 			'type' => 'select',
 			'label' => 'Operation Mode',
 			'description' => 'In simulation mode the general merchant ID on the simulation plattform is used. In simulation mode the test cards must be used. After you received the merchant ID you should switch to live mode.',
 			'defaultValue' => 'simulation',
 			'allowedFileExtensions' => array(
			),
 		),
 		'live_merchant_id' => array(
			'id' => 'worldline-sips-live-merchant-id-setting',
 			'machineName' => 'live_merchant_id',
 			'type' => 'textfield',
 			'label' => 'Live Merchant ID',
 			'description' => 'Enter here the live merchant ID. This merchant ID is used only when the live operation mode is used.',
 			'defaultValue' => '',
 			'allowedFileExtensions' => array(
			),
 		),
 		'live_secret_key' => array(
			'id' => 'worldline-sips-live-secret-key-setting',
 			'machineName' => 'live_secret_key',
 			'type' => 'password',
 			'label' => 'Live Secret Key',
 			'description' => 'Enter here the live secret key. You can get the secret key from the extranet. This secret key is used only when the live operation mode is used. The secret key is used to sign the request done to BNP Paribas Mercanet.',
 			'defaultValue' => '',
 			'allowedFileExtensions' => array(
			),
 		),
 		'live_secret_key_version' => array(
			'id' => 'worldline-sips-live-secret-key-version-setting',
 			'machineName' => 'live_secret_key_version',
 			'type' => 'textfield',
 			'label' => 'Live Secret Key Version',
 			'description' => 'Enter here the live secret key version. You can get the secret key version from the extranet. This secret key is used only when the live operation mode is used.',
 			'defaultValue' => '1',
 			'allowedFileExtensions' => array(
			),
 		),
 		'test_merchant_id' => array(
			'id' => 'worldline-sips-test-merchant-id-setting',
 			'machineName' => 'test_merchant_id',
 			'type' => 'textfield',
 			'label' => 'Test Merchant ID',
 			'description' => 'Enter here the test merchant ID. This merchant ID is used only when the test operation mode is used.',
 			'defaultValue' => '',
 			'allowedFileExtensions' => array(
			),
 		),
 		'test_secret_key' => array(
			'id' => 'worldline-sips-test-secret-key-setting',
 			'machineName' => 'test_secret_key',
 			'type' => 'password',
 			'label' => 'Test Secret Key',
 			'description' => 'Enter here the test secret key. You can get the secret key from the extranet. This secret key is used only when the test operation mode is used. The secret key is used to sign the request done to BNP Paribas Mercanet.',
 			'defaultValue' => '',
 			'allowedFileExtensions' => array(
			),
 		),
 		'test_secret_key_version' => array(
			'id' => 'worldline-sips-test-secret-key-version-setting',
 			'machineName' => 'test_secret_key_version',
 			'type' => 'textfield',
 			'label' => 'Test Secret Key Version',
 			'description' => 'Enter here the test secret key version. You can get the secret key version from the extranet. This secret key is used only when the test operation mode is used.',
 			'defaultValue' => '1',
 			'allowedFileExtensions' => array(
			),
 		),
 		'simulation_merchant_id' => array(
			'id' => 'worldline-sips-simulation-merchant-id-setting',
 			'machineName' => 'simulation_merchant_id',
 			'type' => 'textfield',
 			'label' => 'Simulation Merchant ID',
 			'description' => 'Enter here the simulation merchant ID. This merchant ID is used only when the simulation operation mode is used.',
 			'defaultValue' => '',
 			'allowedFileExtensions' => array(
			),
 		),
 		'simulation_secret_key' => array(
			'id' => 'worldline-sips-simulation-secret-key-setting',
 			'machineName' => 'simulation_secret_key',
 			'type' => 'password',
 			'label' => 'Simulation Secret Key',
 			'description' => 'Enter here the simulation secret key. You can get the secret key from the extranet. This secret key is used only when the simulation operation mode is used. The secret key is used to sign the request done to BNP Paribas Mercanet.',
 			'defaultValue' => '',
 			'allowedFileExtensions' => array(
			),
 		),
 		'simulation_secret_key_version' => array(
			'id' => 'worldline-sips-simulation-secret-key-version-setting',
 			'machineName' => 'simulation_secret_key_version',
 			'type' => 'textfield',
 			'label' => 'Simulation Secret Key Version',
 			'description' => 'Enter here the simulation secret key version. You can get the secret key version from the extranet. This secret key is used only when the simulation operation mode is used.',
 			'defaultValue' => '1',
 			'allowedFileExtensions' => array(
			),
 		),
 		'intermediate_service_provider_id' => array(
			'id' => 'worldine-sips-intermediate-service-provider-id-setting',
 			'machineName' => 'intermediate_service_provider_id',
 			'type' => 'textfield',
 			'label' => 'Intermediate Service Provider Id',
 			'description' => 'You may operate multiple stores using the same merchant id and secret key from BNP Paribas Mercanet. For this they will supply with you different Intermediate Service Provider Ids which are used to differentiate between them.',
 			'defaultValue' => '',
 			'allowedFileExtensions' => array(
			),
 		),
 		'transaction_reference_schema' => array(
			'id' => 'worldline-sips-transaction-reference-schema-setting',
 			'machineName' => 'transaction_reference_schema',
 			'type' => 'textfield',
 			'label' => 'Transaction Reference Prefix',
 			'description' => 'Here you can insert an transaction reference prefix. The prefix allows you to change the order number that is transmitted to BNP Paribas Mercanet. The prefix must contain the tag {id}. It will then be replaced by the order number (e.g. name_{id}).',
 			'defaultValue' => '{id}',
 			'allowedFileExtensions' => array(
			),
 		),
 		'template_name' => array(
			'id' => 'worldline-sips-paymentpage-template-name',
 			'machineName' => 'template_name',
 			'type' => 'textfield',
 			'label' => 'Template Name',
 			'description' => 'If you created a special template at BNP Paribas Mercanet. It will then be used for the payment page.',
 			'defaultValue' => '',
 			'allowedFileExtensions' => array(
			),
 		),
 		'review_input_form' => array(
			'id' => 'woocommerce-input-form-in-review-pane-setting',
 			'machineName' => 'review_input_form',
 			'type' => 'select',
 			'label' => 'Review Input Form',
 			'description' => 'Should the input form for credit card data rendered in the review pane? To work the user must have JavaScript activated. In case the browser does not support JavaScript a fallback is provided. This feature is not supported by all payment methods.',
 			'defaultValue' => 'active',
 			'allowedFileExtensions' => array(
			),
 		),
 		'order_identifier' => array(
			'id' => 'woocommerce-order-number-setting',
 			'machineName' => 'order_identifier',
 			'type' => 'select',
 			'label' => 'Order Identifier',
 			'description' => 'Set which identifier should be sent to the payment service provider. If a plugin modifies the order number and can not guarantee it\'s uniqueness, select Post Id.',
 			'defaultValue' => 'ordernumber',
 			'allowedFileExtensions' => array(
			),
 		),
 		'log_level' => array(
			'id' => '',
 			'machineName' => 'log_level',
 			'type' => 'select',
 			'label' => 'Log Level',
 			'description' => 'Messages of this or a higher level will be logged.',
 			'defaultValue' => 'error',
 			'allowedFileExtensions' => array(
			),
 		),
 	);

	
	/**
	 * (non-PHPdoc)
	 * @see Customweb_Payment_IConfigurationAdapter::getConfigurationValue()
	 */
	public function getConfigurationValue($key, $languageCode = null) {

		$setting = $this->settingsMap[$key];
		$value =  get_option('woocommerce_bnpmercanetcw_' . $key, $setting['defaultValue']);
		
		if($setting['type'] == 'file') {
			if(isset($value['path']) && file_exists($value['path'])) {
				return new Customweb_Core_Stream_Input_File($value['path']);
			}
			else {
				$resolver = BNPMercanetCw_Util::getAssetResolver();
				return $resolver->resolveAssetStream($setting['defaultValue']);
			}
		}
		else if($setting['type'] == 'multiselect') {
			if(empty($value)){
				return array();
			}
		}
		return $value;
	}
		
	public function existsConfiguration($key, $languageCode = null) {
		if ($languageCode !== null) {
			$languageCode = (string)$languageCode;
		}
		$value = get_option('woocommerce_bnpmercanetcw_' . $key, null);
		if ($value === null) {
			return false;
		}
		else {
			return true;
		}
	}
	
	
}