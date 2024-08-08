<?php

namespace Meetanshi\GoogleSitemap\Block;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;
use Meetanshi\GoogleSitemap\Helper\Data as GoogleSitemapHelper;
use Meetanshi\GoogleSitemap\Model\Config\Source\CmsPages;
use Meetanshi\GoogleSitemap\Model\Sitemap;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Htmlsitemap
 * @package Meetanshi\GoogleSitemap\Block
 */
class Htmlsitemap extends Template
{

    /**
     * @var GoogleSitemapHelper
     */
    private $googleSitemapHelper;

    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var CmsPages
     */
    private $cmsPages;

    /**
     * @var Sitemap
     */
    private $googleSitemapModel;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Htmlsitemap constructor.
     * @param Template\Context $context
     * @param Sitemap $googleSitemapModel
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param ProductCollectionFactory $productCollectionFactory
     * @param CmsPages $cmsPages
     * @param GoogleSitemapHelper $googleSitemapHelper
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Sitemap $googleSitemapModel,
        CategoryCollectionFactory $categoryCollectionFactory,
        ProductCollectionFactory $productCollectionFactory,
        CmsPages $cmsPages,
        GoogleSitemapHelper $googleSitemapHelper,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->googleSitemapHelper = $googleSitemapHelper;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->cmsPages = $cmsPages;
        $this->googleSitemapModel = $googleSitemapModel;
        $this->storeManager = $storeManager;
    }

    /**
     * @return mixed
     */
    public function getHtmlSitemapUrls()
    {
        return $this->googleSitemapModel->getUrls();
    }

    /**
     * @return int
     * @throws NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCategoryUrls()
    {
        if (!$this->googleSitemapHelper->getConfigData(
            GoogleSitemapHelper::GOOGLE_SITEMAP_HTML_SETTINGS_CATEGORY_URL,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        )) {
            return [];
        }
        $categoryUrls = [];
        $categories = $this->categoryCollectionFactory->create();
        $categories->addAttributeToSelect('url_path');
        foreach ($categories as $category) {
            if (!is_null($category->getData('url_path'))) {
                $categoryUrls[] = rtrim($this->getUrl($category->getData('url_path')), '/')  .
                    $this->googleSitemapHelper->getConfigData(GoogleSitemapHelper::CATEGORY_URL_SUFFIX);
            }
        }

        return $categoryUrls;
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getProductUrls()
    {
        if (!$this->googleSitemapHelper->getConfigData(
            GoogleSitemapHelper::GOOGLE_SITEMAP_HTML_SETTINGS_PRODUCT_URL,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        )) {
            return [];
        }
        $productUrls = [];
        $products = $this->productCollectionFactory->create();
        $products->addAttributeToSelect('url_key');
        foreach ($products as $product) {
            if (!is_null($product->getData('url_key'))) {
                $productUrls[] = rtrim($this->getUrl($product->getData('url_key')), '/')  .
                    $this->googleSitemapHelper->getConfigData(GoogleSitemapHelper::PRODUCT_URL_SUFFIX);
            }
        }

        return $productUrls;
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getCmsUrls()
    {
        if (!$this->googleSitemapHelper->getConfigData(
            GoogleSitemapHelper::GOOGLE_SITEMAP_HTML_SETTINGS_CMS_PAGE_URL,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        )) {
            return [];
        }
        $excludeCmsPages = $this->googleSitemapHelper->getConfigData(
            GoogleSitemapHelper::GOOGLE_SITEMAP_HTML_SETTINGS_EXCLUDE_CMS_PAGES,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        );
        $excludeCmsPagesArray = explode(',', $excludeCmsPages);
        $cmsUrls = [];

        foreach ($this->cmsPages->getCmsPageCollection() as $item) {
            if (!in_array($item->getIdentifier(), $excludeCmsPagesArray)) {
                $cmsUrls[] = $this->getUrl($item->getIdentifier());
            }
        }
        return $cmsUrls;
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getAdditionalLinks()
    {
        if (!$this->googleSitemapHelper->getConfigData(
            GoogleSitemapHelper::GOOGLE_SITEMAP_HTML_SETTINGS_ENABLE_ADDITIONAL_LINKS,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        )) {
            return [];
        }
        $additionalLinks = [];

        $links = explode("\n", $this->googleSitemapHelper->getConfigData(
            GoogleSitemapHelper::GOOGLE_SITEMAP_HTML_SETTINGS_ADDITIONAL_LINKS,
            ScopeInterface::SCOPE_STORES,
            $this->getStoreId()
        ));
        foreach ($links as $link) {
            $link = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $link);
            $additionalLinks[] = $link;
        }

        return $additionalLinks;
    }
}
