<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-seo
 * @version   2.1.11
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Seo\Block\Adminhtml\System;

class NoindexPages extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * @var NoindexOption
     */
    protected $optionsRenderer;

    /**
     * @return \Magento\Framework\View\Element\Html\Select
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getOptionsRenderer()
    {
        if (!$this->optionsRenderer) {
            $this->optionsRenderer = $this->getLayout()->createBlock(
                'Mirasvit\Seo\Block\Adminhtml\System\NoindexOption',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->optionsRenderer->setClass('customer_options_select');
            $this->optionsRenderer->setExtraParams('style="width:150px"');
        }

        return $this->optionsRenderer;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->addColumn('pattern', [
            'label' => __('URL Pattern'),
            'style' => 'width:250px',
        ]);
        $select = $this->_getOptionsRenderer();

        $this->addColumn('option', [
            'label' => __('Option'),
            'renderer' => $select,
        ]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
        parent::_construct();
    }


    /**
     * @param \Magento\Framework\DataObject $row
     *
     * @return void
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $options = [];
        if ($row->getOption()) {
            $options['option_' . $this->_getOptionsRenderer()->calcOptionHash($row->getData('option'))]
                = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }
}
