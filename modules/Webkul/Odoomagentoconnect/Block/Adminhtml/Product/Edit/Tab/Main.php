<?php
/**
 * Webkul Odoomagentoconnect Product Tabs Main Block
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
// @codingStandardsIgnoreFile

namespace Webkul\Odoomagentoconnect\Block\Adminhtml\Product\Edit\Tab;

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
        \Webkul\Odoomagentoconnect\Model\ResourceModel\product $productMapResource,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->_productMapResource = $productMapResource;
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
        $mageProduct = $this->_productMapResource->getMageProductArray();
        $odooProduct = $this->_productMapResource->getOdooProductArray();
        $model = $this->_coreRegistry->registry('odoomagentoconnect_user');
        $productmodel = $this->_coreRegistry->registry('odoomagentoconnect_product');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('user_');

        // $baseFieldset = $form->addFieldset('base_fieldset', ['legend' => __('Product Information')]);
        $baseFieldset = $form->addFieldset('base_fieldset',['legend' => __('Product Mapping'), 'class' => 'fieldset-wide']);

        if($productmodel->getEntityId()){
            $baseFieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);
        }

        $baseFieldset->addField(
                'magento_id',
                'select',
                [
                    'label' => __('Magento Product'),
                    'title' => __('Magento Product'),
                    'name' => 'magento_id',
                    'required' => true,
                    'options' => $mageProduct
                ]
            );
        $baseFieldset->addField(
                'odoo_id',
                'select',
                [
                    'label' => __('Odoo Product'),
                    'title' => __('Odoo Product'),
                    'name' => 'odoo_id',
                    'required' => true,
                    'options' => $odooProduct
                ]
            );

        $data= $productmodel->getData();
        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }

}
