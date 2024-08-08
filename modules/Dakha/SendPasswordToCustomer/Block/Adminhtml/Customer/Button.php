<?php

namespace Dakha\SendPasswordToCustomer\Block\Adminhtml\Customer;

use Magento\Backend\Block\Widget\Context;
use Magento\Customer\Block\Adminhtml\Edit\GenericButton;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Mageplaza\LoginAsCustomer\Helper\Data;
/**
 * Class Button
 * @package Mageplaza\LoginAsCustomer\Block\Adminhtml\Customer
 */
class Button extends GenericButton implements ButtonProviderInterface
{
    /**
     * @var Data
     */
    protected $_helper;

    /**
     * Button constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Data $helper
    ) {
        $this->_helper = $helper;

        parent::__construct($context, $registry);
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        $customerId = $this->getCustomerId();
        $data       = [];
        if ($customerId && $this->_helper->isAllowLogin()) {
            $data = [
                'label'      => __('Send Password To Customer'),
                'class'      => 'login-as-customer',
                'on_click'   => sprintf("location.href = '%s';", $this->getLoginUrl()),
                'sort_order' => 60,
            ];
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getLoginUrl()
    {
        return $this->getUrl('sendpasswordtocustomer/resendpass/index', ['id' => $this->getCustomerId()]);
    }
}
