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
require_once 'BNPMercanetCw/Util.php';
require_once 'BNPMercanetCw/ContextRequest.php';
require_once 'BNPMercanetCw/Controller/Abstract.php';
require_once 'BNPMercanetCw/Entity/ExternalCheckoutContext.php';



/**
 *
 * @author Nico Eigenmann
 *
 */
class BNPMercanetCw_Controller_ExternalLogin extends BNPMercanetCw_Controller_Abstract {


	public function indexAction(){
		$GLOBALS['woo_bnpmercanetcwTitle'] = __('Login / Register' , 'woocommerce_bnpmercanetcw');
		
		$parameters = BNPMercanetCw_ContextRequest::getInstance()->getParameters();
		$context = BNPMercanetCw_Entity_ExternalCheckoutContext::getContextById($parameters['bnpmercanetcw-context-id']);
		try{
			BNPMercanetCw_Util::checkToken($context, $parameters);
		}
		catch(Exception $e){
			$service = BNPMercanetCw_Util::createContainer()->getBean('BNPMercanetCw_ExternalCheckoutService');
			$service->markContextAsFailed($context, __('Token expired', 'woocommerce_bnpmercanetcw'));
		}
		if($context->getState() != BNPMercanetCw_Entity_ExternalCheckoutContext::STATE_PENDING) {
			wp_redirect($context->getCartUrl());
		}
		$errorMessage = '';
		
		$displayGuest = !WC_Checkout::instance()->is_registration_required();
			
		$emailAddress = $context->getAuthenticationEmailAddress();
		$displayRegister = get_option('users_can_register');
		
		if (isset($parameters['checkout-register']) && isset($parameters['checkout-login'])) {
			//Template Error, or other stuff
			$errorMessage = __('You can not submit both forms at once', 'woocommerce_bnpmercanetcw');
		}
		elseif (isset($parameters['checkout-register'])) {
			if ($displayGuest && !isset($parameters['register-create-account'])) {
				//Guest checkout, no account creation
				if (isset($parameters['register-email']) && is_email($parameters['register-email'])) {
					$context->setCustomerEmailAddress($parameters['register-email']);
					BNPMercanetCw_Util::getEntityManager()->persist($context);
					wp_redirect($context->getAuthenticationSuccessUrl());
					die();
				}
				else {
					$errorMessage = __('Please provide a valid email address', 'woocommerce');
				}
			}
			else {
				$emailAddress = isset($parameters['register-email']) ? $parameters['register-email'] : '';
				$password = isset($parameters['register-password']) ? $parameters['register-password'] : '';
				try {
					$userId = BNPMercanetCw_Util::createUser($emailAddress, $password);
					wc_set_customer_auth_cookie($userId);
					$context->setCustomerEmailAddress($emailAddress);
					$context->setCustomerId($userId);
					BNPMercanetCw_Util::getEntityManager()->persist($context);
					wp_redirect($context->getAuthenticationSuccessUrl());
					die();
				}
				catch (Exception $e) {
					$errorMessage = $e->getMessage();
				}
			}
		}
		else if (isset($parameters['checkout-login'])) {
			$emailAddress = isset($parameters['login-email']) ? $parameters['login-email'] : '';
			$password = isset($parameters['login-password']) ? $parameters['login-password'] : '';
			if (empty($emailAddress)) {
				$errorMessage = __('The email address field was empty', 'woocommerce_bnpmercanetcw');
			}
			elseif (empty($password)) {
				$errorMessage = __('The password field was empty', 'woocommerce_bnpmercanetcw');
			}
			else {
				$secureCookie = false;
				/*@var $user WP_User */
				$tmpUser = false;
				if (is_email($emailAddress)) {
					$tmpUser = get_user_by('email', $emailAddress);
				}
				else {
					$userName = sanitize_user($emailAddress);
					$tmpUser = get_user_by('login', $userName);
				}
				if ($tmpUser !== false && !force_ssl_admin()) {
					if (get_user_option('use_ssl', $tmpUser->ID)) {
						$secureCookie = true;
						force_ssl_admin(true);
					}
				}
				if ($tmpUser !== false) {
					$credentials = array(
						'user_login' => $tmpUser->user_login,
						'user_password' => $password,
						'rememberme' => false 
					);
					
					$user = wp_signon($credentials, $secureCookie);
					if (is_wp_error($user)) {
						$errorMessage = $user->get_error_message();
					}
					else {
						$context->setCustomerEmailAddress($user->get('user_email'));
						$context->setCustomerId($user->ID);
						BNPMercanetCw_Util::getEntityManager()->persist($context);
						wp_redirect($context->getAuthenticationSuccessUrl());
						die();
					}
				}
				else {
					$errorMessage = __('Invalid Email/Username', 'woocommerce_bnpmercanetcw');
				}
			}
		}
		ob_start();
		BNPMercanetCw_Util::includeTemplateFile('external_login', 
				array(
					'errorMessage' => $errorMessage,
					'displayGuest' => $displayGuest,
					'displayRegister' => $displayRegister,
					'email' => $emailAddress,
					'url' => BNPMercanetCw_Util::getPluginUrl('externalLogin', 
							array(
								'bnpmercanetcw-context-id' => $context->getContextId(),
								'token' => $context->getSecurityToken() 
							)) 
				));
		$content = ob_get_clean();
		return $content;
	}
	
}