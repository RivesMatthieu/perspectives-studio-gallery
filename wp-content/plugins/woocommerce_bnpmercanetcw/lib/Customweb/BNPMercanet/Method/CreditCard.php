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

require_once 'Customweb/Util/Country.php';
require_once 'Customweb/Util/Address.php';
require_once 'Customweb/BNPMercanet/Method/Default.php';
require_once 'Customweb/Util/String.php';


/**
 * 
 * @author Thomas Hunziker
 * @Method(paymentMethods={'Visa', 'MasterCard', 'CreditCard', 'Maestro', 'VPAY', 'VisaElectron', 'AmericanExpress', 'CarteBancaire'})
 * 
 */
class Customweb_BNPMercanet_Method_CreditCard extends Customweb_BNPMercanet_Method_Default{
	
	public function getRedirectionAuthorizationFields(Customweb_BNPMercanet_Authorization_Transaction $transaction, array $formData) {
		$fields = parent::getRedirectionAuthorizationFields($transaction, $formData);
		if (strtolower($this->getPaymentMethodName()) == 'creditcard') {
			$map = $this->getPaymentInformationMap();
			$selectedBrands = $this->getPaymentMethodConfigurationValue('credit_card_brands');
			
			$mappedBrandList = array();
			foreach ($map as $key => $data) {
				foreach ($selectedBrands as $brandName) {
					if (strtolower($data['machine_name']) == strtolower($brandName) && isset($data['parameters']['brand'])) {
						$mappedBrandList[] = $data['parameters']['brand'];
					}
				}
			}
			$fields['paymentMeanBrandList'] = implode(',', $mappedBrandList);
			
		}
		return array_merge($fields, $this->getHolderData($transaction, $formData));
	}
	
	
	protected function getHolderData(Customweb_BNPMercanet_Authorization_Transaction $transaction, array $formData){
		$billing = $transaction->getTransactionContext()->getOrderContext()->getBillingAddress();
		$parameters = array();
		
		$mobile = null;
		$phone = null;		
		
		$phone = $billing->getPhoneNumber();
		$phone = preg_replace("/^\+{1}/", "00", $phone);
		$phone = preg_replace("/[^\d]/", "", $phone);
		
		$mobile = $billing->getMobilePhoneNumber();
		$mobile = preg_replace("/^\+{1}/", "00", $mobile);
		$mobile = preg_replace("/[^\d]/", "", $mobile);
		
		if(!empty($phone)){
			$parameters['holderContact.phone'] = Customweb_Util_String::substrUtf8($phone, 0, 10);
		}
		if(!empty($mobile)){
			$parameters['holderContact.mobile'] = Customweb_Util_String::substrUtf8($mobile, 0, 10);
		}
		
		$parameters['holderContact.firstname'] = Customweb_Util_String::substrUtf8(preg_replace('/[^A-Za-z0-9 ]+/', '', $billing->getFirstName()), 0, 40);
		$parameters['holderContact.lastname'] = Customweb_Util_String::substrUtf8(preg_replace('/[^A-Za-z0-9 ]+/', '', $billing->getLastName()), 0, 40);
		$parameters['holderContact.email'] = Customweb_Util_String::substrUtf8($transaction->getTransactionContext()->getOrderContext()->getCustomerEMailAddress(), 0, 128);
		
		// We remove all special chars from the city, as recommend by PSP
		$parameters['holderAddress.city'] = Customweb_Util_String::substrUtf8(preg_replace('/[^A-Za-z0-9 ]+/', '', $billing->getCity()), 0, 50);
		
		$company = $billing->getCompanyName();
		if (!empty($company)) {
			$parameters['holderAddress.company'] = Customweb_Util_String::substrUtf8(preg_replace('/[^A-Za-z0-9 ]+/', '', $company), 0, 50);
		}
		
		$countryCode = $billing->getCountryIsoCode();
		$parameters['holderAddress.country'] = Customweb_Util_Country::getCountry3LetterCode($countryCode);
		$state = $billing->getState();
		if (!empty($state)) {
			$parameters['holderAddress.state'] = Customweb_Util_String::substrUtf8(preg_replace('/[^A-Za-z0-9 ]+/', '', $state), 0, 30);
		}
		$splits = Customweb_Util_Address::splitStreet($billing->getStreet(), $billing->getCountryIsoCode(),
				$billing->getPostCode());
		$parameters['holderAddress.street'] = Customweb_Util_String::substrUtf8(preg_replace('/[^A-Za-z0-9 ]+/', '', $splits['street']), 0, 50);
		if(!empty($splits['street-number'])){
			$parameters['holderAddress.streetNumber'] = Customweb_Util_String::substrUtf8(preg_replace('/[^A-Za-z0-9 ]+/', '', $splits['street-number']), 0,
					10);
		}
		$parameters['holderAddress.zipCode'] = Customweb_Util_String::substrUtf8(preg_replace('/[^A-Za-z0-9 ]+/', '', $billing->getPostCode()), 0,
				10);
		return $parameters;
	}	
}