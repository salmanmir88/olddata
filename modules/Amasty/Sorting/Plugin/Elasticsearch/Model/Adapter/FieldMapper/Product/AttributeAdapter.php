<?php

namespace Amasty\Sorting\Plugin\Elasticsearch\Model\Adapter\FieldMapper\Product;

use Amasty\Sorting\Helper\Data;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter as NativeAttributeAdapter;

class AttributeAdapter
{
    /**
     * @var Data
     */
    private $helper;

    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param NativeAttributeAdapter $subject
     * @param bool $result
     * @return bool
     */
    public function afterIsSortable($subject, $result)
    {
        if (in_array($subject->getAttributeCode(), $this->helper->getAmastyAttributesCodes())) {
            $result = true;
        }

        return $result;
    }
}
