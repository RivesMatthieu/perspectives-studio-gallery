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
require_once 'Customweb/Payment/Authorization/DefaultTransaction.php';

class Customweb_BNPMercanet_Authorization_Transaction extends Customweb_Payment_Authorization_DefaultTransaction {
	private $captureDelay = null;
	private $statusAfterReceivingUpdate = null;
	private $captureMode;
	private $notFoundCounter = 0;
	private $environment;

	public function getTransactionReference(){
		$params = $this->getAuthorizationParameters();
		if(!isset($params['transactionReference'])) {
			return null;
		}
		return $params['transactionReference'];
	}

	public function isCaptureClosable(){
		// We support only one capture per transaction, hence the first capture
		// closes the transaction.
		return false;
	}
	
	public function setEnvironment($environment){
		$this->environment= $environment;
	}
	
	public function getEnivronment(){
		return $this->environment;
	}

	protected function getTransactionSpecificLabels(){
		$labels = array();
		
		$delay = $this->getCaptureDelay();
		$parameters = $this->getAuthorizationParameters();
		if (isset($parameters['responseCode']) && $parameters['responseCode'] != '') {
			$label = Customweb_I18n_Translation::__('Response Code');
			$description = Customweb_I18n_Translation::__('The response code returned by ____paymentServiceProvider____ after the authorization.');
			$labels['responseCode'] = array(
				'label' => $label,
				'value' => $parameters['responseCode'],
				'description' => $description 
			);
		}
		
		if ($delay !== null) {
			$label = Customweb_I18n_Translation::__('Capture Delay');
			$description = Customweb_I18n_Translation::__(
					'The capture delay indicates how many days the transaction capture is delayed after the authorization.');
			if ($this->getCaptureMode() == 'VALIDATION') {
				$label = Customweb_I18n_Translation::__('Validation Time');
				$description = Customweb_I18n_Translation::__(
						'The validation time indicates how many days the authorization is valid. It has to be captured in this time period');
			}
			$labels['captureDay'] = array(
				'label' => $label,
				'value' => $delay,
				'description' => $description 
			);
		}
		if(isset($parameters['authorisationId']) && $parameters['authorisationId'] != ''){
			$label = Customweb_I18n_Translation::__('Authorisation Id');
			$description = Customweb_I18n_Translation::__('The authorisation Id returned by ____paymentServiceProvider____ after the authorization.');
			$labels['authorisationId'] = array(
				'label' => $label,
				'value' => $parameters['authorisationId'],
				'description' => $description
			);
		}
		if(isset($parameters['transactionReference']) && $parameters['transactionReference'] != ''){
			$label = Customweb_I18n_Translation::__('Transaction Reference');
			$description = Customweb_I18n_Translation::__('The transaction reference returned by ____paymentServiceProvider____ after the authorization.');
			$labels['transactionReference'] = array(
				'label' => $label,
				'value' => $parameters['transactionReference'],
				'description' => $description
			);
		}
		if(isset($parameters['s10TransactionId']) && $parameters['s10TransactionId'] != ''){
			$label = Customweb_I18n_Translation::__('s10TransactionId');
			$description = Customweb_I18n_Translation::__('The s10TransactionId returned by ____paymentServiceProvider____ after the authorization.');
			$labels['s10TransactionId'] = array(
				'label' => $label,
				'value' => $parameters['s10TransactionId'],
				'description' => $description
			);
		}
		if(isset($parameters['paymentMeanType']) && $parameters['paymentMeanType'] != ''){
			$label = Customweb_I18n_Translation::__('Payment Type');
			$labels['paymentMeanType'] = array(
				'label' => $label,
				'value' => $parameters['paymentMeanType']
			);
		}
		if(isset($parameters['paymentMeanBrand']) && $parameters['paymentMeanBrand'] != ''){
			$label = Customweb_I18n_Translation::__('Payment Brand');
			$labels['paymentMeanBrand'] = array(
				'label' => $label,
				'value' => $parameters['paymentMeanBrand']
			);
		}
		if(isset($parameters['instalmentPlanName']) && $parameters['instalmentPlanName'] != ''){
			$label = Customweb_I18n_Translation::__('Instalment Plan');
			$labels['instalmentPlanName'] = array(
				'label' => $label,
				'value' => $parameters['instalmentPlanName']
			);
		}
		
		return $labels;
	}

	public function getCaptureDelay(){
		return $this->captureDelay;
	}

	public function setCaptureDelay($captureDelay){
		$this->captureDelay = $captureDelay;
		return $this;
	}

	public function setCaptureMode($mode){
		$this->captureMode = $mode;
		return $this;
	}

	public function getCaptureMode(){
		return $this->captureMode;
	}

	public function getStatusAfterReceivingUpdate(){
		return $this->statusAfterReceivingUpdate;
	}

	public function setStatusAfterReceivingUpdate($state){
		$this->statusAfterReceivingUpdate = $state;
	}

	public function addAuthorizationParameters(array $parameters){
		$existing = $this->getAuthorizationParameters();
		if(!is_array($existing)){
			$existing = array();
		}
		foreach($parameters as $key => $value){
			$existing[$key] = $value;
		}
		$this->setAuthorizationParameters($existing);
	}
	
	
	public function getNotFoundCounter(){
		return $this->notFoundCounter;
	}
	
	public function increaseNotFoundCounter(){
		$this->notFoundCounter++;
	}
	
	protected function getCustomOrderStatusSettingKey($statusKey){
		$method = $this->getPaymentMethod();
		if ($this->getStatusAfterReceivingUpdate() == 'success') {
			if ($method->existsPaymentMethodConfigurationValue('status_success_after_uncertain')) {
				$updateSuccess = $method->getPaymentMethodConfigurationValue('status_success_after_uncertain');
				if ($updateSuccess != 'no_status_change' && $updateSuccess != 'none') {
					$statusKey = 'status_success_after_uncertain';
				}
			}
		}
		else if ($this->getStatusAfterReceivingUpdate() == 'refused') {
			if ($method->existsPaymentMethodConfigurationValue('status_refused_after_uncertain')) {
				$updateRefused = $method->getPaymentMethodConfigurationValue('status_refused_after_uncertain');
				if ($updateRefused != 'no_status_change' && $updateRefused != 'none') {
					$statusKey = 'status_refused_after_uncertain';
				}
			}
		}
		
		return $statusKey;
	}
}