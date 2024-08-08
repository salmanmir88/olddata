<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Eextensions\General\Plugin\Customer\Ajax;

class Login
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context
    ) {
        $this->_request = $context->getRequest();
        $this->_response = $context->getResponse();
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->resultFactory = $context->getResultFactory();
    }

    public function aroundExecute(\Magento\Customer\Controller\Ajax\Login $subject, $proceed)
    {           
        $login =  json_decode($this->_request->getContent(),true);    
        
        //~ echo "hello ajax 123313";
        //~ pr($login);exit;
        
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
