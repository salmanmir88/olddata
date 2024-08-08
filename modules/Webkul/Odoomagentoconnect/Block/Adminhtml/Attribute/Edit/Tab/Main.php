<?php

/**
 * Webkul Odoomagentoconnect Attribute Form Main Block
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

// @codingStandardsIgnoreFile

namespace Webkul\Odoomagentoconnect\Block\Adminhtml\Attribute\Edit\Tab;
use Webkul\Odoomagentoconnect\Model\Attribute;
use Webkul\Odoomagentoconnect\Helper\Connection;

/**
 * Cms page edit form main tab
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic
{
    protected $_objectManager;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Attribute $attrMapResouce,
        array $data = []
    ) {
        $this->_attrMapResouce = $attrMapResouce;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form fields
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return \Magento\Backend\Block\Widget\Form
     */
    protected function _prepareForm()
    {
        $mageAttribute = $this->_attrMapResouce->getMageAttributeArray();
        $odooAttribute = $this->_attrMapResouce->getOdooAttributeArray();
        $model = $this->_coreRegistry->registry('odoomagentoconnect_user');
        $attributemodel = $this->_coreRegistry->registry('odoomagentoconnect_attribute');

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('user_');

        $baseFieldset = $form->addFieldset('base_fieldset',['legend' => __('Attribute Mapping'), 'class' => 'fieldset-wide']);

        if($attributemodel->getEntityId()){
            $baseFieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);
        }

        $baseFieldset->addField(
                'magento_id',
                'select',
                [
                    'label' => __('Magento Attribute'),
                    'title' => __('Magento Attribute'),
                    'name' => 'magento_id',
                    'required' => true,
                    'options' => $mageAttribute
                ]
            );
        $baseFieldset->addField(
                'odoo_id',
                'select',
                [
                    'label' => __('Odoo Attribute'),
                    'title' => __('Odoo Attribute'),
                    'name' => 'odoo_id',
                    'required' => true,
                    'options' => $odooAttribute
                ]
            );

        $data= $attributemodel->getData();
        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
