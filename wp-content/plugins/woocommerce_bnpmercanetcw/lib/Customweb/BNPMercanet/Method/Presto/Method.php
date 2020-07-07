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

require_once 'Customweb/BNPMercanet/Method/Presto/RedirectParameterBuilder.php';
require_once 'Customweb/Core/DateTime.php';
require_once 'Customweb/Util/Rand.php';
require_once 'Customweb/Util/Currency.php';
require_once 'Customweb/I18n/Translation.php';
require_once 'Customweb/BNPMercanet/Method/Default.php';
require_once 'Customweb/Util/String.php';
require_once 'Customweb/BNPMercanet/Util.php';
require_once 'Customweb/Payment/Util.php';



/**
 *
 * @author Thomas Hunziker
 * @Method(paymentMethods={'CetelemPresto'})
 */
class Customweb_BNPMercanet_Method_Presto_Method extends Customweb_BNPMercanet_Method_Default {
	const MIN_AMOUNT = 300;
	
	public function preValidate(Customweb_Payment_Authorization_IOrderContext $orderContext,
			Customweb_Payment_Authorization_IPaymentCustomerContext $paymentContext){
		parent::preValidate($orderContext, $paymentContext);
		if($orderContext->getOrderAmountInDecimals() < self::MIN_AMOUNT) {
			throw new Exception(Customweb_I18n_Translation::__("Cart minimum for Cetelem Presto is !amount.", array("!amount" => self::MIN_AMOUNT)));
		}
	}
	
	public function getRedirectParameterBuilder(Customweb_BNPMercanet_Authorization_Transaction $transaction, array $formData) {
		return new Customweb_BNPMercanet_Method_Presto_RedirectParameterBuilder($this->getContainer(), $transaction, $formData);
	}
	
	public function getRedirectionAuthorizationFields(Customweb_BNPMercanet_Authorization_Transaction $transaction, array $formData){
		$parameters = parent::getRedirectionAuthorizationFields($transaction, $formData);
		$parameters['paymentPattern'] = 'ONE_SHOT';
		$parameters['orderId'] = $this->formatSchemaForOrderId($transaction->getTransactionId(), 13);
		$parameters['paymentMeanData.presto.paymentMeanCustomerId'] = $this->formatSchemaForOrderId($transaction->getTransactionId(), 21);
// 		$parameters['customerLanguage'] = 'fr';
		$type = $this->getPaymentMethodConfigurationValue('cetelem_type');
		if($type == 'CCH' && Customweb_Util_Currency::compareAmount($transaction->getAuthorizationAmount(), 1500, $transaction->getCurrencyCode()) <= 0){
			$parameters['paymentMeanData.presto.financialProduct'] = 'CCH';
			$parameters['paymentMeanData.presto.prestoCardType'] = 'A';
		}
		else{
			$parameters['paymentMeanData.presto.financialProduct'] = 'CLA';
		}

		$billing = $transaction->getTransactionContext()->getOrderContext()->getBillingAddress();
// 		$parameters['holderContact.firstname'] = Customweb_Util_String::substrUtf8(preg_replace('/[^A-Za-z0-9 ]+/', '', $billing->getFirstName()), 0, 40);
// 		$parameters['holderContact.lastname'] = Customweb_Util_String::substrUtf8(preg_replace('/[^A-Za-z0-9 ]+/', '', $billing->getLastName()), 0, 40);
// 		$parameters['holderContact.email'] = Customweb_Util_String::substrUtf8($transaction->getTransactionContext()->getOrderContext()->getCustomerEMailAddress(), 0, 128);
		$parameters['customerAddress.city'] = Customweb_Util_String::substrUtf8(preg_replace('/[^A-Za-z0-9 ]+/', '', $billing->getCity()), 0, 30);
		$parameters['customerAddress.addressAdditional1'] = Customweb_Util_String::substrUtf8(preg_replace('/[^A-Za-z0-9 ]+/', '', $billing->getStreet()), 0, 32);
		$parameters['customerAddress.zipCode'] = Customweb_Util_String::substrUtf8(preg_replace('/[^A-Za-z0-9 ]+/', '', $billing->getPostCode()), 0, 10);

		$product = $this->getPaymentMethodConfigurationValue('cetelem_product');
		$parameters['shoppingCartDetail.mainProduct'] = $product;
		return $parameters;
	}


	private function formatSchemaForOrderId($transactionId, $length){
		$configuration = $this->getContainer()->getBean('Customweb_BNPMercanet_Configuration');
		$schema = $configuration->getTransactionReferenceSchema();
		$transactionId = preg_replace('/[^0-9A-Z]+/i', '', $transactionId);

		// We add a random part to ensure, that the reference will be always unique. In
		// test mode all merchant use the same account, hence the transaction reference is
		// hard to get unique, without randomness.
		if ($configuration->isTestMode()) {
			$schema = Customweb_Util_Rand::getRandomString(6) . $schema;
		}

		$transactionId = Customweb_Payment_Util::applyOrderSchemaImproved($schema, $transactionId, $length);
		// We filter the transaction ID again in case the merchant adds a invalid char over the schema.
		$transactionId = preg_replace('/[^0-9A-Z]+/i', '', $transactionId);
		return $transactionId;
	}

	public function getCaptureModeFields(Customweb_BNPMercanet_Authorization_Transaction $transaction){
		return array(
			'captureMode' => 'IMMEDIATE',
			'captureDay' => 0
		);
	}

	public function isNFoisActivated(){
		return false;
	}

	public function checkAcquirerResponseCode(){
		return true;
	}
	
	public function processAcquirerResponseCode(Customweb_BNPMercanet_Authorization_Transaction $transaction, array $parameters) {
		if(isset($parameters['acquirerResponseCode']) && isset($parameters['responseCode'])) {
			if($parameters['responseCode'] == '00' && ($parameters['acquirerResponseCode'] === '08' || $parameters['acquirerResponseCode'] === '02')){
				$transaction->setUpdateExecutionDate(
						Customweb_Core_DateTime::_()->addMinutes(Customweb_BNPMercanet_Util::UPDATE_INTERVAL_AUTHORIZED));
				$transaction->setStatusAfterReceivingUpdate('pending_capture');
				$transaction->setAuthorizationUncertain();
			}
		}
	}


}