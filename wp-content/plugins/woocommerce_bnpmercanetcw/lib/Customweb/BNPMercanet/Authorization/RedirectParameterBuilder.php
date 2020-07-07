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

require_once 'Customweb/Util/Country.php';
require_once 'Customweb/Core/String.php';
require_once 'Customweb/Core/Http/ContextRequest.php';
require_once 'Customweb/Util/Currency.php';
require_once 'Customweb/BNPMercanet/BackendOperation/Form/RiskManagement.php';
require_once 'Customweb/Util/Address.php';
require_once 'Customweb/Util/String.php';
require_once 'Customweb/Core/Util/Rand.php';
require_once 'Customweb/BNPMercanet/Authorization/AbstractParameterBuilder.php';
require_once 'Customweb/BNPMercanet/Util.php';



/**
 *
 * @author Thomas Hunziker
 */
class Customweb_BNPMercanet_Authorization_RedirectParameterBuilder extends Customweb_BNPMercanet_Authorization_AbstractParameterBuilder {
	private $formData = array();

	public function __construct(Customweb_DependencyInjection_IContainer $container, Customweb_BNPMercanet_Authorization_Transaction $transaction, array $formData){
		parent::__construct($container, $transaction);
		$this->formData = $formData;
	}

	protected function getFormData(){
		return $this->formData;
	}

	public function buildParameterArray(){
		$dataPairs = array();
		$fields = $this->buildFieldArray();
		foreach ($fields as $key => $value) {
			$dataPairs[] = $key . '=' . $value;
		}
		$data = base64_encode(implode('|', $dataPairs));

		// Sign Paramters
		$seal = $this->getHelper()->calculateSeal($data);

		return array(
			'Encode' => 'base64',
			'InterfaceVersion' => 'HP_2.26',
			'Seal' => $seal,
			'Data' => $data
		);
	}

	protected function buildFieldArray(){
		return array_merge($this->getAmountFields(), $this->getBaseFields(), $this->getResponseUrlFields(), $this->getTransactionReferenceFields(),
				$this->getCustomerFields(), $this->getPaymentPageSettingFields(), $this->getMethodSpecificFields(),
				$this->getRiskManagementParameters(), $this->getOneClickWalletParameters(), $this->getStylingParameters());
	}

	protected function getOneClickWalletParameters(){
		$oneclick = array();
		if($this->getPaymentMethod()->isAliasManagerSupported() && $this->getTransaction()->getTransactionContext()->getAlias()){
			$customerId = $this->getTransaction()->getTransactionContext()->getOrderContext()->getCustomerId();
			if(!empty($customerId)) {
				$customerContext = $this->getTransaction()->getPaymentCustomerContext();
				$contextMap = $customerContext->getMap();
				$walletId = '';
				if (isset($contextMap['walletId']) && $contextMap['walletId'] != null) {
					$walletId = $contextMap['walletId'];
				}
				else {

					$walletIdLong = $customerId . '_' . Customweb_Core_Util_Rand::getRandomString(20, '');
					$walletId = Customweb_Core_String::_($walletIdLong)->substring(0, 21)->toString();
					$customerContext->updateMap(array(
						'walletId' => $walletId
					));
				}
				$oneclick['merchantWalletID'] = $walletId;
			}
		}
		return $oneclick;
	}

	protected function getStylingParameters(){
		$parameters = array();
		$templateName = trim($this->getConfiguration()->getTemplateName());
		if(!empty($templateName)){
			$parameters['templateName'] = $templateName;
		}
		return $parameters;
	}

	protected function getMethodSpecificFields(){
		return $this->getPaymentMethod()->getRedirectionAuthorizationFields($this->getTransaction(), $this->getFormData());
	}


	protected function getResponseUrlFields(){
		return array(
			'normalReturnUrl' => $this->getContainer()->getBean('Customweb_Payment_Endpoint_IAdapter')->getUrl("process", "redirect",
					array(
						'cw_transaction_id' => $this->getTransaction()->getExternalTransactionId()
					)),
			'automaticResponseUrl' => $this->getContainer()->getBean('Customweb_Payment_Endpoint_IAdapter')->getUrl("process", "index",
					array(
						'cw_transaction_id' => $this->getTransaction()->getExternalTransactionId()
					))
		);
	}


	protected function getPaymentPageSettingFields(){
		return array(
			'paypageData.bypassReceiptPage' => 'true'
		);
	}

	protected function getCustomerFields(){
		$data = array_merge($this->getCustomerAddressFields(), $this->getBillingAddressFields(), $this->getDeliveryAddressFields());

		$orderContext = $this->getTransaction()->getTransactionContext()->getOrderContext();
		try {
			$ipV4 = Customweb_Core_Http_ContextRequest::getClientIpV4();
			if (!empty($ipV4)) {
				$data['customerIpAddress'] = $ipV4;
			}
		}
		catch (Exception $e) {
			//It is an optional parameter, if this fails we do not send it
		}
		$customerId = $orderContext->getCustomerId();

		if (!empty($customerId)) {
			$data['customerId'] = Customweb_Util_String::substrUtf8($customerId, 0, 19);
		}
		$data['customerLanguage'] = Customweb_BNPMercanet_Util::getCleanLanguageCode($orderContext->getLanguage());

		return $data;
	}

	protected function getCustomerAddressFields(){
		$data = $this->getBillingAddressFields();
		$customerData = array();

		foreach ($data as $key => $value) {
			$key = str_replace('billingAddress', 'customerAddress', $key);
			$key = str_replace('billingContact', 'customerContact', $key);
			$customerData[$key] = $value;
		}

		return $customerData;
	}

	protected function getBillingAddressFields(){
		$data = array();

		$context = $this->getTransaction()->getTransactionContext()->getOrderContext();
		// We remove all special chars from the city, as recommend by PSP
		$data['billingAddress.city'] = Customweb_Util_String::substrUtf8(preg_replace('/[^A-Za-z0-9 ]+/', '', $context->getBillingCity()), 0, 50);

		$company = $context->getBillingCompanyName();
		if (!empty($company)) {
			$data['billingAddress.company'] = Customweb_Util_String::substrUtf8(preg_replace('/[^A-Za-z0-9 ]+/', '', $company), 0, 50);
		}

		$countryCode = $context->getBillingCountryIsoCode();
		$data['billingAddress.country'] = Customweb_Util_Country::getCountry3LetterCode($countryCode);
		$state = $context->getBillingState();
		if (!empty($state)) {
			$data['billingAddress.state'] = Customweb_Util_String::substrUtf8(preg_replace('/[^A-Za-z0-9 ]+/', '', $state), 0, 30);
		}
		$splits = Customweb_Util_Address::splitStreet($context->getBillingStreet(), $context->getBillingCountryIsoCode(),
				$context->getBillingPostCode());
		$data['billingAddress.street'] = Customweb_Util_String::substrUtf8(preg_replace('/[^A-Za-z0-9 ]+/', '', $splits['street']), 0, 50);
		$data['billingAddress.streetNumber'] = Customweb_Util_String::substrUtf8(preg_replace('/[^A-Za-z0-9 ]+/', '', $splits['street-number']), 0,
				10);
		$data['billingAddress.zipCode'] = Customweb_Util_String::substrUtf8(preg_replace('/[^A-Za-z0-9 ]+/', '', $context->getBillingPostCode()), 0,
				10);

		$data['billingContact.email'] = Customweb_Util_String::substrUtf8($context->getBillingEMailAddress(), 0, 128);
		$data['billingContact.firstname'] = Customweb_Util_String::substrUtf8(preg_replace("/[^\p{L}\p{N} -]/u", "", $context->getBillingFirstName()),
				0, 50);
		$data['billingContact.lastname'] = Customweb_Util_String::substrUtf8(preg_replace("/[^\p{L}\p{N} -]/u", "", $context->getBillingLastName()),
				0, 50);

		return $data;
	}

	protected function getRiskManagementParameters(){
		$data = array();
		$paymentMethod = $this->getTransaction()->getPaymentMethod();
		if ($paymentMethod->existsPaymentMethodConfigurationValue('threed_secure_min_amount')) {
			$minAmount = intval($paymentMethod->getPaymentMethodConfigurationValue('threed_secure_min_amount'));
			if (Customweb_Util_Currency::compareAmount($this->getTransaction()->getAuthorizationAmount(), $minAmount,
					$this->getTransaction()->getCurrencyCode()) < 0) {
				$data['fraudData.bypass3DS'] = 'All';
			}
		}
		$byPassSettings = Customweb_BNPMercanet_BackendOperation_Form_RiskManagement::getRiskManagementFields();
		$settingsHandler = $this->getContainer()->getBean('Customweb_Payment_SettingHandler');
		$tmpArray = array();
		$authorizationAmount = $this->getTransaction()->getAuthorizationAmount();
		$currency = $this->getTransaction()->getCurrencyCode();
		$byPassList = '';
		foreach ($byPassSettings as $key => $fieldValues) {
			$maxValue = intval($settingsHandler->getSettingValue($key));
			if (Customweb_Util_Currency::compareAmount($authorizationAmount, $maxValue, $currency) < 0) {
				$byPassList .= $key . ',';
			}
		}
		$byPassList = rtrim($byPassList, ',');
		if (!empty($byPassList)) {
			$data['fraudData.bypassCtrlList'] = $byPassList;
		}
		return $data;
	}

	protected function getDeliveryAddressFields(){
		$data = array();
		if ($this->getPaymentMethod()->isDeliveryAddressAllowed()) {
			$context = $this->getTransaction()->getTransactionContext()->getOrderContext();
			$data['deliveryAddress.city'] = Customweb_Util_String::substrUtf8(preg_replace('/[^A-Za-z0-9 ]+/', '', $context->getShippingCity()), 0,
					50);

			$company = $context->getShippingCompanyName();
			if (!empty($company)) {
				$data['deliveryAddress.company'] = Customweb_Util_String::substrUtf8(preg_replace('/[^A-Za-z0-9 ]+/', '', $company), 0, 50);
			}

			$countryCode = $context->getShippingCountryIsoCode();
			$data['deliveryAddress.country'] = Customweb_Util_Country::getCountry3LetterCode($countryCode);
			$state = $context->getShippingState();
			if (!empty($state)) {
				$data['deliveryAddress.state'] = Customweb_Util_String::substrUtf8(preg_replace('/[^A-Za-z0-9 ]+/', '', $state), 0, 30);
			}

			$splits = Customweb_Util_Address::splitStreet($context->getShippingStreet(), $context->getShippingCountryIsoCode(),
					$context->getShippingPostCode());
			$data['deliveryAddress.street'] = Customweb_Util_String::substrUtf8(preg_replace('/[^A-Za-z0-9 ]+/', '', $splits['street']), 0, 50);
			$data['deliveryAddress.streetNumber'] = Customweb_Util_String::substrUtf8(preg_replace('/[^A-Za-z0-9 ]+/', '', $splits['street-number']),
					0, 10);
			$data['deliveryAddress.zipCode'] = Customweb_Util_String::substrUtf8(
					preg_replace('/[^A-Za-z0-9 ]+/', '', $context->getShippingPostCode()), 0, 10);

			$data['deliveryContact.email'] = Customweb_Util_String::substrUtf8($context->getShippingEMailAddress(), 0, 128);
			$data['deliveryContact.firstname'] = Customweb_Util_String::substrUtf8(
					preg_replace("/[^\p{L}\p{N} -]/u", "", $context->getShippingFirstName()), 0, 50);
			$data['deliveryContact.lastname'] = Customweb_Util_String::substrUtf8(
					preg_replace("/[^\p{L}\p{N} -]/u", "", $context->getShippingLastName()), 0, 50);
		}
		return $data;
	}
}
