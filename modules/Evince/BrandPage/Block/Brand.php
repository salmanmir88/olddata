<?php

namespace Evince\BrandPage\Block;

class Brand extends \Magento\Framework\View\Element\Template
{
    protected $categoryCollection;
    protected $categoryFactory;
    protected $storeManager;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,         
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->categoryCollection = $categoryCollection;
        $this->categoryFactory = $categoryFactory;
        $this->storeManager = $storeManager;
    }
    
    public function getBrandCategoryImg()
    {
        $collection = $this->categoryCollection->create()
            ->addAttributeToSelect('*')
            ->setStore($this->storeManager->getStore())
            ->addAttributeToFilter('show_on_brandpage','1')->getFirstItem();
        return $collection;
    }
    
//    public function getBrandCategory()
//    {
//        $collection = $this->categoryCollection->create()
//            ->addAttributeToSelect('*')
//            ->setStore($this->storeManager->getStore())
//            ->addAttributeToFilter('show_on_brandpage','1');
//        return $collection;
//    }
    
    public function getBt21Category()
    {
        $categoryId = 36; // YOUR CATEGORY ID
        $category = $this->categoryFactory->create()->load($categoryId);
        $childernCategories = $category->getChildrenCategories();
        
        $collection = $this->categoryCollection->create()
           ->addAttributeToSelect('*')
           ->setStore($this->storeManager->getStore())
           ->addAttributeToFilter('show_on_brandpage','1')
           ->addAttributeToFilter('entity_id', array('in' => $childernCategories->getAllIds(),));
        return $collection;

    }

    public function getProductPriceHtml(
    \Magento\Catalog\Model\Product $product, $priceType = null, $renderZone = \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST, array $arguments = []
    ) {
        if (!isset($arguments['zone'])) {
            $arguments['zone'] = $renderZone;
        }
        $arguments['zone'] = isset($arguments['zone']) ? $arguments['zone'] : $renderZone;
        $arguments['price_id'] = isset($arguments['price_id']) ? $arguments['price_id'] : 'old-price-' . $product->getId() . '-' . $priceType;
        $arguments['include_container'] = isset($arguments['include_container']) ? $arguments['include_container'] : true;
        $arguments['display_minimal_price'] = isset($arguments['display_minimal_price']) ? $arguments['display_minimal_price'] : true;

        /** @var \Magento\Framework\Pricing\Render $priceRender */
        $priceRender = $this->getLayout()->getBlock('product.price.render.default');

        $price = '';
        if ($priceRender) 
        {
            $price = $priceRender->render(
                    \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE, $product, $arguments
            );
        }
        return $price;
    }
    
}