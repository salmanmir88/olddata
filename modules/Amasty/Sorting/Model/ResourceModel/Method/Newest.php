<?php

namespace Amasty\Sorting\Model\ResourceModel\Method;

class Newest extends AbstractMethod
{
    public function getSortingColumnName()
    {
        $attributeCode = $this->helper->getScopeValue('new/new_attr');
        if ($attributeCode) {
            return $attributeCode;
        }

        return 'created_at';
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->getSortingColumnName();
    }

    /**
     * {@inheritdoc}
     */
    public function apply($collection, $direction)
    {
        return $this;
    }

    public function getIndexedValues(int $storeId, ?array $entityIds = [])
    {
        return [];
    }
}
