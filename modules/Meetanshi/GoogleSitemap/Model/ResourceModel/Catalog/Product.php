<?php

namespace Meetanshi\GoogleSitemap\Model\ResourceModel\Catalog;

use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Gallery\ReadHandler;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Sitemap\Helper\Data;
use Magento\Sitemap\Model\Source\Product\Image\IncludeImage;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Meetanshi\GoogleSitemap\Helper\Data as GoogleSitemapHelper;

/**
 * Class Product
 * @package Meetanshi\GoogleSitemap\Model\ResourceModel\Catalog
 */
class Product extends AbstractDb
{
    /**
     *
     */
    const NOT_SELECTED_IMAGE = 'no_selection';

    /**
     * Collection Zend Db select
     *
     * @var Select
     */
    protected $_select;

    /**
     * Attribute cache
     *
     * @var array
     */
    protected $_attributesCache = [];

    /**
     * @var ReadHandler
     * @since 100.1.0
     */
    protected $mediaGalleryReadHandler;

    /**
     * Sitemap data
     *
     * @var Data
     */
    protected $_sitemapData = null;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $_productResource;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Visibility
     */
    protected $_productVisibility;

    /**
     * @var Status
     */
    protected $_productStatus;

    /**
     * @var Gallery
     * @since 100.1.0
     */
    protected $mediaGalleryResourceModel;

    /**
     * @var Config
     * @deprecated 100.2.0 unused
     */
    protected $_mediaConfig;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $productModel;

    /**
     * @var Image
     */
    private $catalogImageHelper;
    /**
     * @var GoogleSitemapHelper
     */
    private $googleSitemapHelper;

    /**
     * @param GoogleSitemapHelper $googleSitemapHelper
     * @param Context $context
     * @param Data $sitemapData
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResource
     * @param StoreManagerInterface $storeManager
     * @param Visibility $productVisibility
     * @param Status $productStatus
     * @param Gallery $mediaGalleryResourceModel
     * @param ReadHandler $mediaGalleryReadHandler
     * @param Config $mediaConfig
     * @param string $connectionName
     * @param \Magento\Catalog\Model\Product $productModel
     * @param Image $catalogImageHelper
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        GoogleSitemapHelper $googleSitemapHelper,
        Context $context,
        Data $sitemapData,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        StoreManagerInterface $storeManager,
        Visibility $productVisibility,
        Status $productStatus,
        Gallery $mediaGalleryResourceModel,
        ReadHandler $mediaGalleryReadHandler,
        Config $mediaConfig,
        $connectionName = null,
        \Magento\Catalog\Model\Product $productModel = null,
        Image $catalogImageHelper = null
    ) {
        $this->_productResource = $productResource;
        $this->_storeManager = $storeManager;
        $this->_productVisibility = $productVisibility;
        $this->_productStatus = $productStatus;
        $this->mediaGalleryResourceModel = $mediaGalleryResourceModel;
        $this->mediaGalleryReadHandler = $mediaGalleryReadHandler;
        $this->_mediaConfig = $mediaConfig;
        $this->_sitemapData = $sitemapData;
        $this->productModel = $productModel ?: ObjectManager::getInstance()->get(\Magento\Catalog\Model\Product::class);
        $this->catalogImageHelper = $catalogImageHelper ?: ObjectManager::getInstance()
            ->get(Image::class);
        parent::__construct($context, $connectionName);
        $this->googleSitemapHelper = $googleSitemapHelper;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_product_entity', 'entity_id');
    }

    /**
     * Add attribute to filter
     *
     * @param int $storeId
     * @param string $attributeCode
     * @param mixed $value
     * @param string $type
     * @return Select|bool
     * @throws LocalizedException
     */
    protected function _addFilter($storeId, $attributeCode, $value, $type = '=')
    {
        if (!$this->_select instanceof Select) {
            return false;
        }

        switch ($type) {
            case '=':
                $conditionRule = '=?';
                break;
            case 'in':
                $conditionRule = ' IN(?)';
                break;
            default:
                return false;
                break;
        }

        $attribute = $this->_getAttribute($attributeCode);
        if ($attribute['backend_type'] == 'static') {
            $this->_select->where('e.' . $attributeCode . $conditionRule, $value);
        } else {
            $this->_joinAttribute($storeId, $attributeCode);
            if ($attribute['is_global']) {
                $this->_select->where('t1_' . $attributeCode . '.value' . $conditionRule, $value);
            } else {
                $ifCase = $this->getConnection()->getCheckSql(
                    't2_' . $attributeCode . '.value_id > 0',
                    't2_' . $attributeCode . '.value',
                    't1_' . $attributeCode . '.value'
                );
                $this->_select->where('(' . $ifCase . ')' . $conditionRule, $value);
            }
        }

        return $this->_select;
    }

    /**
     * Join attribute by code
     *
     * @param int $storeId
     * @param string $attributeCode
     * @param string $column Add attribute value to given column
     * @return void
     * @throws LocalizedException
     */
    protected function _joinAttribute($storeId, $attributeCode, $column = null)
    {
        $connection = $this->getConnection();
        $attribute = $this->_getAttribute($attributeCode);
        $linkField = $this->_productResource->getLinkField();
        $attrTableAlias = 't1_' . $attributeCode;
        $this->_select->joinLeft(
            [$attrTableAlias => $attribute['table']],
            "e.{$linkField} = {$attrTableAlias}.{$linkField}"
            . ' AND ' . $connection->quoteInto($attrTableAlias . '.store_id = ?', Store::DEFAULT_STORE_ID)
            . ' AND ' . $connection->quoteInto($attrTableAlias . '.attribute_id = ?', $attribute['attribute_id']),
            []
        );
        // Global scope attribute value
        $columnValue = 't1_' . $attributeCode . '.value';

        if (!$attribute['is_global']) {
            $attrTableAlias2 = 't2_' . $attributeCode;
            $this->_select->joinLeft(
                ['t2_' . $attributeCode => $attribute['table']],
                "{$attrTableAlias}.{$linkField} = {$attrTableAlias2}.{$linkField}"
                . ' AND ' . $attrTableAlias . '.attribute_id = ' . $attrTableAlias2 . '.attribute_id'
                . ' AND ' . $connection->quoteInto($attrTableAlias2 . '.store_id = ?', $storeId),
                []
            );
            $columnValue = $this->getConnection()->getIfNullSql('t2_' . $attributeCode . '.value', $columnValue);
        }
        if (isset($column)) {
            $this->_select->columns([
                $column => $columnValue
            ]);
        }
    }

    /**
     * Get attribute data by attribute code
     *
     * @param string $attributeCode
     * @return array
     * @throws LocalizedException
     */
    protected function _getAttribute($attributeCode)
    {
        if (!isset($this->_attributesCache[$attributeCode])) {
            $attribute = $this->_productResource->getAttribute($attributeCode);

            $this->_attributesCache[$attributeCode] = [
                'entity_type_id' => $attribute->getEntityTypeId(),
                'attribute_id' => $attribute->getId(),
                'table' => $attribute->getBackend()->getTable(),
                'is_global' => $attribute->getIsGlobal() ==
                    ScopedAttributeInterface::SCOPE_GLOBAL,
                'backend_type' => $attribute->getBackendType(),
            ];
        }
        return $this->_attributesCache[$attributeCode];
    }

    /**
     * Get category collection array
     *
     * @param null|string|bool|int|Store $storeId
     * @return array|bool
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Db_Statement_Exception
     */
    public function getCollection($storeId)
    {
        $products = [];

        /* @var $store Store */
        $store = $this->_storeManager->getStore($storeId);
        if (!$store) {
            return false;
        }

        $connection = $this->getConnection();

        $this->_select = $connection->select()->from(
            ['e' => $this->getMainTable()],
            [$this->getIdFieldName(), $this->_productResource->getLinkField(), 'updated_at']
        )->joinInner(
            ['w' => $this->getTable('catalog_product_website')],
            'e.entity_id = w.product_id',
            []
        )->joinLeft(
            ['url_rewrite' => $this->getTable('url_rewrite')],
            'e.entity_id = url_rewrite.entity_id AND url_rewrite.is_autogenerated = 1 AND url_rewrite.metadata IS NULL'
            . $connection->quoteInto(' AND url_rewrite.store_id = ?', $store->getId())
            . $connection->quoteInto(' AND url_rewrite.entity_type = ?', ProductUrlRewriteGenerator::ENTITY_TYPE),
            ['url' => 'request_path']
        )->where(
            'w.website_id = ?',
            $store->getWebsiteId()
        );

        $this->_addFilter($store->getId(), 'visibility', $this->_productVisibility->getVisibleInSiteIds(), 'in');
        $this->_addFilter($store->getId(), 'status', $this->_productStatus->getVisibleStatusIds(), 'in');

        if ($this->googleSitemapHelper->getConfigData(GoogleSitemapHelper::GOOGLE_SITEMAP_XML_SETTINGS_INCLUDE_IMAGES, ScopeInterface::SCOPE_STORES, $storeId)) {
            $imageIncludePolicy= IncludeImage::INCLUDE_ALL;
        } else {
            $imageIncludePolicy= IncludeImage::INCLUDE_NONE;
        }

        if (IncludeImage::INCLUDE_NONE != $imageIncludePolicy) {
            $this->_joinAttribute($store->getId(), 'name', 'name');

            if (IncludeImage::INCLUDE_ALL == $imageIncludePolicy) {
                $this->_joinAttribute($store->getId(), 'thumbnail', 'thumbnail');
            } elseif (IncludeImage::INCLUDE_BASE == $imageIncludePolicy) {
                $this->_joinAttribute($store->getId(), 'image', 'image');
            }
        }

        $query = $connection->query($this->prepareSelectStatement($this->_select));
        while ($row = $query->fetch()) {
            $product = $this->_prepareProduct($row, $store->getId());
            $products[$product->getId()] = $product;
        }

        return $products;
    }

    /**
     * Prepare product
     *
     * @param array $productRow
     * @param int $storeId
     * @return DataObject
     * @throws LocalizedException
     */
    protected function _prepareProduct(array $productRow, $storeId)
    {
        $product = new DataObject();

        $product['id'] = $productRow[$this->getIdFieldName()];
        if (empty($productRow['url'])) {
            $productRow['url'] = 'catalog/product/view/id/' . $product->getId();
        }
        $product->addData($productRow);
        $this->_loadProductImages($product, $storeId);

        return $product;
    }

    /**
     * Load product images
     *
     * @param DataObject $product
     * @param int $storeId
     * @return void
     */
    protected function _loadProductImages($product, $storeId)
    {
        if ($this->googleSitemapHelper->getConfigData(GoogleSitemapHelper::GOOGLE_SITEMAP_XML_SETTINGS_INCLUDE_IMAGES, ScopeInterface::SCOPE_STORES, $storeId)) {
            $imageIncludePolicy= IncludeImage::INCLUDE_ALL;
        } else {
            $imageIncludePolicy= IncludeImage::INCLUDE_NONE;
        }
        $imagesCollection = [];
        if (IncludeImage::INCLUDE_ALL == $imageIncludePolicy) {
            $imagesCollection = $this->_getAllProductImages($product, $storeId);
        } elseif (IncludeImage::INCLUDE_BASE == $imageIncludePolicy &&
            $product->getImage() &&
            $product->getImage() != self::NOT_SELECTED_IMAGE
        ) {
            $imagesCollection = [
                new DataObject(
                    ['url' => $this->getProductImageUrl($product->getImage())]
                ),
            ];
        }

        if ($imagesCollection) {
            // Determine thumbnail path
            $thumbnail = $product->getThumbnail();
            if ($thumbnail && $product->getThumbnail() != self::NOT_SELECTED_IMAGE) {
                $thumbnail = $this->getProductImageUrl($thumbnail);
            } else {
                $thumbnail = $imagesCollection[0]->getUrl();
            }

            $product->setImages(
                new DataObject(
                    ['collection' => $imagesCollection, 'title' => $product->getName(), 'thumbnail' => $thumbnail]
                )
            );
        }
    }

    /**
     * Get all product images
     *
     * @param DataObject $product
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
                $imagesCollection[] = new DataObject(
                    [
                        'url' => $this->getProductImageUrl($image['file']),
                        'caption' => $image['label'] ? $image['label'] : $image['label_default'],
                    ]
                );
            }
        }

        return $imagesCollection;
    }

    /**
     * Allow to modify select statement with plugins
     *
     * @param Select $select
     * @return Select
     */
    public function prepareSelectStatement(Select $select)
    {
        return $select;
    }

    /**
     * Get product image URL from image filename and path
     *
     * @param string $image
     * @return string
     */
    private function getProductImageUrl($image)
    {
        $productObject = $this->productModel;
        $imgUrl = $this->catalogImageHelper
            ->init($productObject, 'product_page_image_large')
            ->setImageFile($image)
            ->getUrl();

        return $imgUrl;
    }
}
