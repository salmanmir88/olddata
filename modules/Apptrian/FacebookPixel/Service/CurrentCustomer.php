<?php
/**
 * @category  Apptrian
 * @package   Apptrian_FacebookPixel
 * @author    Apptrian
 * @copyright Copyright (c) Apptrian (http://www.apptrian.com)
 * @license   http://www.apptrian.com/license Proprietary Software License EULA
 */
 
namespace Apptrian\FacebookPixel\Service;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;

class CurrentCustomer
{
    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface
     */
    public $customer;
    
    /**
     * @var \Magento\Customer\Api\Data\CustomerInterfaceFactory
     */
    public $customerFactory;

    /**
     * @var int|string
     */
    public $customerId;
    
    /**
     * Constructor.
     *
     * @param \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory
     */
    public function __construct(
        CustomerInterfaceFactory $customerFactory
    ) {
        $this->customerFactory = $customerFactory;
    }

    /**
     * Sets customer interface.
     *
     * @param CustomerInterface $customer
     */
    public function set(CustomerInterface $customer)
    {
        $this->customer = $customer;
    }

    /**
     * Gets customer Interface.
     *
     * @return CustomerInterface
     */
    public function get()
    {
        return isset($this->customer) ? $this->customer : $this->createNullCustomer();
    }
    
    /**
     * Returns empty customer interface object.
     *
     * @return CustomerInterface
     */
    public function createNullCustomer()
    {
        return $this->customerFactory->create();
    }
    
    /**
     * Returns customer.
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function getCustomer()
    {
        return $this->customer;
    }
    
    /**
     * Sets customer.
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
    }
    
    /**
     * Returns customer id.
     *
     * @return int|string
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }
    
    /**
     * Sets customer id.
     *
     * @param int|string $id
     */
    public function setCustomerId($id)
    {
        $this->customerId = $id;
    }
}
