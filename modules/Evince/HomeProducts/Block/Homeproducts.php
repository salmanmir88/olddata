<?php

namespace Evince\HomeProducts\Block;

class Homeproducts extends \Magento\Framework\View\Element\Template {

    protected $productCollectionFactory;
    protected $categoryCollection;
    protected $storeManager;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Block\Product\ListProduct $addToCartPostParams,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory  $productCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->productCollectionFactory = $productCollectionFactory;
        $this->categoryCollection = $categoryCollection;
        $this->storeManager = $storeManager;
        $this->addToCartPostParams = $addToCartPostParams;
    }

    public function getBestSellerProducts() {

        $productcollection = $this->productCollectionFactory->create();
                $productcollection->addAttributeToSelect('*');
                $productcollection->addAttributeToFilter('best_seller', '1');
                return $productcollection;
    }
    
    public function getNewArrivalProducts() {

        $productcollection = $this->productCollectionFactory->create();
                $productcollection->addAttributeToSelect('*');
                $productcollection->addAttributeToFilter('new_arrival', '1');
                return $productcollection;
    }
    
    public function getHomePageCategoryCollection()
    {
        $collection = $this->categoryCollection->create()
            ->addAttributeToSelect('*')
            ->setStore($this->storeManager->getStore())
            ->addAttributeToFilter('show_on_homepage','1');
        return $collection;
    }
    
    public function getFeaturedCategory()
    {
        $collection = $this->categoryCollection->create()
            ->addAttributeToSelect('*')
            ->setStore($this->storeManager->getStore())
            ->addAttributeToFilter('is_featured','1')->getFirstItem();
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
        if ($priceRender) {
            $price = $priceRender->render(
                    \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE, $product, $arguments
            );
        }
        return $price;
    }

    public function getAddToCartParams(\Magento\Catalog\Model\Product $product)
    {
       return $this->addToCartPostParams->getAddToCartPostParams($product);
    } 

}
