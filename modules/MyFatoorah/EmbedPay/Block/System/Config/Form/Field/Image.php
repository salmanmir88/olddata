<?php

/**
 * Image config field renderer
 */

namespace MyFatoorah\EmbedPay\Block\System\Config\Form\Field;

/**
 * Class Image Field
 * @method getFieldConfig()
 * @method setFieldConfig()
 */
class Image extends \Magento\Config\Block\System\Config\Form\Field {

    /**
     * Get country selector html
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element) {
        $html = '';

        if (!(string) $element->getValue()) {
            $defaultImage = $this->getViewFileUrl('MyFatoorah_EmbedPay::images/myfatoorah.png');

            $html .= '<img src="' . $defaultImage . '" alt="MyFatoorah logo" height="30" class="small-image-preview v-middle" />';
            $html .= '<p class="note"><span>Upload a new image if you wish to replace this logo.</span></p>';
        }

        //the standard image preview is very small- bump the height up a bit and remove the width
        $html .= str_replace('height="22" width="22"', 'height="50"', parent::_getElementHtml($element));

        return $html;
    }

}
