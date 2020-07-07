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
require_once 'Customweb/Payment/Authorization/Iframe/IAdapter.php';
require_once 'Customweb/BNPMercanet/Util.php';



/**
 *
 * @author Thomas Hunziker
 * @Bean
 *
 */
class Customweb_BNPMercanet_Authorization_Iframe_Adapter extends Customweb_BNPMercanet_Authorization_AbstractAdapter implements 
		Customweb_Payment_Authorization_Iframe_IAdapter {

	public function getAdapterPriority(){
		return 100;
	}

	public function getAuthorizationMethodName(){
		return self::AUTHORIZATION_METHOD_NAME;
	}

	public function createTransaction(Customweb_Payment_Authorization_Iframe_ITransactionContext $transactionContext, $failedTransaction){
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

	/**
	 * This method returns the URL to be set as the src for the Iframe.
	 *
	 * @param Customweb_Payment_Authorization_ITransaction $transaction
	 */
	public function getIframeUrl(Customweb_Payment_Authorization_ITransaction $transaction, array $formData){
		$modifiedForm = array();
		foreach($formData as $key => $value){
			$modifiedForm['bnpmercanetform_'.$key] = $value;
		}
		return $this->getContainer()->getBean('Customweb_Payment_Endpoint_IAdapter')->getUrl("process", "post",
				array_merge(
						array(
							"cw_transaction_id" => $transaction->getExternalTransactionId(),
							"signature" => $transaction->getSecuritySignature("process/post")
						), $modifiedForm));
	}
	
	/**
	 * This method returns the height of the iframe in the browser in pixel.
	 *
	 * @param Customweb_Payment_Authorization_ITransaction $transaction
	 * @param array $formData
	 * @return int Height in pixel
	 */
	public function getIframeHeight(Customweb_Payment_Authorization_ITransaction $transaction, array $formData){
		return 800;
	}
	
	protected function createResponse(Customweb_BNPMercanet_Authorization_Transaction $transaction){
		$url = $this->getContainer()->getBean('Customweb_Payment_Endpoint_IAdapter')->getUrl("process", "breakout",
						array(
							"cw_transaction_id" => $transaction->getExternalTransactionId(),
							"signature" => $transaction->getSecuritySignature("process/breakout")
						));
		return 'redirect:' . $url;
	}


	
}
