<?php
/**
 * Webkul Odoomagentoconnect Category Tabs Main Block
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
// @codingStandardsIgnoreFile

namespace Webkul\Odoomagentoconnect\Block\Adminhtml\Category\Edit\Tab;
use Webkul\Odoomagentoconnect\Model\Category;
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
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Category $categoryMapResource,
        array $data = []
    ) {
        $this->_categoryMapResource = $categoryMapResource;
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
        $mageCategory = $this->_categoryMapResource->getMageCategoryArray();
        $odooCategory = $this->_categoryMapResource->getOdooCategoryArray();
        $model = $this->_coreRegistry->registry('odoomagentoconnect_user');
        $categorymodel = $this->_coreRegistry->registry('odoomagentoconnect_category');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('user_');

        $baseFieldset = $form->addFieldset('base_fieldset',['legend' => __('Category Mapping'), 'class' => 'fieldset-wide']);

        if($categorymodel->getEntityId()){
            $baseFieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);
        }

        $baseFieldset->addField(
                'magento_id',
                'select',
                [
                    'label' => __('Magento Category'),
                    'title' => __('Magento Category'),
                    'name' => 'magento_id',
                    'required' => true,
                    'options' => $mageCategory
                ]
            );
        $baseFieldset->addField(
                'odoo_id',
                'select',
                [
                    'label' => __('Odoo Category'),
                    'title' => __('Odoo Category'),
                    'name' => 'odoo_id',
                    'required' => true,
                    'options' => $odooCategory
                ]
            );

        $data= $categorymodel->getData();
        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }




}
