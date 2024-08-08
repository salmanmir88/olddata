<?php


namespace Dakha\SendPasswordToCustomer\Controller\Adminhtml\ResendPass;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Url;
use Mageplaza\LoginAsCustomer\Helper\Data;

/**
 * Class Index
 * @package Dakha\SendPasswordToCustomer\Controller\Adminhtml\Login
 */
class Index extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Dakha_SendPasswordToCustomer::allow';

    /**
     * @var CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var Data
     */
    protected $_loginHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

     /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    const XML_PATH_EMAIL_RECIPIENT_NAME = 'trans_email/ident_support/name';

    const XML_PATH_EMAIL_RECIPIENT_EMAIL = 'trans_email/ident_support/email';

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $_inlineTranslation;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Framework\Encryption\Encryptor
     */
    protected $encryptor;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * @param \Psr\Log\LoggerInterface
     */
    private $logger;


    /**
     * Index constructor.
     *
     * @param Context $context
     * @param CustomerFactory $customerFactory
     * @param Data $helper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Encryption\Encryptor $encryptor
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
     */
    public function __construct(
        Context $context,
        CustomerFactory $customerFactory,
        Data $helper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Encryption\Encryptor $encryptor,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_customerFactory = $customerFactory;
        $this->_loginHelper     = $helper;
        $this->scopeConfig        = $scopeConfig;
        $this->_inlineTranslation = $inlineTranslation;
        $this->_transportBuilder  = $transportBuilder;
        $this->storeManager       = $storeManager;
        $this->encryptor          = $encryptor;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->logger = $logger;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function execute()
    {
        if (!$this->_loginHelper->isEnabled()) {
            $this->messageManager->addErrorMessage(__('Module is not enabled.'));

            return $this->_redirect('customer');
        }
        
        $customerId = $this->getRequest()->getParam('id');
        $customer   = $this->_customerFactory->create()->load($customerId);
        $this->sendEmailPasswordToCustomer($customer);
        if (!$customer || !$customer->getId()) {
            $this->messageManager->addErrorMessage(__('Customer does not exist.'));

            return $this->_redirect('customer');
        }

        return $this->_redirect('customer/index/edit/id/'.$customer->getId());
    }
    
    /**
     * @return string
     * @param customer
     */
    public function sendEmailPasswordToCustomer($customer)
    {
        try {
                $password = $this->generatePassword();
                $customerLoad = $this->customerRepositoryInterface->getById($customer->getId());
                $this->customerRepositoryInterface->save($customerLoad, $this->encryptor->getHash($password, true));

                $this->_inlineTranslation->suspend();
                $sentToEmail = $this->scopeConfig->getValue('trans_email/ident_general/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $sentToName  = $this->scopeConfig->getValue('trans_email/ident_general/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

                $sender = [
                    'name' => $sentToName,
                    'email' => $sentToEmail,
                ];

                $transport = $this->_transportBuilder->setTemplateIdentifier('send_password_to_customer')
                    ->setTemplateOptions(
                        [
                            'area' => 'frontend',
                            'store' => $customer->getStoreId()
                        ]
                    )
                    ->setTemplateVars([
                        'name' => $customer->getFirstname(),
                        'email' => $customer->getEmail(),
                        'password' =>$password
                    ])
                    ->setFromByScope($sender)
                    ->addTo($customer->getEmail(), $customer->getFirstname())
                    ->getTransport();
                $transport->sendMessage();
                $this->_inlineTranslation->resume();
                $this->messageManager->addSuccess(__('Successfully send password to customer')); 
        } catch (Exception $e) {
                $this->messageManager->addErrorMessage(__($e->getMessage()));
        }
    }

    /**
     * @return string
     */
    public function generatePassword()
    {
      $chars =  'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.
            '0123456789`-=~!@#$%^&*()_+,./<>?;:[]{}\|';
      $length = 20;
      $str = '';
      $max = strlen($chars) - 1;

      for ($i=0; $i < $length; $i++)
        $str .= $chars[mt_rand(0, $max)];

      return $str;
    }
}
