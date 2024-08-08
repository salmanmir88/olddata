<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Affiliate for Magento 2
*/

namespace Amasty\Affiliate\Model\Website;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Store\Model\ResourceModel\Website\Collection;

class Options extends AbstractSource
{

    /**
     * @var Collection
     */
    private $collectionWebsite;

    public function __construct(Collection $collectionWebsite)
    {
        $this->collectionWebsite = $collectionWebsite;
    }

    /**
     * @return array
     */
    public function getAllOptions()
    {
        return $this->collectionWebsite->toOptionArray();
    }
}
