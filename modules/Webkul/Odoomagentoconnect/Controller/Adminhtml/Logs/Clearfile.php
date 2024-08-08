<?php
/**
 * Webkul Odoomagentoconnect Logs Clearfile Controller
 *
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Odoomagentoconnect\Controller\Adminhtml\Logs;

/**
 * Webkul Odoomagentoconnect Logs Clearfile Controller class
 */
class Clearfile extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Backend\Model\View\Result\Forward
     */
    protected $_resultForwardFactory;

    /**
     * @param \Magento\Backend\App\Action\Context               $context
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     */
    public function __construct(
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
    ) {
        $this->fileDriver = $fileDriver;
        $this->directoryList = $directoryList;
        $this->scopeConfig = $scopeConfig;
        $this->_resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Odoomagentoconnect::synchronization_logs');
    }

    /**
     * Forward to edit
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $logDir = $this->directoryList->getPath('log');
        $logfile = $this->scopeConfig->getValue('odoomagentoconnect/additional/view_log');
        if (!$logfile) {
            $logfile = "odoo_connector.log";
        }
        try {
            $logfilePath = $logDir . DIRECTORY_SEPARATOR . $logfile;
            if ($this->fileDriver->isExists($logfilePath)) {
                $this->fileDriver->deleteFile($logfilePath);
                $this->fileDriver->touch($logfilePath);
                $this->messageManager->addSuccess(__("Log file $logfile cleared successfully."));
            } else {
                $this->messageManager->addError(__("Log file $logfile doesn't exists."));
            }
        } catch (\Exception $exception) {
            $this->messageManager->addError(__('Error during clearing logfile, '.$exception->getMessage()));
            ;
        }
    }
}
