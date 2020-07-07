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
require_once 'Customweb/BNPMercanet/BackendOperation/RefundParameterBuilder.php';
require_once 'Customweb/Payment/BackendOperation/Adapter/Service/IRefund.php';
require_once 'Customweb/BNPMercanet/AbstractOfficeAdapter.php';
require_once 'Customweb/Util/Invoice.php';



/**
 *
 * @author Thomas Hunziker
 * @Bean
 *
 */
class Customweb_BNPMercanet_BackendOperation_RefundAdapter extends Customweb_BNPMercanet_AbstractOfficeAdapter implements 
		Customweb_Payment_BackendOperation_Adapter_Service_IRefund {

	protected function getEndpoint(){
		return $this->getConfiguration()->getRefundEndPointUrl();
	}

	public function refund(Customweb_Payment_Authorization_ITransaction $transaction){
		return $this->partialRefund($transaction, $transaction->getNonRefundedLineItems(), true);
	}

	public function partialRefund(Customweb_Payment_Authorization_ITransaction $transaction, $items, $close){
		if (!($transaction instanceof Customweb_BNPMercanet_Authorization_Transaction)) {
			throw new Exception("Unable to cast transaction variable to Customweb_BNPMercanet_Authorization_Transaction.");
		}
		
		$transaction->refundByLineItemsDry($items, $close);
		$amount = Customweb_Util_Invoice::getTotalAmountIncludingTax($items);
		$builder = new Customweb_BNPMercanet_BackendOperation_RefundParameterBuilder($this->getContainer(), $transaction, $amount);
		$parameters = $builder->build();
		if ($transaction->isLiveTransaction() || $transaction->getEnivronment()  != 'simulation') {
			$builder = new Customweb_BNPMercanet_BackendOperation_RefundParameterBuilder($this->getContainer(), $transaction, $amount);
			$this->processWithResponseValidation($parameters);
			$transaction->refundByLineItems($items, $close);
		}
		else {
			$this->checkLiveAvailability();
			$transaction->refundByLineItems($items, $close);
		}
	}
}