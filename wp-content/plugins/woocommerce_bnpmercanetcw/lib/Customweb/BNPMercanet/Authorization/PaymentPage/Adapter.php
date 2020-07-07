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

require_once 'Customweb/BNPMercanet/Authorization/Transaction.php';
require_once 'Customweb/Core/DateTime.php';
require_once 'Customweb/BNPMercanet/Authorization/AbstractAdapter.php';
require_once 'Customweb/Payment/Authorization/PaymentPage/IAdapter.php';
require_once 'Customweb/BNPMercanet/Util.php';



/**
 *
 * @author Thomas Hunziker
 * @Bean
 *
 */
class Customweb_BNPMercanet_Authorization_PaymentPage_Adapter extends Customweb_BNPMercanet_Authorization_AbstractAdapter implements 
		Customweb_Payment_Authorization_PaymentPage_IAdapter {

	public function getAdapterPriority(){
		return 100;
	}

	public function getAuthorizationMethodName(){
		return self::AUTHORIZATION_METHOD_NAME;
	}

	public function createTransaction(Customweb_Payment_Authorization_PaymentPage_ITransactionContext $transactionContext, $failedTransaction){
		$transaction = new Customweb_BNPMercanet_Authorization_Transaction($transactionContext);
		$transaction->setAuthorizationMethod(self::AUTHORIZATION_METHOD_NAME);
		$transaction->setLiveTransaction(!$this->getConfiguration()->isTestMode());
		$transaction->setEnvironment($this->getConfiguration()->getConfiguredEnvironment());
		$transaction->setUpdateExecutionDate(
				Customweb_Core_DateTime::_()->addMinutes(Customweb_BNPMercanet_Util::UPDATE_INTERVAL_NONAUTHORIZED));
		return $transaction;
	}

	public function getVisibleFormFields(Customweb_Payment_Authorization_IOrderContext $orderContext, $aliasTransaction, $failedTransaction, $paymentCustomerContext){
		return $this->getPaymentMethod($orderContext)->getVisibleFormFields($orderContext, $aliasTransaction, $failedTransaction, 
				$paymentCustomerContext);
	}

	public function isHeaderRedirectionSupported(Customweb_Payment_Authorization_ITransaction $transaction, array $formData){
		return false;
	}

	public function getRedirectionUrl(Customweb_Payment_Authorization_ITransaction $transaction, array $formData){
		throw new Exception("This method should not be invoked, because we can redirect the customer only with HTML form.");
	}

	public function getParameters(Customweb_Payment_Authorization_ITransaction $transaction, array $formData){
		try {
			return $this->buildParameters($transaction, $formData);
		}
		catch (Exception $e) {
			//gets handled in getFormActionUrl
		}
		return array();
	}

	private function buildParameters(Customweb_Payment_Authorization_ITransaction $transaction, array $formData){
		$builder = $this->getPaymentMethod($transaction->getTransactionContext()->getOrderContext())->getRedirectParameterBuilder($transaction, $formData);
		return $builder->buildParameterArray();
	}

	public function getFormActionUrl(Customweb_Payment_Authorization_ITransaction $transaction, array $formData){
		try {
			$this->buildParameters($transaction, $formData);
		}
		catch (Exception $e) {
			$transaction->setAuthorizationFailed($e->getMessage());
			return $transaction->getFailedUrl();
		}
		return $this->getConfiguration()->getPaymentInitEndPoint();
	}

}
