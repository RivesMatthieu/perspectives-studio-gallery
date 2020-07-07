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


abstract class Customweb_BNPMercanet_AbstractAdapter {
	
	
	private $container;
	

	public function __construct(Customweb_DependencyInjection_IContainer $container){

		$this->container = $container;
	}

	/**
	 * Returns the configuration object.
	 *
	 * @return Customweb_BNPMercanet_Configuration
	 */
	protected function getConfiguration(){
		return $this->getContainer()->getBean('Customweb_BNPMercanet_Configuration');
	}

	protected function getHelper(){
		return $this->getContainer()->getBean('Customweb_BNPMercanet_Helper');
	}

	/**
	 * 
	 * @return Customweb_DependencyInjection_IContainer
	 */
	protected function getContainer(){
		return $this->container;
	}
}