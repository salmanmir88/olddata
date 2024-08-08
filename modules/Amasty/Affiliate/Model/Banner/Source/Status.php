<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Affiliate for Magento 2
*/

namespace Amasty\Affiliate\Model\Banner\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    /**
     * @var \Amasty\Affiliate\Model\Banner
     */
    private $banner;

    /**
     * IsActive constructor.
     * @param \Amasty\Affiliate\Model\Banner $banner
     */
    public function __construct(\Amasty\Affiliate\Model\Banner $banner)
    {
        $this->banner = $banner;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->banner->getAvailableStatuses();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
