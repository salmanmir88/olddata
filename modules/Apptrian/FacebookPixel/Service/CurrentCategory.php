<?php
/**
 * @category  Apptrian
 * @package   Apptrian_FacebookPixel
 * @author    Apptrian
 * @copyright Copyright (c) Apptrian (http://www.apptrian.com)
 * @license   http://www.apptrian.com/license Proprietary Software License EULA
 */
 
namespace Apptrian\FacebookPixel\Service;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\CategoryInterfaceFactory;

class CurrentCategory
{
    /**
     * @var \Magento\Catalog\Api\Data\CategoryInterface
     */
    public $category;
    
    /**
     * @var \Magento\Catalog\Api\Data\CategoryInterfaceFactory
     */
    public $categoryFactory;

    /**
     * @var int|string
     */
    public $categoryId;
    
    /**
     * Constructor.
     *
     * @param \Magento\Catalog\Api\Data\CategoryInterfaceFactory $categoryFactory
     */
    public function __construct(
        CategoryInterfaceFactory $categoryFactory
    ) {
        $this->categoryFactory = $categoryFactory;
    }

    /**
     * Sets category interface.
     *
     * @param CategoryInterface $category
     */
    public function set(CategoryInterface $category)
    {
        $this->category = $category;
    }

    /**
     * Gets category Interface.
     *
     * @return CategoryInterface
     */
    public function get()
    {
        return isset($this->category) ? $this->category : $this->createNullCategory();
    }
    
    /**
     * Returns empty category interface object.
     *
     * @return CategoryInterface
     */
    public function createNullCategory()
    {
        return $this->categoryFactory->create();
    }
    
    /**
     * Returns category.
     *
     * @return \Magento\Catalog\Api\Data\CategoryInterface
     */
    public function getCategory()
    {
        return $this->category;
    }
    
    /**
     * Sets category.
     *
     * @param \Magento\Catalog\Api\Data\CategoryInterface $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }
    
    /**
     * Returns category id.
     *
     * @return int|string
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }
    
    /**
     * Sets categroy id.
     *
     * @param int|string $id
     */
    public function setCategoryId($id)
    {
        $this->categoryId = $id;
    }
}
