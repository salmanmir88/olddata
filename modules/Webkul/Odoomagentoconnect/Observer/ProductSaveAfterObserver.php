<?php
/**
 * Webkul Odoomagentoconnect SalesOrderPlaceAfterObserver Observer Model
 *
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Odoomagentoconnect\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Webkul Odoomagentoconnect ProductSaveAfterObserver Class
 */
class ProductSaveAfterObserver implements ObserverInterface
{
    public function __construct(
        \Magento\Framework\App\RequestInterface $requestInterface,
        \Webkul\Odoomagentoconnect\Helper\Connection $connection,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Template $templateMapping,
        \Webkul\Odoomagentoconnect\Model\Template $templateModel,
        \Webkul\Odoomagentoconnect\Model\Product $productModel,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableModel,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Product $productMapping
    ) {
        $this->_requestInterface = $requestInterface;
        $this->_templateMapping = $templateMapping;
        $this->_templateModel = $templateModel;
        $this->_configurableModel = $configurableModel;
        $this->_productModel = $productModel;
        $this->_productMapping = $productMapping;
        $this->_connection = $connection;
    }

    /**
     * Product Save After event handler
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $route = $this->_requestInterface->getControllerName();
        $product = $observer->getEvent()->getProduct();
        $proId = $product->getId();
        $mappingObj = 0;
        $templateObj = $this->_templateMapping;
        $variantModel = $this->_productMapping;
        if ($proId && $route == 'product') {
            $sku = $product->getSku();
            if (!$sku) {
                return true;
            }
            $helper = $this->_connection;
            $helper->getSocketConnect();
            $userId = $helper->getSession()->getUserId();

            $autoSync = $helper->getStoreConfig('odoomagentoconnect/automatization_settings/auto_product');
            if ($product->getTypeID() == 'configurable') {
                $collection = $this->_templateModel
                    ->getCollection()
                    ->addFieldToFilter('magento_id', ['eq'=>$proId]);
                foreach ($collection as $map) {
                    $mappingObj = $map;
                }
                if (!$autoSync && $mappingObj) {
                    $templateObj->updateMapping($mappingObj, 'yes');
                }
                if ($userId > 0 && $autoSync) {
                    $templateObj->syncConfigurableProduct($mappingObj, $proId);
                }
            } else {
                $parentIds = $this->_configurableModel
                    ->getParentIdsByChild($proId);
                $collection = $this->_productModel
                    ->getCollection()
                    ->addFieldToFilter('magento_id', ['eq'=>$proId]);
                foreach ($collection as $map) {
                    $mappingObj = $map;
                }
                if (!$autoSync && $mappingObj) {
                    $variantModel->updateMapping($mappingObj, 'yes');
                }
                if ($userId > 0 && $autoSync) {
                    $visibility = $observer->getEvent()->getProduct()->getVisibility();
                    $variantModel->syncSimpleProduct($visibility, $parentIds, $mappingObj, $proId);
                }
            }
        }
    }
}
