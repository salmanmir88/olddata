<?php /** Product collection filter by custom attribute **/

namespace Eextensions\General\Block;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory as BestSellersCollectionFactory;


class BestCategoryProducts extends \Magento\Catalog\Block\Product\AbstractProduct {

    /**
     * Default toolbar block name
     *
     * @var string
     */
    protected $_defaultToolbarBlock = 'Magento\Catalog\Block\Product\ProductList\Toolbar';

    /**
     * Product Collection
     *
     * @var AbstractCollection
     */
    protected $_productCollection;

    /**
     * Catalog layer
     *
     * @var \Magento\Catalog\Model\Layer
     */
    protected $_catalogLayer;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    protected $_postDataHelper;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $urlHelper;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;
    protected $productCollectionFactory;
    protected $storeManager;
    protected $catalogConfig;
    protected $productVisibility;
    protected $scopeConfig;

    protected $_registry;

    /**
     * @var BestSellersCollectionFactory
     */
    protected $_bestSellersCollectionFactory;


    /**
     * @param Context $context
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Framework\Registry $registry,
        BestSellersCollectionFactory $bestSellersCollectionFactory,
        array $data = []
    ) {
        $this->_catalogLayer = $layerResolver->get();
        $this->_postDataHelper = $postDataHelper;
        $this->categoryRepository = $categoryRepository;
        $this->urlHelper = $urlHelper;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->storeManager = $context->getStoreManager();
        $this->catalogConfig = $context->getCatalogConfig();
        $this->productVisibility = $productVisibility;
        $this->_registry = $registry;
        $this->_bestSellersCollectionFactory = $bestSellersCollectionFactory;
        parent::__construct(
            $context,
            $data
        );
    }
    public function getProducts()
    {
        return $this->getBestSellerProducts();
    }
    
    public function getTitle()
    {
        if (!$this->hasData('title')) {
            $this->setData('title', self::DISPLAY_TYPE_ALL_PRODUCTS);
        }
        return $this->getData('title');
    }
    
    public function getDescription()
    {
        if (!$this->hasData('description')) {
            $this->setData('description', self::DISPLAY_TYPE_ALL_PRODUCTS);
        }
        return $this->getData('description');
    }
    
    public function getPageSize()
    {
        if (!$this->hasData('page_size')) {
            $this->setData('page_size', self::DISPLAY_TYPE_ALL_PRODUCTS);
        }
        return $this->getData('page_size');
    }
    
    public function getConfig($att) 
    {
        $path = 'featuredproduct/featuredproduct_config/' . $att;
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    function cut_string_featuredproduct($string,$number){
        if(strlen($string) <= $number) {
            return $string;
        }
        else {  
            if(strpos($string," ",$number) > $number){
                $new_space = strpos($string," ",$number);
                $new_string = substr($string,0,$new_space)."..";
                return $new_string;
            }
            $new_string = substr($string,0,$number)."..";
            return $new_string;
        }
    }
    
    public function getAddToCartPostParams(\Magento\Catalog\Model\Product $product)
    {
        $url = $this->getAddToCartUrl($product);
        return [
            'action' => $url,
            'data' => [
                'product' => $product->getEntityId(),
                \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED =>
                    $this->urlHelper->getEncodedUrl($url),
            ]
        ];
    }
    public function getProductData($pId = '')
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product = $objectManager->create('Magento\Catalog\Model\Product')->load($pId);
        return $product;
    }

    public function getCurrentCategory()
    {
        if($this->_registry->registry('current_category'))
        {
            return $this->_registry->registry('current_category')->getId();
        }
        return ;
    }

    public function getBestSellerProducts()
    {
        $bestProductIds = $this->getBestSellerProductIds();
        $categoryProductIds = $this->getProductCollectionByCategories($this->getCategoryIds());
        $productIds = array_intersect($categoryProductIds, $bestProductIds);
        if (empty($productIds)) {
            $productIds = $categoryProductIds;
        }
        $collection = $this->getProductCollectionByIds($productIds);
        return $collection;
    }

    protected function getBestSellerProductIds()
    {
        return $this->_bestSellersCollectionFactory->create()->setPeriod('month')->getColumnValues('product_id');
        /*foreach ($bestSellers as $product) {
            $bestProductIds[] = $product->getProductId();
        }*/
        //return $bestProductIds;
    }

    protected function getProductCollectionByIds($productIds)
    {
        $collection = $this->productCollectionFactory->create()
            ->addIdFilter($productIds)
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect('*')
            ->addStoreFilter($this->getStoreId())
            ->setPageSize(10)
            ->setCurPage(1);
        return $collection;
    }


    public function getProductCollectionByCategories($ids)
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect(['entity_id']);
        $collection->addCategoriesFilter(['in' => $ids]);
        return $collection->getColumnValues('entity_id');
    }

    public function getCategoryIds()
    {
        return $this->getData('category_ids');
    }
}
