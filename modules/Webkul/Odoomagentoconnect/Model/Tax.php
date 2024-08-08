<?php
/**
 * Webkul Odoomagentoconnect Tax Model
 *
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Odoomagentoconnect\Model;

use Webkul\Odoomagentoconnect\Api\Data\TaxInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Webkul Odoomagentoconnect Tax Model Class
 */
class Tax extends \Magento\Framework\Model\AbstractModel implements TaxInterface, IdentityInterface
{
    protected $_interfaceAttributes = [
   
    TaxInterface::MAGENTO_ID,
    TaxInterface::ODOO_ID,
    TaxInterface::CREATED_BY,
    TaxInterface::NEED_SYNC,
    ];

    /**
* #@+
     * Post's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    /**
* #@-
*/

    /**
     * CMS page cache tag
     */
    const CACHE_TAG = 'odoomagentoconnect_tax';

    /**
     * @var string
     */
    protected $_cacheTag = 'odoomagentoconnect_tax';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'odoomagentoconnect_tax';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Odoomagentoconnect\Model\ResourceModel\Tax::class);
    }
    /**
     * Prepare post's statuses.
     * Available event to customize statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }
    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get the magento_id.
     *
     * @api
     * @return int|null
     */

    public function getMagentoId()
    {
        return $this->getData(self::MAGENTO_ID);
    }

    /**
     * Get the odoo_id.
     *
     * @api
     * @return int|null
     */

    public function getOdooId()
    {
        return $this->getData(self::ODOO_ID);
    }

    /**
     * Get the created_by.
     *
     * @api
     * @return string|null
     */

    public function getCreatedBy()
    {
        return $this->getData(self::CREATED_BY);
    }

    /**
     * Get the need_sync.
     *
     * @api
     * @return string|null
     */

    public function getNeedSync()
    {
        return $this->getData(self::NEED_SYNC);
    }

    /**
     * Set magentoId
     *
     * @api
     * @param  int $magentoId
     * @return $this
     */

    public function setMagentoId($magentoId)
    {
        return $this->setData(self::MAGENTO_ID, $magentoId);
    }

    /**
     * Set odooId
     *
     * @api
     * @param  int $odooId
     * @return $this
     */
    public function setOdooId($odooId)
    {
        return $this->setData(self::ODOO_ID, $odooId);
    }

    /**
     * Set createdBy
     *
     * @api
     * @param  string $createdBy
     * @return $this
     */
    public function setCreatedBy($createdBy)
    {
        return $this->setData(self::CREATED_BY, $createdBy);
    }

    /**
     * Set needSync
     *
     * @api
     * @param  string $needSync
     * @return $this
     */
    public function setNeedSync($needSync)
    {
        return $this->setData(self::NEED_SYNC, $needSync);
    }
}
