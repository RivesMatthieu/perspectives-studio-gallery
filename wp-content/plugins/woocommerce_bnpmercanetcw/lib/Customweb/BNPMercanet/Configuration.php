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

require_once 'Customweb/I18n/Translation.php';


/**
 *
 * @author Thomas Hunziker
 * @Bean
 */
class Customweb_BNPMercanet_Configuration {
	
	/**
	 *
	 * @var Customweb_Payment_IConfigurationAdapter
	 */
	private $configurationAdapter = null;

	public function __construct(Customweb_Payment_IConfigurationAdapter $configurationAdapter){
		$this->configurationAdapter = $configurationAdapter;
	}

	public function isTestMode(){
		if ($this->getConfiguredEnvironment() == 'live') {
			return false;
		}
		else {
			return true;
		}
	}
	
	public function getConfiguredEnvironment(){
		return strtolower($this->getConfigurationValue('operation_mode'));
	}

	public function getTemplateName(){
		return $this->getConfigurationValue('template_name');
	}

	public function getMerchantId(){
		if ($this->getConfiguredEnvironment() == 'simulation') {
			$id = trim($this->getConfigurationValue('simulation_merchant_id'));
		}
		else if ($this->getConfiguredEnvironment() == 'test'){
			$id = trim($this->getConfigurationValue('test_merchant_id'));
		}
			
		else {
			$id = trim($this->getConfigurationValue('live_merchant_id'));
		}
		if (empty($id)) {
			throw new Exception(Customweb_I18n_Translation::__('Merchant ID not configured.'));
		}
		return $id;
	}

	public function getIntermediateServiceProviderId(){
		return trim($this->getConfigurationValue('intermediate_service_provider_id'));
	}

	public function getSecretKeyVersion(){
		if ($this->getConfiguredEnvironment() == 'simulation') {
			$version = $this->getConfigurationValue('simulation_secret_key_version');
		}
		else if ($this->getConfiguredEnvironment() == 'test'){
			$version = $this->getConfigurationValue('test_secret_key_version');
		}
		else {
			$version = $this->getConfigurationValue('live_secret_key_version');
		}
		if (empty($version)) {
			throw new Exception(Customweb_I18n_Translation::__('Key Version not configured.'));
		}
		return $version;
	}

	public function getSecretKey(){
		if ($this->getConfiguredEnvironment() == 'simulation') {
			$key = $this->getConfigurationValue('simulation_secret_key');
		}
		else if ($this->getConfiguredEnvironment() == 'test'){
			$key = $this->getConfigurationValue('test_secret_key');
		}
		else {
			$key = $this->getConfigurationValue('live_secret_key');
		}
		if (empty($key)) {
			throw new Exception(Customweb_I18n_Translation::__('Key not configured.'));
		}
		return $key;
	}

	public function getTransactionReferenceSchema(){
		return $this->getConfigurationValue('transaction_reference_schema');
	}

	/**
	 *
	 * @return string
	 */
	public function getBaseEndPointUrl(){
		if ($this->getConfiguredEnvironment() == 'simulation') {
			return trim('https://payment-webinit.simu.mercanet.bnpparibas.net/') . '/';
		}
		else if ($this->getConfiguredEnvironment() == 'test'){
			return trim('https://payment-webinit.test.sips-atos.com') . '/';
		}
		else {
			return trim('https://payment-webinit.mercanet.bnpparibas.net') . '/';
		}
	}

	/**
	 *
	 * @return string
	 */
	public function getBaseOfficeEndPointUrl(){
		if ($this->isTestMode()) {
			return $this->getBaseOfficeEndPointUrlTest();
		}
		else {
			return $this->getBaseOfficeEndPointUrlLive();
		}
	}

	public function getBaseOfficeEndPointUrlLive(){
		return trim('https://office-server.mercanet.bnpparibas.net') . '/';
	}

	public function getBaseOfficeEndPointUrlTest(){
		if ($this->getConfiguredEnvironment() == 'simulation') {
			return trim('https://office-server.test.sips-atos.com/') . '/';
		}
		else if ($this->getConfiguredEnvironment() == 'test'){
			return trim('https://office-server.test.sips-atos.com') . '/';
		}
		
	}

	/**
	 *
	 * @return string
	 */
	public function getCancelEndPointUrl(){
		return $this->getBaseOfficeEndPointUrl() . 'rs-services/v2/cashManagement/cancel';
	}

	/**
	 *
	 * @return string
	 */
	public function getRefundEndPointUrl(){
		return $this->getBaseOfficeEndPointUrl() . 'rs-services/v2/cashManagement/refund';
	}

	/**
	 *
	 * @return string
	 */
	public function getCaptureEndPointUrl(){
		return $this->getBaseOfficeEndPointUrl() . 'rs-services/v2/cashManagement/validate';
	}

	/**
	 *
	 * @return string
	 */
	public function getDuplicateEndPointUrl(){
		return $this->getBaseOfficeEndPointUrl() . 'rs-services/v2/cashManagement/duplicate';
	}

	/**
	 *
	 * @return string
	 */
	public function getDiagnosticEndPointUrl(){
		return $this->getBaseOfficeEndPointUrl() . 'rs-services/v2/diagnostic/getTransactionData';
	}

	/**
	 *
	 * @return string
	 */
	public function getPaymentInitEndPoint(){
		return $this->getBaseEndPointUrl() . 'paymentInit';
	}

	private function getConfigurationValue($key){
		return $this->configurationAdapter->getConfigurationValue($key);
	}
}