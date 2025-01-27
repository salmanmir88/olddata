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



namespace Mirasvit\SeoContent\Ui\Template\Source;

use Magento\Framework\Option\ArrayInterface;
use Mirasvit\SeoContent\Api\Data\TemplateInterface;

class ProductDescriptionPositionSource implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => __('Bottom of the page'),
                'value' => TemplateInterface::DESCRIPTION_POSITION_BOTTOM_PAGE,
            ],
            [
                'label' => __('Under Short Description'),
                'value' => TemplateInterface::DESCRIPTION_POSITION_UNDER_SHORT_DESCRIPTION,
            ],
            [
                'label' => __('Under Full Description'),
                'value' => TemplateInterface::DESCRIPTION_POSITION_UNDER_FULL_DESCRIPTION,
            ],
            [
                'label' => __('Under custom template'),
                'value' => TemplateInterface::DESCRIPTION_POSITION_CUSTOM_TEMPLATE,
            ],
            [
                'label' => __('Don\'t add automatically'),
                'value' => TemplateInterface::DESCRIPTION_POSITION_DISABLED,
            ],
        ];
    }
}
