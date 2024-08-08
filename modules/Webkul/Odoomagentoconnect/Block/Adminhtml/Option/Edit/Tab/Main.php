<?php
/**
 * Webkul Odoomagentoconnect Attribute Tabs Main Block
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

// @codingStandardsIgnoreFile

namespace Webkul\Odoomagentoconnect\Block\Adminhtml\Option\Edit\Tab;
use Webkul\Odoomagentoconnect\Model\Option;
use Webkul\Odoomagentoconnect\Helper\Connection;

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic
{
    const CURRENT_OPTION_PASSWORD_FIELD = 'current_password';

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_authSession;

    /**
     * @var \Magento\Framework\Locale\ListsInterface
     */
    protected $_LocaleLists;
    protected $_objectManager;



    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\Locale\ListsInterface $localeLists
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Locale\ListsInterface $localeLists,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Option $optionMapping,
        array $data = []
    ) {
        $this->_authSession = $authSession;
        $this->_LocaleLists = $localeLists;
        $this->_objectManager = $objectManager;
        $this->_optionMapping = $optionMapping;

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
        /** @var $model \Magento\User\Model\User */
        $mageOption = $this->_optionMapping->getMageOptionArray();
        $odooOption = $this->_optionMapping->getOdooOptionArray();
        $model = $this->_coreRegistry->registry('odoomagentoconnect_user');
        $optionmodel = $this->_coreRegistry->registry('odoomagentoconnect_option');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('user_');

        // $baseFieldset = $form->addFieldset('base_fieldset', ['legend' => __('Option Information')]);
        $baseFieldset = $form->addFieldset('base_fieldset',['legend' => __('Option Mapping'), 'class' => 'fieldset-wide']);

        if($optionmodel->getEntityId()){
            $baseFieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);
        }

        $baseFieldset->addField(
                'magento_id',
                'select',
                [
                    'label' => __('Magento Option'),
                    'title' => __('Magento Option'),
                    'name' => 'magento_id',
                    'required' => true,
                    'options' => $mageOption
                ]
            );
        $baseFieldset->addField(
                'odoo_id',
                'select',
                [
                    'label' => __('Odoo Option'),
                    'title' => __('Odoo Option'),
                    'name' => 'odoo_id',
                    'required' => true,
                    'options' => $odooOption
                ]
            );

        //$data = $model->getData();
        $data= $optionmodel->getData();
        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
