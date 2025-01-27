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



namespace Mirasvit\Seo\Ui\Redirect\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Mirasvit\Seo\Model\ResourceModel\Redirect\CollectionFactory;

class DataProvider extends AbstractDataProvider
{
    /**
     * DataProvider constructor.
     * @param CollectionFactory $collectionFactory
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create()->addStoreColumn();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }


    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $storeIds = [];
        if ($data = $this->collection->getData()) { //prepare store_id for multistore
            foreach ($data as $value) {
                $storeIds[$value['redirect_id']] = $value['store_id'];
            }
        }

        $result = [];
        foreach ($this->collection->getItems() as $item) {
            if (isset($storeIds[$item->getId()])) {  //prepare store_id for multistore
                $item->setData('store_id', $storeIds[$item->getId()]);
            }
            $result[$item->getId()] = $item->getData();
        }

        return $result;
    }
}
