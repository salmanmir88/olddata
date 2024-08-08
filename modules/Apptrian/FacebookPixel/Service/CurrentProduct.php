<?php
/**
 * @category  Apptrian
 * @package   Apptrian_FacebookPixel
 * @author    Apptrian
 * @copyright Copyright (c) Apptrian (http://www.apptrian.com)
 * @license   http://www.apptrian.com/license Proprietary Software License EULA
 */
 
namespace Apptrian\FacebookPixel\Service;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;

class CurrentProduct
{
    /**
     * @var \Magento\Catalog\Api\Data\ProductInterface
     */
    public $product;
    
    /**
     * @var \Magento\Catalog\Api\Data\ProductInterfaceFactory
     */
    public $productFactory;

    /**
     * @var int|string
     */
    public $productId;
    
    /**
     * Constructor.
     *
     * @param \Magento\Catalog\Api\Data\ProductInterfaceFactory $productFactory
     */
    public function __construct(
        ProductInterfaceFactory $productFactory
    ) {
        $this->productFactory = $productFactory;
    }

    /**
     * Sets product interface.
     *
     * @param ProductInterface $product
     */
    public function set(ProductInterface $product)
    {
        $this->product = $product;
    }

    /**
     * Gets product Interface.
     *
     * @return ProductInterface
     */
    public function get()
    {
        return isset($this->product) ? $this->product : $this->createNullProduct();
    }
    
    /**
     * Returns empty product interface object.
     *
     * @return ProductInterface
     */
    public function createNullProduct()
    {
        return $this->productFactory->create();
    }
    
    /**
     * Returns product.
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    public function getProduct()
    {
        return $this->product;
    }
    
    /**
     * Sets product.
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }
    
    /**
     * Returns product id.
     *
     * @return int|string
     */
    public function getProductId()
    {
        return $this->productId;
    }
    
    /**
     * Sets product id.
     *
     * @param int|string $id $id
     */
    public function setProductId($id)
    {
        $this->productId = $id;
    }
}
