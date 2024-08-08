<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Affiliate for Magento 2
*/

namespace Amasty\Affiliate\Block\Adminhtml\Account\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class SaveAndContinueButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->canRender('save_and_continue_edit')) {
            $data = [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'on_click' => '',
                'sort_order' => 90,
            ];
        }
        return $data;
    }
}
