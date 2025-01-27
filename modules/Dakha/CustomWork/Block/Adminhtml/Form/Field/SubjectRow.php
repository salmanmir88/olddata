<?php

namespace Dakha\CustomWork\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Dakha\CustomWork\Block\Adminhtml\Form\Field\SubjectColumn;

class SubjectRow extends AbstractFieldArray
{
    /**
     * @var Templete
     */
    private $templeteRenderer;

    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn('text_1', ['label' => __('English'), 'class' => 'required-entry']);
        $this->addColumn('text_2', ['label' => __('Arabic'), 'class' => 'required-entry']);
        $this->addColumn('templete', [
            'label' => __('Select'),
            'renderer' => $this->getTempleteRenderer()
        ]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Row');
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];

        $templete = $row->getTemplete();
        if ($templete !== null) {
            $options['option_' . $this->getTempleteRenderer()->calcOptionHash($templete)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    /**
     *
     * @return Templete
     * @throws LocalizedException
     */
    private function getTempleteRenderer()
    {
        if (!$this->templeteRenderer) {
            $this->templeteRenderer = $this->getLayout()->createBlock(
                SubjectColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->templeteRenderer;
    }
}