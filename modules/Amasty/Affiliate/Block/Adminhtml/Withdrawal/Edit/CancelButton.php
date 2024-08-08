<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Affiliate for Magento 2
*/

namespace Amasty\Affiliate\Block\Adminhtml\Withdrawal\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class CancelButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     * @codeCoverageIgnore
     */
    public function getButtonData()
    {
        /** @var \Amasty\Affiliate\Model\Withdrawal $currentWithdrawal */
        $currentWithdrawal = $this->getCurrentWithdrawal();
        $data = [];
        if ($currentWithdrawal->getStatus() == $currentWithdrawal::STATUS_PENDING) {
            $data = [
                'label' => __('Cancel'),
                'on_click' => sprintf("location.href = '%s';", $this->getCancelUrl($currentWithdrawal->getTransactionId()))
            ];
        }

        return $data;
    }

    public function getCancelUrl($withdrawalId)
    {
        return $this->getUrl('amasty_affiliate/withdrawal/cancel', ['id' => $withdrawalId]);
    }
}
