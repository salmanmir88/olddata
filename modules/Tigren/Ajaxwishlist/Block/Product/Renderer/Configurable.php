<?php
/**
 * @copyright Copyright (c) 2017 www.tigren.com
 */

namespace Tigren\Ajaxwishlist\Block\Product\Renderer;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Product as CatalogProduct;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Helper\Data;
use Magento\ConfigurableProduct\Model\ConfigurableAttributeData;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Store\Model\ScopeInterface;
use Magento\Swatches\Helper\Data as SwatchData;
use Magento\Swatches\Helper\Media;
use Magento\Swatches\Model\Swatch;

/**
 * Swatch renderer block
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Configurable extends \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable implements
    \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * Path to template file with Swatch renderer.
     */
    const SWATCH_RENDERER_TEMPLATE = 'Tigren_Ajaxwishlist::product/view/swatches.phtml';

    /**
     * Path to default template file with standard Configurable renderer.
     */
    const CONFIGURABLE_RENDERER_TEMPLATE = 'Tigren_Ajaxwishlist::product/view/type/options/configurable.phtml';

    /**
     * Action name for ajax request
     */
    const MEDIA_CALLBACK_ACTION = 'swatches/ajax/media';

    /**
     * @var Product
     */
    protected $product;

    /**
     * @var SwatchData
     */
    protected $swatchHelper;

    /**
     * @var Media
     */
    protected $swatchMediaHelper;

    /**
     * Indicate if product has one or more Swatch attributes
     *
     * @var boolean
     */
    protected $isProductHasSwatchAttribute;

    /**
     * @param Context $context
     * @param ArrayUtils $arrayUtils
     * @param EncoderInterface $jsonEncoder
     * @param Data $helper
     * @param CatalogProduct $catalogProduct
     * @param CurrentCustomer $currentCustomer
     * @param PriceCurrencyInterface $priceCurrency
     * @param ConfigurableAttributeData $configurableAttributeData
     * @param SwatchData $swatchHelper
     * @param Media $swatchMediaHelper
     * @param array $data other data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        ArrayUtils $arrayUtils,
        EncoderInterface $jsonEncoder,
        Data $helper,
        CatalogProduct $catalogProduct,
        CurrentCustomer $currentCustomer,
        PriceCurrencyInterface $priceCurrency,
        ConfigurableAttributeData $configurableAttributeData,
        SwatchData $swatchHelper,
        Media $swatchMediaHelper,
        array $data = []
    ) {
        $this->swatchHelper = $swatchHelper;
        $this->swatchMediaHelper = $swatchMediaHelper;

        parent::__construct(
            $context,
            $arrayUtils,
            $jsonEncoder,
            $helper,
            $catalogProduct,
            $currentCustomer,
            $priceCurrency,
            $configurableAttributeData,
            $data
        );
    }

    /**
     * Get Key for caching block content
     *
     * @return string
     */
    public function getCacheKey()
    {
        return parent::getCacheKey() . '-' . $this->getProduct()->getId();
    }

    /**
     * Override parent function
     *
     * @return Product
     */
    public function getProduct()
    {
        if (!$this->product) {
            $this->product = parent::getProduct();
        }

        return $this->product;
    }

    /**
     * Set product to block
     *
     * @param Product $product
     * @return $this
     */
    public function setProduct(Product $product)
    {
        $this->product = $product;
        return $this;
    }

    /**
     * Get Swatch config data
     *
     * @return string
     */
    public function getJsonSwatchConfig()
    {
        $attributesData = $this->getSwatchAttributesData();
        $allOptionIds = $this->getConfigurableOptionsIds($attributesData);
        $swatchesData = $this->swatchHelper->getSwatchesByOptionsId($allOptionIds);

        $config = [];
        foreach ($attributesData as $attributeId => $attributeDataArray) {
            if (isset($attributeDataArray['options'])) {
                $config[$attributeId] = $this->addSwatchDataForAttribute(
                    $attributeDataArray['options'],
                    $swatchesData,
                    $attributeDataArray
                );
            }
        }

        return $this->jsonEncoder->encode($config);
    }

    /**
     * @return array
     */
    protected function getSwatchAttributesData()
    {
        return $this->swatchHelper->getSwatchAttributesAsArray($this->getProduct());
    }

    /**
     * @param array $attributeData
     * @return array
     */
    protected function getConfigurableOptionsIds(array $attributeData)
    {
        $ids = [];
        foreach ($this->getAllowProducts() as $product) {
            /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute $attribute */
            foreach ($this->helper->getAllowAttributes($this->getProduct()) as $attribute) {
                $productAttribute = $attribute->getProductAttribute();
                $productAttributeId = $productAttribute->getId();
                if (isset($attributeData[$productAttributeId])) {
                    $ids[$product->getData($productAttribute->getAttributeCode())] = 1;
                }
            }
        }
        return array_keys($ids);
    }

    /**
     * Add Swatch Data for attribute
     *
     * @param array $options
     * @param array $swatchesCollectionArray
     * @param array $attributeDataArray
     * @return array
     */
    protected function addSwatchDataForAttribute(
        array $options,
        array $swatchesCollectionArray,
        array $attributeDataArray
    ) {
        $result = [];
        foreach ($options as $optionId => $label) {
            if (isset($swatchesCollectionArray[$optionId])) {
                $result[$optionId] = $this->extractNecessarySwatchData($swatchesCollectionArray[$optionId]);
                $result[$optionId] = $this->addAdditionalMediaData($result[$optionId], $optionId, $attributeDataArray);
                $result[$optionId]['label'] = $label;
            }
        }

        return $result;
    }

    /**
     * Retrieve Swatch data for config
     *
     * @param array $swatchDataArray
     * @return array
     */
    protected function extractNecessarySwatchData(array $swatchDataArray)
    {
        $result['type'] = $swatchDataArray['type'];

        if ($result['type'] == Swatch::SWATCH_TYPE_VISUAL_IMAGE && !empty($swatchDataArray['value'])) {
            $result['value'] = $this->swatchMediaHelper->getSwatchAttributeImage(
                Swatch::SWATCH_IMAGE_NAME,
                $swatchDataArray['value']
            );
            $result['thumb'] = $this->swatchMediaHelper->getSwatchAttributeImage(
                Swatch::SWATCH_THUMBNAIL_NAME,
                $swatchDataArray['value']
            );
        } else {
            $result['value'] = $swatchDataArray['value'];
        }

        return $result;
    }

    /**
     * Add media from variation
     *
     * @param array $swatch
     * @param integer $optionId
     * @param array $attributeDataArray
     * @return array
     */
    protected function addAdditionalMediaData(array $swatch, $optionId, array $attributeDataArray)
    {
        if (
            isset($attributeDataArray['use_product_image_for_swatch'])
            && $attributeDataArray['use_product_image_for_swatch']
        ) {
            $variationMedia = $this->getVariationMedia($attributeDataArray['attribute_code'], $optionId);
            if (!empty($variationMedia)) {
                $swatch['type'] = Swatch::SWATCH_TYPE_VISUAL_IMAGE;
                $swatch = array_merge($swatch, $variationMedia);
            }
        }
        return $swatch;
    }

    /**
     * Generate Product Media array
     *
     * @param string $attributeCode
     * @param integer $optionId
     * @return array
     */
    protected function getVariationMedia($attributeCode, $optionId)
    {
        $variationProduct = $this->swatchHelper->loadFirstVariationWithSwatchImage(
            $this->getProduct(),
            [$attributeCode => $optionId]
        );

        if (!$variationProduct) {
            $variationProduct = $this->swatchHelper->loadFirstVariationWithImage(
                $this->getProduct(),
                [$attributeCode => $optionId]
            );
        }

        $variationMediaArray = [];
        if ($variationProduct) {
            $variationMediaArray = [
                'value' => $this->getSwatchProductImage($variationProduct, Swatch::SWATCH_IMAGE_NAME),
                'thumb' => $this->getSwatchProductImage($variationProduct, Swatch::SWATCH_THUMBNAIL_NAME),
            ];
        }

        return $variationMediaArray;
    }

    /**
     * @param Product $childProduct
     * @param string $imageType
     * @return string
     */
    protected function getSwatchProductImage(Product $childProduct, $imageType)
    {
        if ($this->isProductHasImage($childProduct, Swatch::SWATCH_IMAGE_NAME)) {
            $swatchImageId = $imageType;
            $imageAttributes = ['type' => Swatch::SWATCH_IMAGE_NAME];
        } elseif ($this->isProductHasImage($childProduct, 'image')) {
            $swatchImageId = $imageType == Swatch::SWATCH_IMAGE_NAME ? 'swatch_image_base' : 'swatch_thumb_base';
            $imageAttributes = ['type' => 'image'];
        }
        if (isset($swatchImageId)) {
            return $this->_imageHelper->init($childProduct, $swatchImageId, $imageAttributes)->getUrl();
        }
    }

    /**
     * @param Product $product
     * @param string $imageType
     * @return bool
     */
    protected function isProductHasImage(Product $product, $imageType)
    {
        return $product->getData($imageType) !== null && $product->getData($imageType) != SwatchData::EMPTY_IMAGE_VALUE;
    }

    /**
     * Get number of swatches from config to show on product listing.
     * Other swatches can be shown after click button 'Show more'
     *
     * @return string
     */
    public function getNumberSwatchesPerProduct()
    {
        return $this->_scopeConfig->getValue(
            'catalog/frontend/swatches_per_product',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Produce and return block's html output
     *
     * @codeCoverageIgnore
     * @return string
     */
    public function toHtml()
    {
        $this->initIsProductHasSwatchAttribute();
        $this->setTemplate(
            $this->getRendererTemplate()
        );

        return parent::toHtml();
    }

    /**
     * @codeCoverageIgnore
     * @return void
     */
    protected function initIsProductHasSwatchAttribute()
    {
        $this->isProductHasSwatchAttribute = $this->swatchHelper->isProductHasSwatch($this->getProduct());
    }

    /**
     * @codeCoverageIgnore
     * @return string
     */
    protected function getRendererTemplate()
    {
        return $this->isProductHasSwatchAttribute ?
            self::SWATCH_RENDERER_TEMPLATE : self::CONFIGURABLE_RENDERER_TEMPLATE;
    }

    /**
     * @return string
     */
    public function getMediaCallback()
    {
        return $this->getUrl(self::MEDIA_CALLBACK_ACTION, ['_secure' => $this->getRequest()->isSecure()]);
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return string[]
     */
    public function getIdentities()
    {
        if ($this->product instanceof \Magento\Framework\DataObject\IdentityInterface) {
            return $this->product->getIdentities();
        } else {
            return [];
        }
    }

    /**
     * Get block cache life time
     *
     * @return int
     */
    protected function getCacheLifetime()
    {
        return parent::hasCacheLifetime() ? parent::getCacheLifetime() : 3600;
    }

    /**
     * Return HTML code
     *
     * @codeCoverageIgnore
     * @return string
     */
    protected function _toHtml()
    {
        return $this->getHtmlOutput();
    }

    /**
     * @codeCoverageIgnore
     * @return string
     */
    protected function getHtmlOutput()
    {
        return parent::_toHtml();
    }
}
