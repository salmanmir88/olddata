<?php

namespace Evince\HomeProducts\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {

    protected $image;
    protected $storeManager;
    protected $categoryRepository;
    protected $assetRepos;
    protected $helperImageFactory;


    public function __construct(
        \Magento\Catalog\Helper\Image $image,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Magento\Framework\View\Asset\Repository $assetRepos,
        \Magento\Catalog\Helper\ImageFactory $helperImageFactory
    ) {
        $this->image = $image;
        $this->storeManager = $storeManager;
        $this->categoryRepository = $categoryRepository;
        $this->assetRepos = $assetRepos;
        $this->helperImageFactory = $helperImageFactory;
    }


    public function getProductImageUrl($product) {
        return $this->image->init($product, 'product_base_image')->constrainOnly(FALSE)
                        ->keepAspectRatio(TRUE)
                        ->keepFrame(FALSE)
                        ->getUrl();
    }
    
    public function getCategoryImage($categoryId) {
        
        $categoryIdElements = explode('-', $categoryId);
        $category           = $this->categoryRepository->get(end($categoryIdElements));
        return $category->getImageUrl();
        
    }
    
    public function getCurrentStore()
    {
        return $this->storeManager->getStore();
    }
    
    public function getStoreUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl();
    }

    public function getPlaceHolderImage()
    {
        $imagePlaceholder = $this->helperImageFactory->create();
        return $this->assetRepos->getUrl($imagePlaceholder->getPlaceholder('small_image'));
    }

}
