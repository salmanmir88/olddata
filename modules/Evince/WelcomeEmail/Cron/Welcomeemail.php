<?php
namespace Evince\WelcomeEmail\Cron;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Welcomeemail {

    protected $storeManager;
    protected $scopeConfig;
    protected $transportBuilder;
    protected $resourceConnection;
    protected $logger;

    public function __construct(
        \Magento\Framework\App\Action\Context $context, 
        \Magento\Store\Model\StoreManagerInterface $storeManager, 
        ScopeConfigInterface $scopeConfig, 
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder, 
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation, 
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
        //parent::__construct($context);
    }

    public function execute() {

        try {
            $connection = $this->resourceConnection->getConnection();
            $getMigrateCustomerQuery = " SELECT `email`, `firstname` FROM `customer_entity` WHERE `migrate_customer` = 1 ";
            $customers = $connection->fetchAll($getMigrateCustomerQuery);
            //echo "<pre>"; print_r($customers); echo "</pre>"; exit;
            $storeId = $this->storeManager->getStore()->getId();
            $fromEmail = $this->scopeConfig->getValue('trans_email/ident_support/email', ScopeInterface::SCOPE_STORE);
            $fromName = $this->scopeConfig->getValue('trans_email/ident_support/name', ScopeInterface::SCOPE_STORE);
            $from = ['email' => $fromEmail, 'name' => $fromName];

            $templateOptions = [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId
            ];
            
            $templateId = $this->scopeConfig->getValue('kpopia/welcome/email_template', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
            foreach ($customers as $customer)
            {
                $templateVars = [];
                $to = $customer['email'];
                
                $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
                    ->setTemplateOptions($templateOptions)
                    ->setTemplateVars($templateVars)
                    ->setFrom($from)
                    ->addTo($to)
                    ->getTransport();
                $transport->sendMessage();
                $this->inlineTranslation->resume();
                $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/welcomecron.log');
                $logger = new \Zend\Log\Logger();
                $logger->addWriter($writer);
                $logger->info($to);
            }
        } catch (\Exception $e) {
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/welcomeemailerror.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info($e->getMessage());
        }
    }

}
