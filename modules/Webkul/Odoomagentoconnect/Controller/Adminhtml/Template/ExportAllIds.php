<?php
/**
 * Webkul Odoomagentoconnect Template ExportAllIds Controller
 *
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Odoomagentoconnect\Controller\Adminhtml\Template;

/**
 * Webkul Odoomagentoconnect Template ExportAllIds Controller class
 */
class ExportAllIds extends \Magento\Backend\App\Action
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
        \Webkul\Odoomagentoconnect\Helper\Connection $connection,
        \Webkul\Odoomagentoconnect\Model\Template $templateMapping,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
    ) {
    
        $this->_connection = $connection;
        $this->_templateMapping = $templateMapping;
        $this->_productModel = $productModel;
        $this->_resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Odoomagentoconnect::product_save');
    }

    /**
     * Forward to edit
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $exportIds = [];
        $helper = $this->_connection;
        $helper->getSocketConnect();
        $userId = $helper->getSession()->getUserId();
        if ($userId) {
            $mapTemplates = [];
            $templateCollection = $this->_templateMapping->getCollection()
                ->addFieldToSelect('magento_id')->getData();
            foreach ($templateCollection as $value) {
                    array_push($mapTemplates, $value['magento_id']);
            }
            if ($mapTemplates) {
                $collection = $this->_productModel->getCollection()
                    ->addAttributeToFilter('entity_id', ['nin' => $mapTemplates])
                    ->addAttributeToFilter('type_id', ['eq' => 'configurable'])
                    ->addAttributeToSelect('entity_id');
            } else {
                $collection = $this->_productModel->getCollection()
                    ->addAttributeToFilter('type_id', ['eq' => 'configurable'])
                    ->addAttributeToSelect('entity_id');
            }
            $exportIds = $collection->getAllIds();
            if (count($collection) == 0) {
                $this->messageManager->addSuccess(__("No Products are exist at Magento."));
            } else {
                if (count($exportIds) == 0) {
                    $this->messageManager
                        ->addSuccess(__("All Magento Configurable Products are already exported at Odoo."));
                }
            }
        } else {
            $errorMessage = $helper->getSession()->getErrorMessage();
            $this->messageManager->addError(
                __(
                    "Configurable Products(s) have not been Exported at Odoo !! Reason : ".$errorMessage
                )
            );
        }
        $this->getResponse()->clearHeaders()->setHeader('content-type', 'application/json', true);
        $this->getResponse()->setBody(json_encode($exportIds));
    }
}
