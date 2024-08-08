<?php

namespace Amasty\Sorting\Model\Config\Backend;

class SimpleText extends \Magento\Framework\App\Config\Value
{
    public function beforeSave()
    {
        if ($this->isValueChanged() && isset($this->_data['escaper'])) {
            /** @var \Magento\Framework\Escaper $escaper */
            $escaper = $this->_data['escaper'];
            $this->setValue(
                $escaper->escapeHtml($this->getValue())
            );
        }

        return parent::beforeSave();
    }
}
