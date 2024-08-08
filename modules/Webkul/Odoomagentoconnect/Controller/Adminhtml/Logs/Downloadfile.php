<?php
/**
 * Webkul Odoomagentoconnect Logs Downloadfile Controller
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
class Downloadfile extends \Magento\Backend\App\Action
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
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
    ) {
        $this->directoryList = $directoryList;
        $this->scopeConfig = $scopeConfig;
        $this->fileDriver = $fileDriver;
        $this->jsonHelper = $jsonHelper;
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
            if ($this->fileDriver->isExists($logfilePath) && filesize($logfilePath)) {
                $response = $this->getResponse();
                $response->setHeader('Content-Description: File Transfer', true);
                $response->setHeader('Content-Type: application/octet-stream', true);
                $response->setHeader('Content-Disposition: attachment; filename="'.basename($logfilePath).'"', true);
                $response->setHeader('Expires: 0', true);
                $response->setHeader('Cache-Control: must-revalidate', true);
                $response->setHeader('Pragma: public', true);
                $response->setHeader('Content-Length: ' . filesize($logfilePath), true);
                readfile($logfilePath);
                $this->getResponse()->setBody($this->jsonHelper->jsonEncode([]));
                $response->sendResponse();
                $this->messageManager->addSuccess(__("Synchronization log file '$logfile' has been downloaded successfully."));
            } elseif (!filesize($logfilePath)) {
                $this->messageManager->addError(__("Synchronization log file '$logfile' contains no logs to download."));
            } else {
                $this->messageManager->addError(__("Synchronization log file '$logfile' doesn't exists at Magento!!"));
            }
        } catch (\Exception $exception) {
            $this->messageManager->addError(__('Error during downloading Synchronization log file, '.$exception->getMessage()));
            ;
        }
    }
}
