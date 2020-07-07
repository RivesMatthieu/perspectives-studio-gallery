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
require_once 'Customweb/Form/Button.php';
require_once 'Customweb/Form/Control/TextInput.php';



/**
 * @BackendForm
 */
final class Customweb_BNPMercanet_BackendOperation_Form_RiskManagement extends Customweb_Payment_BackendOperation_Form_Abstract {

	public static function getRiskManagementFields(){
		return array(
			'IpCountry' => array(
				'label' => Customweb_I18n_Translation::__('IP Country'),
				'desc' => Customweb_I18n_Translation::__('If an order total is below the specified amount, it will be processed without the IP Country check.'),
				'default' => '0'
			),
			'GreyIp' => array(
				'label' => Customweb_I18n_Translation::__('Grey IP'),
				'desc' => Customweb_I18n_Translation::__('If an order total is below the specified amount, it will be processed without the Grey IP check.'),
				'default' => '0'
			),
			'VelocityIp' => array(
				'label' => Customweb_I18n_Translation::__('Velocity IP'),
				'desc' => Customweb_I18n_Translation::__('If an order total is below the specified amount, it will be processed without the Velocity IP check.'),
				'default' => '0'
			),
			'SimilityIpCard' => array(
				'label' => Customweb_I18n_Translation::__('Simility IP Card'),
				'desc' => Customweb_I18n_Translation::__('If an order total is below the specified amount, it will be processed without the Simility IP Card check.'),
				'default' => '0'
			),
			'ForeignBinCard' => array(
				'label' => Customweb_I18n_Translation::__('Foreign Bin Card'),
				'desc' => Customweb_I18n_Translation::__('If an order total is below the specified amount, it will be processed without the Foreign Bin Card check.'),
				'default' => '0'
			),
			'VelocityCard' => array(
				'label' => Customweb_I18n_Translation::__('Velocity Card'),
				'desc' => Customweb_I18n_Translation::__('If an order total is below the specified amount, it will be processed without the Velocity Card check.'),
				'default' => '0'
			),
			'BlackCard' => array(
				'label' => Customweb_I18n_Translation::__('Black Card'),
				'desc' => Customweb_I18n_Translation::__('If an order total is below the specified amount, it will be processed without the Black Card check.'),
				'default' => '0'
			),
			'GreyCard' => array(
				'label' => Customweb_I18n_Translation::__('Grey Card'),
				'desc' => Customweb_I18n_Translation::__('If an order total is below the specified amount, it will be processed without the Grey Card check.'),
				'default' => '0'
			),
			'WhiteCard' => array(
				'label' => Customweb_I18n_Translation::__('White Card'),
				'desc' => Customweb_I18n_Translation::__('If an order total is below the specified amount, it will be processed without the White Card check.'),
				'default' => '0'
			),
			'ECard' => array(
				'label' => Customweb_I18n_Translation::__('ECard'),
				'desc' => Customweb_I18n_Translation::__('If an order total is below the specified amount, it will be processed without the ECard check.'),
				'default' => '0'
			),
			'SystematicAuthorizationCard' => array(
				'label' => Customweb_I18n_Translation::__('Systematic Authorization Card'),
				'desc' => Customweb_I18n_Translation::__('If an order total is below the specified amount, it will be processed without the Systematic Authorization Card check.'),
				'default' => '0'
			),
			'CorporateCard' => array(
				'label' => Customweb_I18n_Translation::__('Corporate Card'),
				'desc' => Customweb_I18n_Translation::__('If an order total is below the specified amount, it will be processed without the Corporate Card check.'),
				'default' => '0'
			),
			'VelocityCustomerId' => array(
				'label' => Customweb_I18n_Translation::__('Velocity Customer Id'),
				'desc' => Customweb_I18n_Translation::__('If an order total is below the specified amount, it will be processed without the Velocity Customer Id check.'),
				'default' => '0'
			),
			'MaxCustomerIdPerCard' => array(
				'label' => Customweb_I18n_Translation::__('Max CustomerId Per Card'),
				'desc' => Customweb_I18n_Translation::__('If an order total is below the specified amount, it will be processed without the Max CustomerId Per Card check.'),
				'default' => '0'
			),
			'MaxCardPerCustomerId' => array(
				'label' => Customweb_I18n_Translation::__('Max Card Per CustomerId'),
				'desc' => Customweb_I18n_Translation::__('If an order total is below the specified amount, it will be processed without the Max Card Per CustomerId check.'),
				'default' => '0'
			),
			'ExpiryDate' => array(
				'label' => Customweb_I18n_Translation::__('Expiry Date'),
				'desc' => Customweb_I18n_Translation::__('If an order total is below the specified amount, it will be processed without the Expiry Date check.'),
				'default' => '0'
			),
			
			
		
		);
	}

	public function isProcessable(){
		return true;
	}

	public function getTitle(){
		return Customweb_I18n_Translation::__("Risk Management");
	}

	public function getElementGroups(){
		$elementGroups = array();
		$elementGroups[] = $this->getRiskManagement();
		return $elementGroups;
	}

	private function getRiskManagement(){
		$riskManagement = new Customweb_Form_ElementGroup();
		$riskManagement->setTitle(Customweb_I18n_Translation::__('Risk Management'));
		foreach (self::getRiskManagementFields() as $key => $value) {
			$control = new Customweb_Form_Control_TextInput($key, $this->getPrefillValue($key, $value['default']));
			$element = new Customweb_Form_Element($value['label'], $control, $value['desc'], false, !$this->getSettingHandler()->hasCurrentStoreSetting($key));
			$riskManagement->addElement($element);
		}
		return $riskManagement;
	}


	private function getPrefillValue($key, $default){
		$stored = $this->getSettingValue($key);
		if ($stored === null) {
			return $default;
		}
		return $stored;
	}

	/**
	 *
	 * @return Customweb_Storage_IBackend
	 */
	private function getStorageBackend(){
		return $this->getContainer()->getBean('Customweb_Storage_IBackend');
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