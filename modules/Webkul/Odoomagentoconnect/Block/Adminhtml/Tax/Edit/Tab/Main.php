<?php
/**
 * Webkul Odoomagentoconnect Tax Tabs Main Block
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

// @codingStandardsIgnoreFile

namespace Webkul\Odoomagentoconnect\Block\Adminhtml\Tax\Edit\Tab;
use Webkul\Odoomagentoconnect\Helper\Connection;

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
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Tax $taxMapResource,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->_taxMapResource = $taxMapResource;
    }

    /**
     * Prepare form fields
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return \Magento\Backend\Block\Widget\Form
     */
    protected function _prepareForm()
    {
        /** @var $model \Magento\User\Model\User */
        $mageTax =$this->_taxMapResource->getMageTaxArray();
        $odooTax = $this->_taxMapResource->getOdooTaxArray();
        $model = $this->_coreRegistry->registry('odoomagentoconnect_user');
        $taxmodel = $this->_coreRegistry->registry('odoomagentoconnect_tax');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('user_');

        $baseFieldset = $form->addFieldset('base_fieldset',['legend' => __('Tax Mapping'), 'class' => 'fieldset-wide']);

        if($taxmodel->getEntityId()){
            $baseFieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);
        }

        $baseFieldset->addField(
                'magento_id',
                'select',
                [
                    'label' => __('Magento Tax'),
                    'title' => __('Magento Tax'),
                    'name' => 'magento_id',
                    'required' => true,
                    'options' => $mageTax
                ]
            );
        $baseFieldset->addField(
                'odoo_id',
                'select',
                [
                    'label' => __('Odoo Tax'),
                    'title' => __('Odoo Tax'),
                    'name' => 'odoo_id',
                    'required' => true,
                    'options' => $odooTax
                ]
            );

        //$data = $model->getData();
        $data= $taxmodel->getData();
        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }

}
