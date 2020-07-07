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

require_once 'Customweb/BNPMercanet/AbstractParameterBuilder.php';


class Customweb_BNPMercanet_Update_DiagnosticParameterBuilder extends Customweb_BNPMercanet_AbstractParameterBuilder{
	
	public function build() {
		return array_merge(
			$this->getBaseFields(),
			$this->getInterfaceVersionFields(),
			$this->getTransactionReferenceFields()
		);
	}
	
	protected function getInterfaceVersionFields() {
		return array(
			'interfaceVersion' => 'DR_WS_2.26',
		);
	}
	
	protected function getTransactionReferenceFields(){
		$reference = $this->getTransaction()->getTransactionReference();
		if($reference === null) {
			$reference = $this->getHelper()->formatTransactionReference($this->getTransaction()->getExternalTransactionId());
		}
		return array(
			'transactionReference' =>  $reference
		);
	}
	
	
}