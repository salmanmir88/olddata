<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Eextensions\General\Plugin\Customer;

class LoginPost
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context
    ) {
        $this->_request = $context->getRequest();
        $this->_response = $context->getResponse();
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->resultFactory = $context->getResultFactory();
    }

    public function aroundExecute(\Magento\Customer\Controller\Account\LoginPost $subject, $proceed)
    {           
		// echo "login post";die;
        $login =  $this->_request->getPost('login');    
        $returnValue = $proceed();            
		
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		//$cookieManager = $objectManager->get('Magento\Framework\Stdlib\CookieManagerInterface');
		$remembermeManager = $objectManager->get('Eextensions\General\Rememberme');
		if(isset($login['rememberme']) && $login['rememberme']){
			// set cookie value
			$remembermeManager->set($login['username']);
		}else{
			// set cookie value
			$remembermeManager->delete();
		}

        
        return $returnValue;
    }
}
