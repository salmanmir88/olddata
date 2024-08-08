<?php
/**
 * Webkul Odoomagentoconnect Currency Tabs Main Block
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
// @codingStandardsIgnoreFile

namespace Webkul\Odoomagentoconnect\Block\Adminhtml\Currency\Edit\Tab;
use Webkul\Odoomagentoconnect\Helper\Connection;

/**
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
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Currency $currencyMapResource,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->_currencyMapResource = $currencyMapResource;
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
        $mageCurrency = $this->_currencyMapResource->getMageCurrencyArray();
        $odooCurrency = $this->_currencyMapResource->getOdooCurrencyArray();
        $model = $this->_coreRegistry->registry('odoomagentoconnect_user');
        $currencymodel = $this->_coreRegistry->registry('odoomagentoconnect_currency');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('user_');

        // $baseFieldset = $form->addFieldset('base_fieldset', ['legend' => __('Currency Information')]);
        $baseFieldset = $form->addFieldset('base_fieldset',['legend' => __('Currency Mapping'), 'class' => 'fieldset-wide']);

        if($currencymodel->getEntityId()){
            $baseFieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);
        }

        $baseFieldset->addField(
                'magento_id',
                'select',
                [
                    'label' => __('Magento Currency'),
                    'title' => __('Magento Currency'),
                    'name' => 'magento_id',
                    'required' => true,
                    'options' => $mageCurrency
                ]
            );
        $baseFieldset->addField(
                'odoo_id',
                'select',
                [
                    'label' => __('Odoo Currency'),
                    'title' => __('Odoo Currency'),
                    'name' => 'odoo_id',
                    'required' => true,
                    'options' => $odooCurrency
                ]
            );
        $data= $currencymodel->getData();
        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
