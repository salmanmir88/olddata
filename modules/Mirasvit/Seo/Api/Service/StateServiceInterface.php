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



namespace Mirasvit\Seo\Api\Service;

interface StateServiceInterface
{
    /**
     * @return bool
     */
    public function isCategoryPage();

    /**
     * @return bool
     */
    public function isNavigationPage();

    /**
     * @return bool
     */
    public function isProductPage();

    /**
     * @return bool
     */
    public function isCmsPage();

    /**
     * @return bool
     */
    public function isHomePage();

    /**
     * @return false|\Magento\Catalog\Model\Category
     */
    public function getCategory();

    /**
     * @return false|\Magento\Catalog\Model\Product
     */
    public function getProduct();

    /**
     * @return false|\Magento\Framework\DataObject
     */
    public function getFilters();
}