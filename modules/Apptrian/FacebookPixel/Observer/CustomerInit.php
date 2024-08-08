<?php
/**
 * @category  Apptrian
 * @package   Apptrian_FacebookPixel
 * @author    Apptrian
 * @copyright Copyright (c) Apptrian (http://www.apptrian.com)
 * @license   http://www.apptrian.com/license Proprietary Software License EULA
 */
 
namespace Apptrian\FacebookPixel\Observer;

use Magento\Customer\Api\Data\CustomerInterface;

class CustomerInit implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Apptrian\FacebookPixel\Service\CurrentCustomer
     */
    public $currentCustomer;
    
    /**
     * Constructor.
     *
     * @param \Apptrian\FacebookPixel\Service\CurrentCustomer $currentCustomer
     */
    public function __construct(
        \Apptrian\FacebookPixel\Service\CurrentCustomer $currentCustomer
    ) {
        $this->currentCustomer = $currentCustomer;
    }
    
    /**
     * Execute method.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Apptrian\FacebookPixel\Observer\CustomerInit
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer        = null;
        $customerId      = 0;
        $customerSession = $observer->getEvent()->getCustomerSession();
        
        if ($customerSession) {
            $customer = $customerSession->getCustomer();
            if ($customer) {
                $customerId = $customer->getId();
                $this->currentCustomer->setCustomerId($customerId);
                $this->currentCustomer->setCustomer($customer);
            }
        }
        
        return $this;
    }
}
