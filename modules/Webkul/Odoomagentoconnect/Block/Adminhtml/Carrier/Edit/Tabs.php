<?php
namespace Webkul\Odoomagentoconnect\Block\Adminhtml\Carrier\Edit;

/**
 * Webkul Odoomagentoconnect Carrier Tabs Block
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
        $this->setTitle(__('Carrier Information'));
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'main_section',
            [
                'label' => __('Carrier Manual Mapping'),
                'title' => __('Carrier Manual Mapping'),
                'content' => $this->getLayout()
                    ->createBlock(\Webkul\Odoomagentoconnect\Block\Adminhtml\Carrier\Edit\Tab\Main::class)
                    ->toHtml(),
                'active' => true
            ]
        );
        return parent::_beforeToHtml();
    }
}
