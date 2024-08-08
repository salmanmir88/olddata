<?php
/**
 * @category  Apptrian
 * @package   Apptrian_FacebookPixel
 * @author    Apptrian
 * @copyright Copyright (c) Apptrian (http://www.apptrian.com)
 * @license   http://www.apptrian.com/license Proprietary Software License EULA
 */

namespace Apptrian\FacebookPixel\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;

class MatchingSection implements SectionSourceInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    public $customerSession;
    
    /**
     * @var \Apptrian\FacebookPixel\Helper\Data
     */
    public $helper;
    
    /**
     * Constructor.
     *
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Apptrian\FacebookPixel\Helper\Data $helper
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Apptrian\FacebookPixel\Helper\Data $helper
    ) {
        $this->customerSession = $customerSession;
        $this->helper = $helper;
    }
    
    /**
     * Get user/customer data.
     *
     * @return array
     */
    public function getSectionData()
    {
        $customerData = [];
        $customerId = $this->customerSession->getCustomerId();
        
        if (!$customerId) {
            $customerId = 0;
        }
        
        $customerData = $this->helper->getUserDataForClient($customerId);
        
        return [
            'matching_data' => $customerData,
        ];
    }
}
