<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Affiliate for Magento 2
*/

namespace Amasty\Affiliate\Block\Adminhtml\Banner\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class DeleteButton
 */
class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        $data = [];
        $bannerId = $this->getBannerId();
        if ($bannerId && $this->canRender('delete')) {
            $data = [
                'label' => __('Delete'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\'' . __(
                    'Are you sure you want to delete this?'
                ) . '\', \'' . $this->urlBuilder->getUrl('*/*/delete', ['id' => $bannerId]) . '\')',
                'sort_order' => 20,
            ];
        }
        return $data;
    }
}
