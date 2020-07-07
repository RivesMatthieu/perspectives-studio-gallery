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

require_once 'Customweb/Payment/BackendOperation/Form/Abstract.php';
require_once 'Customweb/Form/Element.php';
require_once 'Customweb/Form/ElementGroup.php';
require_once 'Customweb/I18n/Translation.php';
require_once 'Customweb/Form/IButton.php';
require_once 'Customweb/Form/Control/SingleCheckbox.php';
require_once 'Customweb/Form/Button.php';
require_once 'Customweb/Form/Control/TextInput.php';
require_once 'Customweb/BNPMercanet/Util.php';



/**
 * @BackendForm
 */
class Customweb_BNPMercanet_BackendOperation_Form_NFois extends Customweb_Payment_BackendOperation_Form_Abstract {
	const CONIFGURED_LIST_KEY = 'configuredPlans';
	const PAYMENT_PLAN_COUNT = 5;

	public function isProcessable(){
		return true;
	}

	public function getTitle(){
		return Customweb_I18n_Translation::__("Payment n-Fois");
	}

	public function getElementGroups(){
		$elemetGroups = $this->listAvailablePlans();
		return $elemetGroups;
	}

	private function listAvailablePlans(){
		$planGroups = array();
		
		for ($i = 0; $i < self::PAYMENT_PLAN_COUNT; $i++) {
			$id = 'plan' . $i;
			$group = new Customweb_Form_ElementGroup();
			$errors = array();
			if($this->getSettingValue($id.'_active') == 'yes') {
				$errors = Customweb_BNPMercanet_Util::getPlanErrors($this->getSettingValue($id . '_name'), $this->getSettingValue($id . '_min'), 
						$this->getSettingValue($id . '_max'), $this->getSettingValue($id . '_number'), $this->getSettingValue($id . '_period'), 
						$this->getSettingValue($id . '_first'));
				
			}
			
			$activeControl = new Customweb_Form_Control_SingleCheckbox($id . '_active', 'yes', Customweb_I18n_Translation::__('Yes'), $this->getSettingValue($id.'_active') == 'yes');
			$activeElement = new Customweb_Form_Element(Customweb_I18n_Translation::__('Active'), $activeControl, Customweb_I18n_Translation::__('Is this plan active for this store.'), false, !$this->getSettingHandler()->hasCurrentStoreSetting($id . '_active'));
			$activeElement->setRequired(false);
			
			$nameControl = new Customweb_Form_Control_TextInput($id . '_name', $this->getSettingValue($id . '_name'));
			$nameElement = new Customweb_Form_Element(Customweb_I18n_Translation::__('Name'), $nameControl, 
					Customweb_I18n_Translation::__('Name visible to the customer.'), false, 
					!$this->getSettingHandler()->hasCurrentStoreSetting($id . '_name'));
			$nameElement->setRequired(false);
			if (isset($errors['name'])) {
				$nameElement->setErrorMessage($errors['name']);
			}
			
			$minControl = new Customweb_Form_Control_TextInput($id . '_min', $this->getSettingValue($id . '_min'));
			$minElement = new Customweb_Form_Element(Customweb_I18n_Translation::__('Minimum Amount'), $minControl, 
					Customweb_I18n_Translation::__('Minimum order amount for this plan.'), false, 
					!$this->getSettingHandler()->hasCurrentStoreSetting($id . '_min'));
			$minElement->setRequired(false);
			if (isset($errors['min'])) {
				$minElement->setErrorMessage($errors['min']);
			}
			
			$maxControl = new Customweb_Form_Control_TextInput($id . '_max', $this->getSettingValue($id . '_max'));
			$maxElement = new Customweb_Form_Element(Customweb_I18n_Translation::__('Maximum Amount'), $maxControl, 
					Customweb_I18n_Translation::__('Maximum order amount for this plan.'), false, 
					!$this->getSettingHandler()->hasCurrentStoreSetting($id . '_max'));
			$maxElement->setRequired(false);
			
			if (isset($errors['max'])) {
				$maxElement->setErrorMessage($errors['max']);
			}
			
			$numberControl = new Customweb_Form_Control_TextInput($id . '_number', $this->getSettingValue($id . '_number'));
			$numberElement = new Customweb_Form_Element(Customweb_I18n_Translation::__('Number of payments'), $numberControl, 
					Customweb_I18n_Translation::__('The total number of payments (2 or more). Please contact BNP Paribas Mercanet if you wish to use more than 3 payments.'), false, 
					!$this->getSettingHandler()->hasCurrentStoreSetting($id . '_number'));
			$numberElement->setRequired(false);
			
			if (isset($errors['number'])) {
				$numberElement->setErrorMessage($errors['number']);
			}
			
			$periodControl = new Customweb_Form_Control_TextInput($id . '_period', $this->getSettingValue($id . '_period'));
			$periodElement = new Customweb_Form_Element(Customweb_I18n_Translation::__('Period'), $periodControl, 
					Customweb_I18n_Translation::__('The time between two payments in days.'), false, 
					!$this->getSettingHandler()->hasCurrentStoreSetting($id . '_period'));
			$periodElement->setRequired(false);
			if (isset($errors['period'])) {
				$periodElement->setErrorMessage($errors['period']);
			}
			
			$firstControl = new Customweb_Form_Control_TextInput($id . '_first', $this->getSettingValue($id . '_first'));
			$firstElement = new Customweb_Form_Element(Customweb_I18n_Translation::__('First'), $firstControl, 
					Customweb_I18n_Translation::__(
							'Percentage of the order amount which is charged with the first payment. The residual amount is distributed equally among the remaining payments.'), 
					false, !$this->getSettingHandler()->hasCurrentStoreSetting($id . '_first'));
			$firstElement->setRequired(false);
			if (isset($errors['first'])) {
				$firstElement->setErrorMessage($errors['first']);
			}
			
			$group->addElement($activeElement)->addElement($nameElement)->addElement($minElement)->addElement($maxElement)->addElement($numberElement)->addElement(
					$periodElement)->addElement($periodElement)->addElement($firstElement);
			$planGroups[] = $group;
		}
		return $planGroups;
	}

	public function getButtons(){
		return array(
			$this->getResetButton(),
			$this->getSaveButton(),			
		);
	}

	private function getResetButton(){
		$button = new Customweb_Form_Button();
		$button->setMachineName("reset")->setTitle(Customweb_I18n_Translation::__("Reset"))->setType(Customweb_Form_IButton::TYPE_CANCEL);
		return $button;
	}
	
	public function process(Customweb_Form_IButton $pressedButton, array $formData){
		if ($pressedButton->getMachineName() === 'save') {
			$this->getSettingHandler()->processForm($this, $formData);
		}
	}
}