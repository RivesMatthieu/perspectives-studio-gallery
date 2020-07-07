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

require_once 'Customweb/Form/Element.php';
require_once 'Customweb/Form/Validator/NotEmpty.php';
require_once 'Customweb/I18n/Translation.php';
require_once 'Customweb/BNPMercanet/Method/Default.php';
require_once 'Customweb/Util/String.php';
require_once 'Customweb/Form/Control/Select.php';
require_once 'Customweb/Form/Control/TextInput.php';
require_once 'Customweb/I18n/LocalizableException.php';
require_once 'Customweb/BNPMercanet/SalutationUtil.php';



/**
 *
 * @author Thomas Hunziker
 * @Method(paymentMethods={'CetelemThree', 'CetelemFour'})
 */
class Customweb_BNPMercanet_Method_Cetelem extends Customweb_BNPMercanet_Method_Default {

	public function getRedirectionAuthorizationFields(Customweb_BNPMercanet_Authorization_Transaction $transaction, array $formData){
		$parameters = parent::getRedirectionAuthorizationFields($transaction, $formData);
		$parameters['paymentPattern'] = 'ONE_SHOT';
		$parameters['orderId'] = $this->getHelper()->formatSchemaForOrderId($transaction->getTransactionId());
		$parameters['customerLanguage'] = 'fr';
		return array_merge($parameters, $this->getHolderData($transaction, $formData));
	}

	public function getCaptureModeFields(Customweb_BNPMercanet_Authorization_Transaction $transaction){
		return array(
			'captureMode' => 'IMMEDIATE', 
			'captureDay' => 0
		);
	}
	
	public function getVisibleFormFields(Customweb_Payment_Authorization_IOrderContext $orderContext, $aliasTransaction, $failedTransaction, $paymentCustomerContext){
		$salutation = $orderContext->getBillingAddress()->getSalutation();
		$gender = Customweb_BNPMercanet_SalutationUtil::getGender($salutation);
		$elements = array();
		if($gender === null){
			$elements[] = $this->getSalutationElement();	
		}
		$phone = $orderContext->getBillingAddress()->getPhoneNumber();
		$phone = preg_replace("/^\+{1}/", "00", $phone);
		$phone = preg_replace("/[^\d]/", "", $phone);
		
		$mobile = $orderContext->getBillingAddress()->getMobilePhoneNumber();
		$mobile = preg_replace("/^\+{1}/", "00", $mobile);
		$mobile = preg_replace("/[^\d]/", "", $mobile);
		
		if(empty($phone) && empty($mobile)){
			$elements[] = $this->getPhoneNumberField();
		}
		
		return $elements;
	}
	
	public function isNFoisActivated(){
		return false;
	}
	
	protected function getHolderData(Customweb_BNPMercanet_Authorization_Transaction $transaction, array $formData){
		$billing = $transaction->getTransactionContext()->getOrderContext()->getBillingAddress();
		$parameters = array();
		
		$salutation = $transaction->getTransactionContext()->getOrderContext()->getBillingAddress()->getSalutation();
		$gender = Customweb_BNPMercanet_SalutationUtil::getGender($salutation);
		$salutationValue = null;
		if($gender == 'male'){
			$salutationValue = 'M';
		}
		elseif($gender == 'female'){
			$salutationValue= 'Mme';
		}
		else{
			if(!isset($formData['cw_salutation'])){
				throw new Customweb_I18n_LocalizableException(Customweb_I18n_Translation::__('Please provide the salutation.'));
			}
			$salutationValue= $formData['cw_salutation'];
		}		
		$parameters['holderContact.title'] = $salutationValue;
		$mobile = null;
		$phone = null;
		
		if(isset($formData['cw_phone'])){
			$phone = $formData['cw_phone'];
			$phone = preg_replace("/^\+{1}/", "00", $phone);
			$phone = preg_replace("/[^\d]/", "", $phone);
		}
		else{
			$phone = $transaction->getTransactionContext()->getOrderContext()->getBillingAddress()->getPhoneNumber();
			$phone = preg_replace("/^\+{1}/", "00", $phone);
			$phone = preg_replace("/[^\d]/", "", $phone);
			
			$mobile = $transaction->getTransactionContext()->getOrderContext()->getBillingAddress()->getMobilePhoneNumber();
			$mobile = preg_replace("/^\+{1}/", "00", $mobile);
			$mobile = preg_replace("/[^\d]/", "", $mobile);
		}
		if(empty($phone) && empty($mobile)){
			throw new Customweb_I18n_LocalizableException(Customweb_I18n_Translation::__('"Please enter your phone number."'));
		}
		if(!empty($phone)){
			$parameters['holderContact.phone'] = Customweb_Util_String::substrUtf8($phone, 0, 10);
		}
		if(!empty($mobile)){
			$parameters['holderContact.mobile'] = Customweb_Util_String::substrUtf8($mobile, 0, 10);
		}

		$parameters['holderContact.firstname'] = Customweb_Util_String::substrUtf8(preg_replace('/[^A-Za-z0-9 ]+/', '', $billing->getFirstName()), 0, 40);
		$parameters['holderContact.lastname'] = Customweb_Util_String::substrUtf8(preg_replace('/[^A-Za-z0-9 ]+/', '', $billing->getLastName()), 0, 40);
		$parameters['holderContact.email'] = Customweb_Util_String::substrUtf8($transaction->getTransactionContext()->getOrderContext()->getCustomerEMailAddress(), 0, 128);
		
		
		return $parameters;
	}
	
	
	private function getSalutationElement(){
		$control = new Customweb_Form_Control_Select('cw_salutation',
				array(
					"M" => Customweb_I18n_Translation::__("Mr."),
					"Mme" => Customweb_I18n_Translation::__("Ms."),
				));
		return new Customweb_Form_Element(Customweb_I18n_Translation::__("Salutation"), $control);
	}
	
	private function getPhoneNumberField(){
		$control = new Customweb_Form_Control_TextInput('cw_phone');
		$control->addValidator(new Customweb_Form_Validator_NotEmpty($control, Customweb_I18n_Translation::__("Please enter your phone number.")));
		return new Customweb_Form_Element(Customweb_I18n_Translation::__("Phone Number"), $control);
	}
	
	public function checkAcquirerResponseCode(){
		return false;
	}
}