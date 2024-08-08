<?php

namespace Amasty\Reports\Model\Source;

class Country extends \Magento\Directory\Model\Config\Source\Country
{
    /**
     * Return options array
     */
    public function toOptionArray($isMultiselect = false, $foregroundCountries = '')
    {
        if (!$this->_options) {
            $this->_options = $this->_countryCollection->loadData()->setForegroundCountries(
                $foregroundCountries
            )->toOptionArray(
                false
            );
        }

        $options = $this->_options;
        if (!$isMultiselect) {
            array_unshift($options, ['value' => '', 'label' => __('All Countries')]);
        }

        return $options;
    }
}
