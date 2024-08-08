<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-seo
 * @version   2.1.11
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\SeoSitemap\Preference\ResourceModel;

use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Sitemap\Model\Source\Product\Image\IncludeImage;

class Product extends \Magento\Sitemap\Model\ResourceModel\Catalog\Product
{
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    private $catalogImageHelper;

    /**
     * @var null|bool
     */
    private $isImageFriendlyUrlEnabled = null;

    /**
     * @var string
     */
    private $imageUrlTemplate = null;

    /**
     * @var string
     */
    private $productRepository;

    /**
     * @var \Mirasvit\Seo\Api\Service\FriendlyImageUrlServiceInterface\Proxy
     */
    private $friendlyImageUrlService;

    /**
     * @var \Mirasvit\Seo\Model\Config
     */
    private $config;

    /**
     * @var \Mirasvit\SeoSitemap\Model\Config
     */
    private $sitemapConfig;

    protected function _construct()
    {
        $this->_init('catalog_product_entity', 'entity_id');

        $this->catalogImageHelper = ObjectManager::getInstance()
            ->get(\Magento\Catalog\Helper\Image::class);

        $this->productRepository = ObjectManager::getInstance()
            ->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);

        $moduleManager = ObjectManager::getInstance()->get(\Magento\Framework\Module\Manager::class);
        if ($moduleManager->isEnabled('Mirasvit_Seo')) {
            $config = ObjectManager::getInstance()->get(\Mirasvit\Seo\Model\Config\ImageConfig::class);

            $this->isImageFriendlyUrlEnabled = $config->isFriendlyUrlEnabled();
            $this->imageUrlTemplate          = $config->getUrlTemplate();

            $this->friendlyImageUrlService = ObjectManager::getInstance()
                ->get(\Mirasvit\Seo\Api\Service\FriendlyImageUrlServiceInterface\Proxy::class);

            $this->config = ObjectManager::getInstance()->get(\Mirasvit\Seo\Model\Config::class);
            $this->sitemapConfig = ObjectManager::getInstance()->get(\Mirasvit\SeoSitemap\Model\Config::class);
        }
    }

    /**
     * @param int $storeId
     * @return array|bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Db_Statement_Exception
     */
    public function getCollection($storeId)
    {
        $products = [];
        $store = $this->_storeManager->getStore($storeId);
        if (!$store) {
            return false;
        }

        $connection = $this->getConnection();

        $joinCondition = 'e.entity_id = url_rewrite.entity_id AND url_rewrite.is_autogenerated = 1 AND url_rewrite.metadata IS NULL';
        if ($this->config && $this->config->isAddLongestCanonicalProductUrl()) {
            $joinCondition = 'e.entity_id = url_rewrite.entity_id AND url_rewrite.is_autogenerated = 1';
        }

        $this->_select = $connection->select()->from(
            ['e' => $this->getMainTable()],
            [$this->getIdFieldName(), $this->_productResource->getLinkField(), 'updated_at', 'sku']
        )
            ->joinInner(['w' => $this->getTable('catalog_product_website')], 'e.entity_id = w.product_id', [])
            ->joinLeft(
                ['url_rewrite' => $this->getTable('url_rewrite')],
                $joinCondition
                . $connection->quoteInto(' AND url_rewrite.store_id = ?', $store->getId())
                . $connection->quoteInto(' AND url_rewrite.entity_type = ?', ProductUrlRewriteGenerator::ENTITY_TYPE),
                ['url' => 'request_path']
            )
            ->where('w.website_id = ?', $store->getWebsiteId());

        $this->_addFilter($store->getId(), 'visibility', $this->_productVisibility->getVisibleInSiteIds(), 'in');

        if (is_object($this->sitemapConfig)) {
            if (!$this->sitemapConfig->getIsShowNonSalableProducts($storeId)) {
                $this->_addFilter($store->getId(), 'status', $this->_productStatus->getVisibleStatusIds(), 'in');
            }
        } else {
            $this->_addFilter($store->getId(), 'status', $this->_productStatus->getVisibleStatusIds(), 'in');
        }

        $imageIncludePolicy = $this->_sitemapData->getProductImageIncludePolicy($store->getId());
        if (IncludeImage::INCLUDE_NONE != $imageIncludePolicy) {
            if ($this->getNumberOfParameters() > 2) {
                $this->_joinAttribute($store->getId(), 'name', 'name');

                if (IncludeImage::INCLUDE_ALL == $imageIncludePolicy) {
                    $this->_joinAttribute($store->getId(), 'thumbnail', 'thumbnail');
                } elseif (IncludeImage::INCLUDE_BASE == $imageIncludePolicy) {
                    $this->_joinAttribute($store->getId(), 'image', 'image');
                }
            } else {
                $this->_joinAttribute($store->getId(), 'name');
                if (strpos($this->_select->__toString(), 'AS `t2_name`') === false) {
                    $this->_select->columns(
                        ['name' => $this->getConnection()->getIfNullSql('t1_name.value')]
                    );
                } else {
                    $this->_select->columns(
                        ['name' => $this->getConnection()->getIfNullSql('t2_name.value', 't1_name.value')]
                    );
                }

                if (IncludeImage::INCLUDE_ALL == $imageIncludePolicy) {
                    $this->_joinAttribute($store->getId(), 'thumbnail');
                    $this->_select->columns(['thumbnail' => $this->getConnection()->getIfNullSql('t2_thumbnail.value', 't1_thumbnail.value'),]);
                } elseif (IncludeImage::INCLUDE_BASE == $imageIncludePolicy) {
                    $this->_joinAttribute($store->getId(), 'image');
                    $this->_select->columns(
                        ['image' => $this->getConnection()->getIfNullSql('t2_image.value', 't1_image.value')]
                    );
                }
            }
        }
        if ($this->config && $this->config->isAddLongestCanonicalProductUrl()) {
            $this->_select->order('e.entity_id asc');
            $this->_select->order('length(url_rewrite.request_path) desc');
        }

        $query = $connection->query($this->_select);
        while ($row = $query->fetch()) {
            if (!isset($products[$row[$this->getIdFieldName()]])) {
                $product                     = $this->_prepareProduct($row, $store->getId());
                $products[$product->getId()] = $product;
            }
        }

        return $products;
    }

    /**
     * @return bool|int
     */
    protected function getNumberOfParameters()
    {
        $numberOfParameters                 = false;
        $checkJoinAttributeReflectionMethod = new \ReflectionMethod(
            \Magento\Sitemap\Model\ResourceModel\Catalog\Product::class,
            '_joinAttribute'
        );
        if (is_object($checkJoinAttributeReflectionMethod)) {
            $numberOfParameters = $checkJoinAttributeReflectionMethod->getNumberOfParameters();
        }

        return $numberOfParameters;
    }

    /**
     * Load product images
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param int                           $storeId
     *
     * @return void
     */
    protected function _loadProductImages($product, $storeId)
    {
        $helper             = $this->_sitemapData;
        $imageIncludePolicy = $helper->getProductImageIncludePolicy($storeId);

        $imagesCollection = [];
        if (IncludeImage::INCLUDE_ALL == $imageIncludePolicy) {
            $imagesCollection = $this->_getAllProductImages($product, $storeId);
        } elseif (IncludeImage::INCLUDE_BASE == $imageIncludePolicy &&
            $product->getImage() &&
            $product->getImage() != self::NOT_SELECTED_IMAGE
        ) {
            $imagesCollection = [
                new \Magento\Framework\DataObject(
                    ['url' => $this->getCurrentProductImageUrl($product, $product->getImage(), $storeId)]
                ),
            ];
        }

        if ($imagesCollection) {
            $thumbnail = $product->getThumbnail();
            if ($thumbnail && $product->getThumbnail() != self::NOT_SELECTED_IMAGE) {
                $thumbnail = $this->getCurrentProductImageUrl($product, $thumbnail, $storeId);
            } else {
                $thumbnail = $this->friendlyImageUrlService->getFriendlyImageName($product, $imagesCollection[0]->getUrl());
            }

            $product->setImages(
                new \Magento\Framework\DataObject(
                    [
                        'collection'    => $imagesCollection,
                        'title'         => $product->getName(),
                        'thumbnail'     => $thumbnail,
                        'alt'           => $this->friendlyImageUrlService->getFriendlyImageAlt($product),
                    ]
                )
            );
        }
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param int $storeId
     * @return array
     */
    protected function _getAllProductImages($product, $storeId)
    {
        $product->setStoreId($storeId);
        $gallery = $this->mediaGalleryResourceModel->loadProductGalleryByAttributeId(
            $product,
            $this->mediaGalleryReadHandler->getAttribute()->getId()
        );

        $imagesCollection = [];
        if ($gallery) {
            foreach ($gallery as $image) {
                $imagesCollection[] = new \Magento\Framework\DataObject(
                    [
                        'url'       => $this->getCurrentProductImageUrl($product, $image['file'], $storeId),
                        'caption'   => $image['label'] ? $image['label'] : $image['label_default'],
                    ]
                );
            }
        }

        return $imagesCollection;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param mixed $image
     * @param int $storeId
     * @return string
     */
    protected function getCurrentProductImageUrl($product, $image, $storeId)
    {
        if ($this->isImageFriendlyUrlEnabled && $this->imageUrlTemplate && $this->isEnoughData($this->imageUrlTemplate)) {
            $product = $this->productRepository->getById($product->getId(), $editMode = false, $storeId);
            $image = $this->friendlyImageUrlService->getFriendlyImageName($product, $image);
        }

        $imgUrl = $this->catalogImageHelper->init($product, 'product_page_image_small')
            ->resize(800, 600)
            ->setImageFile($image)
            ->getUrl();

        return $imgUrl;
    }

    /**
     * @param mixed $imageUrlTemplate
     * @return bool
     */
    protected function isEnoughData($imageUrlTemplate)
    {
        $imageUrlTemplate = str_replace(
            [
                '[product_name]',
                '[product_sku]',
            ],
            '',
            $imageUrlTemplate
        );

        if (strpos($imageUrlTemplate, ']') !== false) {
            return false;
        }

        return true;
    }
}
