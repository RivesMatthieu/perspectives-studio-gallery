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
require_once 'Customweb/Payment/BackendOperation/Adapter/Service/ICancel.php';
require_once 'Customweb/BNPMercanet/BackendOperation/CancelParameterBuilder.php';
require_once 'Customweb/BNPMercanet/AbstractOfficeAdapter.php';



/**
 *
 * @author Thomas Hunziker
 * @Bean
 *
 */
class Customweb_BNPMercanet_BackendOperation_CancelAdapter extends Customweb_BNPMercanet_AbstractOfficeAdapter implements 
		Customweb_Payment_BackendOperation_Adapter_Service_ICancel {

	protected function getEndpoint(){
		return $this->getConfiguration()->getCancelEndPointUrl();
	}

	public function cancel(Customweb_Payment_Authorization_ITransaction $transaction){
		if (!($transaction instanceof Customweb_BNPMercanet_Authorization_Transaction)) {
			throw new Exception("Unable to cast transaction variable to Customweb_BNPMercanet_Authorization_Transaction.");
		}
		
		$transaction->cancelDry();
		$builder = new Customweb_BNPMercanet_BackendOperation_CancelParameterBuilder($this->getContainer(), $transaction);
		$parameters = $builder->build();
		if ($transaction->isLiveTransaction() || $transaction->getEnivronment() != 'simulation') {
			$this->processWithResponseValidation($parameters);
			$transaction->cancel();
			$transaction->setUpdateExecutionDate(null);
		}
		else {
			$this->checkLiveAvailability();
			$transaction->cancel();
			$transaction->setUpdateExecutionDate(null);
		}
	}
}