<?php
namespace Saee\ShipmentMethod\Model;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class DbData
 * @package Saee\ShipmentMethod\Model
 */
class DbData extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'saee_response';

    protected $_cacheTag = 'saee_response';

    protected $_eventPrefix = 'saee_response';

    protected function _construct()
    {
        $this->_init('Saee\ShipmentMethod\Model\ResourceModel\DbData');
    }

    /**
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return array
     */
    public function getDefaultValues()
    {
        $values = [];

        return $values;
    }

}
