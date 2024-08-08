<?php
/**
 * @category  Apptrian
 * @package   Apptrian_FacebookPixel
 * @author    Apptrian
 * @copyright Copyright (c) Apptrian (http://www.apptrian.com)
 * @license   http://www.apptrian.com/license Proprietary Software License EULA
 */
 
namespace Apptrian\FacebookPixel\Model\Config;

use Magento\Framework\Option\ArrayInterface;

class IdentPluralGrouped implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '1', 'label' => __('Product SKU as (id) (Not Recommended)')],
            ['value' => '2', 'label' => __('Children SKUs as (id)s')],
            ['value' => '3', 'label' => __('Children SKUs as (id)s and Product SKU as (item_group_id)')]
        ];
    }
}
