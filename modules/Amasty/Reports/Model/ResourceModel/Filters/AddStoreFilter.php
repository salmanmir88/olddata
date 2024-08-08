<?php

declare(strict_types=1);

namespace Amasty\Reports\Model\ResourceModel\Filters;

use Amasty\Reports\Model\ResourceModel\Filters\RequestFiltersProvider;
use Magento\Framework\Data\Collection\AbstractDb;

class AddStoreFilter
{
    /**
     * @var RequestFiltersProvider
     */
    private $filtersProvider;

    public function __construct(RequestFiltersProvider $filtersProvider)
    {
        $this->filtersProvider = $filtersProvider;
    }

    public function execute(AbstractDb $collection, $tablePrefix = 'main_table')
    {
        $filters = $this->filtersProvider->execute();
        $store = $filters['store'] ?? false;

        if ($store) {
            $collection->getSelect()->where($tablePrefix . '.store_id = ?', $store);
        }
    }
}
