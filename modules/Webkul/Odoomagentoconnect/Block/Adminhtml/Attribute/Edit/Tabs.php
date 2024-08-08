<?php
namespace Webkul\Odoomagentoconnect\Block\Adminhtml\Attribute\Edit;

/**
 * Webkul Odoomagentoconnect Attribute Tabs Block
 *
 * @author    Webkul
 * @api
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('page_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Attribute Information'));
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'main_section',
            [
                'label' => __('Attribute Manual Mapping'),
                'title' => __('Attribute Manual Mapping'),
                'content' => $this->getLayout()
                    ->createBlock(\Webkul\Odoomagentoconnect\Block\Adminhtml\Attribute\Edit\Tab\Main::class)
                    ->toHtml(),
                'active' => true
            ]
        );
        return parent::_beforeToHtml();
    }
}
