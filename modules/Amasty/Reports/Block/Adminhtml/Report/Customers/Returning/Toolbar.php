<?php

namespace Amasty\Reports\Block\Adminhtml\Report\Customers\Returning;

use Magento\Framework\Data\Form\AbstractForm;

/**
 * Class Toolbar
 * @package Amasty\Reports\Block\Adminhtml\Report\Customers\Returning
 */
class Toolbar extends \Amasty\Reports\Block\Adminhtml\Report\Toolbar
{
    /**
     * @param AbstractForm $form
     *
     * @return $this
     */
    protected function addControls(AbstractForm $form)
    {
        $this->addDateControls($form);

        $form->addField('value', 'radios', [
            'name'      => 'value',
            'values'    => [
                ['value' => 'quantity', 'label' => __('Quantity')],
                ['value' => 'total', 'label' => __('Total')]
            ],
            'value'     => 'quantity'
        ]);

        $this->addViewControls(
            $form,
            [
                ['value' => 'multi-linear', 'label' => __('Multi-linear')],
                ['value' => 'multi-column', 'label' => __('Multi-column')]
            ],
            'multi-linear'
        );
        
        return parent::addControls($form);
    }
}
