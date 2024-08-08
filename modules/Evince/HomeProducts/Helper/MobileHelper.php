<?php

namespace Evince\HomeProducts\Helper;
class MobileHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
	protected $mobileAgent;
	protected $httpHeader;	

	public function __construct
	(
    	\Zend_Http_UserAgent_Mobile $mobileAgent,
    	\Magento\Framework\HTTP\Header $httpHeader
	)
	{
		$this->mobileAgent = $mobileAgent;
		$this->httpHeader = $httpHeader;
	}

	public function isMobile()
	{
    	$userAgent = $this->httpHeader->getHttpUserAgent();
    	return $this->mobileAgent->match($userAgent, $_SERVER);
	}
}