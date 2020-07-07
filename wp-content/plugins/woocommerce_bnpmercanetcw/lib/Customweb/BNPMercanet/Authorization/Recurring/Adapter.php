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
require_once 'Customweb/Core/Exception/CastException.php';
require_once 'Customweb/Payment/Exception/RecurringPaymentErrorException.php';
require_once 'Customweb/BNPMercanet/Authorization/Recurring/ParameterBuilder.php';
require_once 'Customweb/BNPMercanet/Util.php';
require_once 'Customweb/BNPMercanet/AbstractOfficeAdapter.php';
require_once 'Customweb/Payment/Authorization/Recurring/IAdapter.php';



/**
 *
 * @author Thomas Hunziker
 * @Bean
 *
 */
class Customweb_BNPMercanet_Authorization_Recurring_Adapter extends Customweb_BNPMercanet_AbstractOfficeAdapter implements 
		Customweb_Payment_Authorization_Recurring_IAdapter {

	public function getAdapterPriority(){
		return 500;
	}

	public function getAuthorizationMethodName(){
		return self::AUTHORIZATION_METHOD_NAME;
	}

	public function preValidate(Customweb_Payment_Authorization_IOrderContext $orderContext, Customweb_Payment_Authorization_IPaymentCustomerContext $paymentContext){
		$this->getPaymentMethod($orderContext)->preValidate($orderContext, $paymentContext);
	}

	/**
	 *
	 * @return Customweb_BNPMercanet_Method_Factory
	 */
	protected function getMethodFactory(){
		return $this->getContainer()->getBean('Customweb_BNPMercanet_Method_Factory');
	}

	/**
	 *
	 * @param Customweb_Payment_Authorization_IOrderContext $orderContext
	 * @return Customweb_BNPMercanet_Method_Default
	 */
	protected function getPaymentMethod(Customweb_Payment_Authorization_IOrderContext $orderContext){
		return $this->getMethodFactory()->getPaymentMethod($orderContext->getPaymentMethod(), $this->getAuthorizationMethodName());
	}

	protected function getEndpoint(){
		return $this->getConfiguration()->getDuplicateEndPointUrl();
	}

	public function validate(Customweb_Payment_Authorization_IOrderContext $orderContext, Customweb_Payment_Authorization_IPaymentCustomerContext $paymentContext, array $formData){}

	public function isDeferredCapturingSupported(Customweb_Payment_Authorization_IOrderContext $orderContext, Customweb_Payment_Authorization_IPaymentCustomerContext $paymentContext){
		return false;
	}

	public function isPaymentMethodSupportingRecurring(Customweb_Payment_Authorization_IPaymentMethod $paymentMethod){
		$paymentMethodClass = $this->getMethodFactory()->getPaymentMethod($paymentMethod, $this->getAuthorizationMethodName());
		return $paymentMethodClass->isRecurringPaymentSupported();
	}

	public function createTransaction(Customweb_Payment_Authorization_Recurring_ITransactionContext $transactionContext){
		$transaction = new Customweb_BNPMercanet_Authorization_Transaction($transactionContext);
		$transaction->setAuthorizationMethod(self::AUTHORIZATION_METHOD_NAME);
		$transaction->setLiveTransaction(!$this->getConfiguration()->isTestMode());
		$transaction->setEnvironment($this->getConfiguration()->getConfiguredEnvironment());
		return $transaction;
	}

	public function isAuthorizationMethodSupported(Customweb_Payment_Authorization_IOrderContext $orderContext){
		return true;
	}

	public function process(Customweb_Payment_Authorization_ITransaction $transaction){
		if (!($transaction instanceof Customweb_BNPMercanet_Authorization_Transaction)) {
			throw new Customweb_Core_Exception_CastException('Customweb_BNPMercanet_Authorization_Transaction');
		}
		try {
			$builder = new Customweb_BNPMercanet_Authorization_Recurring_ParameterBuilder($this->getContainer(), $transaction);
			$initialParameters = $builder->build();
			$authorizationParameters = $this->processWithSealValidation($initialParameters);
			$transaction->setPaymentId($initialParameters['transactionReference']);
			if (!isset($authorizationParameters['responseCode'])) {
				throw new Exception(Customweb_I18N_Translation('No response Code returned'));
			}
			if ($authorizationParameters['responseCode'] != '00' && $authorizationParameters['responseCode'] != '60') {
				throw new Exception(Customweb_BNPMercanet_Util::getErrorMessageByResponseCode($authorizationParameters['responseCode']));
			}
			$transaction->addAuthorizationParameters(array_merge($initialParameters, $authorizationParameters));
			$transaction->authorize();
			
			if ($authorizationParameters['responseCode'] == '60') {
				$transaction->setAuthorizationUncertain();
				$transaction->setStatusAfterReceivingUpdate('pending');
			}
			$transaction->setUpdateExecutionDate(
					Customweb_Core_DateTime::_()->addMinutes(Customweb_BNPMercanet_Util::UPDATE_INTERVAL_AUTHORIZED));
		}
		catch (Exception $e) {
			$transaction->setAuthorizationFailed($e->getMessage());
			throw new Customweb_Payment_Exception_RecurringPaymentErrorException($e);
		}
	}
}