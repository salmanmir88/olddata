<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Webhook
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Webhook\Observer;

use Exception;
use Magento\Framework\Event\Observer;
use Mageplaza\Webhook\Model\Config\Source\HookType;

/**
 * Class AfterProduct
 * @package Mageplaza\Webhook\Observer
 */
class AfterProduct extends AfterSave
{
    /**
     * @var string
     */
    protected $hookType = HookType::NEW_PRODUCT;

    /**
     * @var string
     */
    protected $hookTypeUpdate = HookType::UPDATE_PRODUCT;

    /**
     * @param Observer $observer
     *
     * @throws Exception
     */
    public function execute(Observer $observer)
    {
        $item = $observer->getDataObject();
        if ($item->getMpNew()) {
            parent::execute($observer); // TODO: Change the autogenerated stub
        } else {
            $this->updateObserver($observer);
        }
    }
}
