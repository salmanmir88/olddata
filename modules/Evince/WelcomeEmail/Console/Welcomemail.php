<?php

namespace Evince\WelcomeEmail\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Console\Cli;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Welcomemail extends Command {

    protected $storeManager;
    protected $scopeConfig;
    protected $transportBuilder;
    protected $resourceConnection;
    protected $logger;
    protected $state;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager, 
        ScopeConfigInterface $scopeConfig, 
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder, 
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation, 
        \Magento\Framework\App\ResourceConnection $resourceConnection, 
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\State $state
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
        $this->state = $state;
        parent::__construct();
    }

    protected function configure() {
        $this->setName('kpopiashop:welcomeemail');
        $this->setDescription('Welcome email');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        try {
            
            $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
            $connection = $this->resourceConnection->getConnection();
//            $getMigrateCustomerQuery = " SELECT `email`, `firstname` FROM `customer_entity` WHERE `migrate_customer` = 1 LIMIT 25447,3100";
//            $customers = $connection->fetchAll($getMigrateCustomerQuery);
            //echo "<pre>";print_r($customers);echo "</pre>"; exit;
            $customers = array();
            
            $storeId = $this->storeManager->getStore()->getId();
            $fromEmail = $this->scopeConfig->getValue('trans_email/ident_support/email', ScopeInterface::SCOPE_STORE);
            $fromName = $this->scopeConfig->getValue('trans_email/ident_support/name', ScopeInterface::SCOPE_STORE);
            $from = ['email' => $fromEmail, 'name' => $fromName];

            $templateOptions = [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId
            ];

            $templateId = $this->scopeConfig->getValue('kpopia/welcome/email_template', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
            foreach ($customers as $customer) {
                $output->writeln($customer);
                $templateVars = [];
                $to = $customer;

                $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
                        ->setTemplateOptions($templateOptions)
                        ->setTemplateVars($templateVars)
                        ->setFrom($from)
                        ->addTo($to)
                        ->getTransport();
                $transport->sendMessage();
                $this->inlineTranslation->resume();
                
                $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/welcome99.log');
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
        return Cli::RETURN_SUCCESS;
    }

}
