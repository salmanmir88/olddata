<?php
namespace Eextensions\Report\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Catalog data helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * ScopeConfigInterface scopeConfig
     *
     * @var scopeConfig
     */
    protected $scopeConfig;
    /**
     * @param CustomerSession $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
    }
	
	
}
