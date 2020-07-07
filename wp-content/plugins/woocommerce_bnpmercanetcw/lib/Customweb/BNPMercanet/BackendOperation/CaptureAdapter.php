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

require_once 'Customweb/Core/DateTime.php';
require_once 'Customweb/BNPMercanet/Authorization/Transaction.php';
require_once 'Customweb/Payment/Authorization/ITransactionCapture.php';
require_once 'Customweb/Payment/BackendOperation/Adapter/Service/ICapture.php';
require_once 'Customweb/I18n/Translation.php';
require_once 'Customweb/BNPMercanet/BackendOperation/CaptureParameterBuilder.php';
require_once 'Customweb/BNPMercanet/BackendOperation/CancelParameterBuilder.php';
require_once 'Customweb/BNPMercanet/Util.php';
require_once 'Customweb/BNPMercanet/AbstractOfficeAdapter.php';
require_once 'Customweb/Util/Invoice.php';



/**
 *
 * @author Thomas Hunziker
 * @Bean
 *
 */
class Customweb_BNPMercanet_BackendOperation_CaptureAdapter extends Customweb_BNPMercanet_AbstractOfficeAdapter implements 
		Customweb_Payment_BackendOperation_Adapter_Service_ICapture {
	
	private $endpointUrl;

	protected function getEndpoint(){
		return $this->endpointUrl;
	}

	public function capture(Customweb_Payment_Authorization_ITransaction $transaction){
		return $this->partialCapture($transaction, $transaction->getUncapturedLineItems(), true);
	}

	public function partialCapture(Customweb_Payment_Authorization_ITransaction $transaction, $items, $close){
		if (!($transaction instanceof Customweb_BNPMercanet_Authorization_Transaction)) {
			throw new Exception("Unable to cast transaction variable to Customweb_BNPMercanet_Authorization_Transaction.");
		}
		// We close always, we do not support multiple partial captures.
		$close = true;
		$transaction->partialCaptureByLineItemsDry($items, $close);
		$captureAmount = Customweb_Util_Invoice::getTotalAmountIncludingTax($items);
		$parameters = null;
		if ($transaction->getCaptureMode() == 'AUTHOR_CAPTURE') {
			//use cancel to reduce amount
			$authorizationAmount = $transaction->getAuthorizationAmount();
			$reduceAmount = $authorizationAmount - $captureAmount;
			$this->endpointUrl = $this->getConfiguration()->getCancelEndPointUrl();
			if ($reduceAmount > 0) {
				$builder = new Customweb_BNPMercanet_BackendOperation_CancelParameterBuilder($this->getContainer(), $transaction, $reduceAmount);
				$parameters = $builder->build();
			}
		}
		elseif ($transaction->getCaptureMode() == 'VALIDATION') {
			$this->endpointUrl = $this->getConfiguration()->getCaptureEndPointUrl();
			$builder = new Customweb_BNPMercanet_BackendOperation_CaptureParameterBuilder($this->getContainer(), $transaction, $captureAmount);
			$parameters = $builder->build();
		}
		else {
			throw new Exception(Customweb_I18n_Translation::__('This transaction is not in a valid state to be captured'));
		}
		
		if ($transaction->isLiveTransaction() || $transaction->getEnivronment() != 'simulation') {
			if ($parameters !== null) {
				$this->processWithResponseValidation($parameters);
			}			
			$captureItem = $transaction->partialCaptureByLineItems($items, $close);
			$captureItem->setStatus(Customweb_Payment_Authorization_ITransactionCapture::STATUS_PENDING);
			$transaction->setUpdateExecutionDate(Customweb_Core_DateTime::_()->addMinutes(Customweb_BNPMercanet_Util::UPDATE_INTERVAL_AUTHORIZED));
		}
		else {
			$this->checkLiveAvailability();
			$transaction->setUpdateExecutionDate(null);
			$transaction->partialCaptureByLineItems($items, $close);
		}
	}
}