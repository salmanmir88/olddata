<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StoreCredit
 */


namespace Amasty\StoreCredit\Controller\Index;

use Amasty\StoreCredit\Model\ConfigProvider;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;

class Index extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        Session $customerSession,
        Registry $registry,
        Context $context,
        ConfigProvider $configProvider
    ) {
        parent::__construct($context);
        $this->registry = $registry;
        $this->customerSession = $customerSession;
        $this->configProvider = $configProvider;
    }

    /**
     * @return $this|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$this->configProvider->isEnabled()) {
            return $this->_forward('noroute');
        }

        if ($customerId = $this->customerSession->getCustomerId()) {
            $this->registry->register(RegistryConstants::CURRENT_CUSTOMER_ID, $customerId);

            return $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
        } else {
            return $this->_redirect('customer/account/login');
        }
    }
}
