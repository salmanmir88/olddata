<?php
namespace Saee\ShipmentMethod\Model\ResourceModel;


use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;


/**
 * Class DbData
 * @package Saee\ShipmentMethod\Model\ResourceModel
 */
class DbData extends AbstractDb
{

    /**
     * DbData constructor.
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    /**
     *
     */
    protected function _construct()
    {
        $this->_init('saee_response', 'id');
    }

}
