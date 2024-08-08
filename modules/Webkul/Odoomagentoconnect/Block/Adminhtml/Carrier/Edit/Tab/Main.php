<?php
/**
 * Webkul Odoomagentoconnect Carrier Tab Main Block
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

// @codingStandardsIgnoreFile

namespace Webkul\Odoomagentoconnect\Block\Adminhtml\Carrier\Edit\Tab;

/**
 * Cms page edit form main tab
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic
{
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
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Carrier $carrierMapResource,
        array $data = []
    ) {
        $this->_carrierMapResource = $carrierMapResource;
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
        $mageCarrier = $this->_carrierMapResource->getMageCarrierArray();
        $odooCarrier = $this->_carrierMapResource->getOdooCarrierArray();
        $model = $this->_coreRegistry->registry('odoomagentoconnect_user');
        $carriermodel = $this->_coreRegistry->registry('odoomagentoconnect_carrier');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('user_');

        $baseFieldset = $form->addFieldset('base_fieldset',['legend' => __('Carrier Mapping'), 'class' => 'fieldset-wide']);

        if($carriermodel->getEntityId()){
            $baseFieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);
        }

        $baseFieldset->addField(
                'carrier_code',
                'select',
                [
                    'label' => __('Magento Carrier'),
                    'title' => __('Magento Carrier'),
                    'name' => 'carrier_code',
                    'required' => true,
                    'options' => $mageCarrier
                ]
            );
        $baseFieldset->addField(
                'odoo_id',
                'select',
                [
                    'label' => __('Odoo Carrier'),
                    'title' => __('Odoo Carrier'),
                    'name' => 'odoo_id',
                    'required' => true,
                    'options' => $odooCarrier
                ]
            );

        $data= $carriermodel->getData();
        $data['created_by'] = 'Manual Mapping';
        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
