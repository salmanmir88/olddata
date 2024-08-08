<?php
namespace Dakha\OrderReport\Block\Adminhtml\Salesitem\Edit;

/**
 * Admin page left menu
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('salesitem_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Salesitem Information'));
    }
}