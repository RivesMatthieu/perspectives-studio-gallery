<?php 
/**
  * You are allowed to use this API in your web application.
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

require_once 'Customweb/Core/DateTime.php';
require_once 'Customweb/BNPMercanet/Authorization/Transaction.php';
require_once 'Customweb/Payment/Authorization/ErrorMessage.php';
require_once 'Customweb/I18n/Translation.php';
require_once 'Customweb/Payment/Authorization/IAdapter.php';
require_once 'Customweb/BNPMercanet/AbstractAdapter.php';
require_once 'Customweb/BNPMercanet/Util.php';


abstract class Customweb_BNPMercanet_Authorization_AbstractAdapter extends Customweb_BNPMercanet_AbstractAdapter implements Customweb_Payment_Authorization_IAdapter{
	

	/**
	 * @return Customweb_BNPMercanet_Method_Factory
	 */
	public function getMethodFactory() {
		return $this->getContainer()->getBean('Customweb_BNPMercanet_Method_Factory');
	}
	
	public function preValidate(Customweb_Payment_Authorization_IOrderContext $orderContext, Customweb_Payment_Authorization_IPaymentCustomerContext $paymentContext){
		$this->getPaymentMethod($orderContext)->preValidate($orderContext, $paymentContext);
	}
	
	/**
	 * @param Customweb_Payment_Authorization_IOrderContext $orderContext
	 * @return Customweb_BNPMercanet_Method_Default
	 */
	protected function getPaymentMethod(Customweb_Payment_Authorization_IOrderContext $orderContext) {
		return $this->getMethodFactory()->getPaymentMethod($orderContext->getPaymentMethod(), $this->getAuthorizationMethodName());
	}
	
	public function validate(Customweb_Payment_Authorization_IOrderContext $orderContext, Customweb_Payment_Authorization_IPaymentCustomerContext $paymentContext, array $formData){
		
	}
	
	public function isDeferredCapturingSupported(Customweb_Payment_Authorization_IOrderContext $orderContext, Customweb_Payment_Authorization_IPaymentCustomerContext $paymentContext) {
		return false;
	}
	
	public function isAuthorizationMethodSupported(Customweb_Payment_Authorization_IOrderContext $orderContext) {
		$paymentMethod = $this->getPaymentMethod($orderContext);
		return $paymentMethod->isAuthorizationMethodSupported($this->getAuthorizationMethodName());
	}
	

	

	public function processAuthorization(Customweb_Payment_Authorization_ITransaction $transaction, array $parameters){
		if (!($transaction instanceof Customweb_BNPMercanet_Authorization_Transaction)) {
			throw new Exception("The transaction must be of instance 'Customweb_BNPMercanet_Authorization_Transaction'.");
		}
	
		if ($transaction->isAuthorizationFailed()) {
			return $this->createResponse($transaction);
		}
	
		if ($transaction->isAuthorized()) {
			return $this->createResponse($transaction);
		}
		if (!isset($parameters['Data'])) {
			$transaction->setAuthorizationFailed(Customweb_I18n_Translation::__("The notification contains no 'Data' field."));
			$transaction->setUpdateExecutionDate(null);
			return $this->createResponse($transaction);
		}
	
		if (!isset($parameters['Seal'])) {
			$transaction->setAuthorizationFailed(Customweb_I18n_Translation::__("The notification contains no 'Seal' field."));
			$transaction->setUpdateExecutionDate(null);
			return $this->createResponse($transaction);
		}
		$seal = $this->getHelper()->calculateSeal($parameters['Data']);
	
		if (strtolower($seal) !== $parameters['Seal']) {
			$transaction->setAuthorizationFailed(Customweb_I18n_Translation::__("The calculated and returned seal do not match."));
			$transaction->setUpdateExecutionDate(null);
			return $this->createResponse($transaction);
		}
		$authorizationParameters = Customweb_BNPMercanet_Util::readDataString($parameters['Data']);
	
		if(isset($authorizationParameters['transactionReference'])){
			$transaction->setPaymentId($authorizationParameters['transactionReference']);
		}
	
		if (!isset($authorizationParameters['responseCode'])) {
			$transaction->setAuthorizationFailed(Customweb_I18n_Translation::__("The notification contains no 'responseCode'."));
			$transaction->setUpdateExecutionDate(null);
			return $this->createResponse($transaction);
		}
	
		if ($authorizationParameters['responseCode'] != '00' && $authorizationParameters['responseCode'] != '60') {
			$transaction->addAuthorizationParameters(array(
				'responseCode' => $authorizationParameters['responseCode']
			));
			$transaction->setUpdateExecutionDate(null);
			$transaction->setAuthorizationFailed(
					Customweb_BNPMercanet_Util::getErrorMessageByResponseCode($authorizationParameters['responseCode']));
			return $this->createResponse($transaction);
		}
		
		$transaction->addAuthorizationParameters($authorizationParameters);
		
		if ($authorizationParameters['responseCode'] == '00') {
			$methodFactory = $this->getContainer()->getBean('Customweb_BNPMercanet_Method_Factory');
			$method = $methodFactory->getPaymentMethod($transaction->getPaymentMethod(), $transaction->getAuthorizationMethod());
			if((!isset($authorizationParameters['acquirerResponseCode']) || !isset($authorizationParameters['authorisationId'])) && $method->checkAcquirerResponseCode()){
				$transaction->setUpdateExecutionDate(null);
				$transaction->setAuthorizationFailed(new Customweb_Payment_Authorization_ErrorMessage(Customweb_I18n_Translation::__("Unexpected failure, please try again or use another payment method."), Customweb_I18n_Translation::__("Missing or empty parameters 'acquirerResponseCode' or 'authorisationId'.")));
				return $this->createResponse($transaction);
			}
		}
		$transaction->authorize();
		
		if($method->checkAcquirerResponseCode()) {
			$method->processAcquirerResponseCode($transaction, $authorizationParameters);
		}
		
		if ($authorizationParameters['responseCode'] == '60') {
			$transaction->setUpdateExecutionDate(
					Customweb_Core_DateTime::_()->addMinutes(Customweb_BNPMercanet_Util::UPDATE_INTERVAL_AUTHORIZED));
			$transaction->setStatusAfterReceivingUpdate('pending');
			$transaction->setAuthorizationUncertain();
		}
	
		if (isset($authorizationParameters['captureDay'])) {
			$transaction->setCaptureDelay($authorizationParameters['captureDay']);
		}
		if (strtoupper($authorizationParameters['captureMode']) == 'IMMEDIATE' && $transaction->getStatusAfterReceivingUpdate() != 'pending_capture') {
			$transaction->capture();
		}
		else {
			if (isset($authorizationParameters['captureDay']) && $authorizationParameters['captureDay'] == '0' && $authorizationParameters['responseCode'] == '00' && $transaction->getCaptureMode() == 'AUTHOR_CAPTURE'&& $transaction->getStatusAfterReceivingUpdate() != 'pending_capture') {
				$transaction->capture();
			}
			else {
				$dayWait = 1;
				if(isset($authorizationParameters['captureDay'])){
					$dayWait = intval($authorizationParameters['captureDay']);
				}
				$transaction->setUpdateExecutionDate(
						Customweb_Core_DateTime::_()->addMinutes($dayWait*24*60 - 4* 60));
			}
		}
		return $this->createResponse($transaction);
	}
	
	protected function createResponse(Customweb_BNPMercanet_Authorization_Transaction $transaction){
		$url = $transaction->getSuccessUrl();
		if($transaction->isAuthorizationFailed()){
			$url = $transaction->getFailedUrl();
		}
		return 'redirect:' . $url;
	}
	
}