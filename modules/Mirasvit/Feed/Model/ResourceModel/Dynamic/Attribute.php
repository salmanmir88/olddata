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
 * @package   mirasvit/module-feed
 * @version   1.1.38
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Feed\Model\ResourceModel\Dynamic;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime;
use Mirasvit\Feed\Service\Serialize;

class Attribute extends AbstractDb
{
    /**
     * @var Serialize
     */
    protected $serializer;

    /**
     * Constructor.
     *
     * @param Serialize $serializer
     * @param Context   $context
     */
    public function __construct(
        Serialize $serializer,
        Context   $context
    ) {
        $this->serializer = $serializer;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('mst_feed_dynamic_attribute', 'attribute_id');
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeSave(AbstractModel $object)
    {
        if ($object->getData('conditions') && is_array($object->getData('conditions'))) {
            $object->setData('conditions_serialized', $this->serializer->serialize($object->getData('conditions')));
        }

        return parent::_beforeSave($object);
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad(AbstractModel $object)
    {
        if ($object->getData('conditions_serialized')) {
            $object->setData('conditions', $this->serializer->unserialize($object->getData('conditions_serialized')));
        }

        return parent::_afterLoad($object);
    }
}
