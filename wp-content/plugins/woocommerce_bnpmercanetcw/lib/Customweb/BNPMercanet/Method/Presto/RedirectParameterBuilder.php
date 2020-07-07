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

require_once 'Customweb/BNPMercanet/Authorization/RedirectParameterBuilder.php';
require_once 'Customweb/Util/String.php';
require_once 'Customweb/Core/Util/Rand.php';


/**
 *
 * @author Sebastian Bossert
 */
class Customweb_BNPMercanet_Method_Presto_RedirectParameterBuilder extends Customweb_BNPMercanet_Authorization_RedirectParameterBuilder {

	protected function getCustomerFields(){
		return $this->getCustomerAddressFields();
	}

	protected function getCustomerAddressFields(){
		$address = $this->getTransaction()->getTransactionContext()->getOrderContext()->getBillingAddress();
		$customerId = Customweb_Util_String::substrUtf8($this->getTransaction()->getTransactionContext()->getOrderContext()->getCustomerId(), 0, 21);
		if (empty($customerId)) {
			$customerId = Customweb_Core_Util_Rand::getRandomString(21);
		}
		return array(
			"customerAddress.addressAdditional1" => Customweb_Util_String::substrUtf8($address->getStreet(), 0, 32),
			"customerAddress.zipCode" => $address->getPostCode(),
			"customerAddress.city" => Customweb_Util_String::substrUtf8($address->getCity(), 0, 30),
			"customerContact.firstname" => Customweb_Util_String::substrUtf8($address->getFirstName(), 0, 30),
			"customerContact.lastname" => Customweb_Util_String::substrUtf8($address->getLastName(), 0, 30),
			"customerContact.email" => Customweb_Util_String::substrUtf8($address->getEMailAddress(), 0, 49),
			"customerId" => $customerId
		);
	}
}
