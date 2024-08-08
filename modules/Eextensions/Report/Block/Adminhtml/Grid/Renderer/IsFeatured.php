<?php
namespace Eextensions\Report\Block\Adminhtml\Grid\Renderer;

class IsFeatured extends \Magento\Backend\Block\Widget\Grid\Column
{
    /**
     * Retrieve row column field value for display
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function getRowField(\Magento\Framework\DataObject $row)
    {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
        $productRepository = $objectManager->get('\Magento\Catalog\Model\ProductRepository');
        try {
            if($row->getOrderItemsSku()){
             $productObj = $productRepository->get($row->getOrderItemsSku());
             $row->setOrderItemsIsFeatured($productObj->getIsFeatured());
            }
        } catch (\Exception $e) {
            
        }

        $renderedValue = $this->getRenderer()->render($row);
        if ($this->getHtmlDecorators()) {
            $renderedValue = $this->_applyDecorators($renderedValue, $this->getHtmlDecorators());
        }

        /*
         * if column has determined callback for framing call
         * it before give away rendered value
         *
         * callback_function($renderedValue, $row, $column, $isExport)
         * should return new version of rendered value
         */
        $frameCallback = $this->getFrameCallback();
        if (is_array($frameCallback)) {
            $this->validateFrameCallback($frameCallback);
            $renderedValue = call_user_func($frameCallback, $renderedValue, $row, $this, false);
        }

        return $renderedValue;
    }
}