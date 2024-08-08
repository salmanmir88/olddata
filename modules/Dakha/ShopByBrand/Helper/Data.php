<?php
/**
 * Copyright © ShopByBrand All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dakha\ShopByBrand\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
   /**
    * @var \Magento\Framework\Registry
    */
   protected $_registry;
    
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Registry $_registry
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $_registry,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory 
    ) {
        $this->_registry = $_registry;
        $this->_productCollectionFactory = $productCollectionFactory;
        parent::__construct($context);
    }

   /**
    * @return string  
    */
   public function getCurrentCategory()
    {        
        return $this->_registry->registry('current_category');
    }

   /**
    * @return string  
    */  
   public function getProductCollectionByCategories()
    {
        $categoryId = $this->getCurrentCategory()->getId();
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect(array('manufacturer'));
        $collection->addCategoriesFilter(['in' => $categoryId]);

        $brand = [];
        foreach($collection as $product)
        {
           $optionId = $product->getManufacturer();  
           $attr = $product->getResource()->getAttribute('manufacturer');
           if ($attr->usesSource()) {
                   $brand[] = $attr->getSource()->getOptionText($optionId);
           }
        }
        
        return array_unique($brand);
    } 
}