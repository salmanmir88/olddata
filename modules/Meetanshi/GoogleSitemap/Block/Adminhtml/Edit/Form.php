<?php
namespace Meetanshi\GoogleSitemap\Block\Adminhtml\Edit;

use Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sitemap\Block\Adminhtml\Edit\Form as FormParent;

/**
 * Class Form
 * @package Meetanshi\GoogleSitemap\Block\Adminhtml\Edit
 */
class Form extends FormParent
{
    /**
     * @var
     */
    protected $_systemStore;

    /**
     * @return $this|FormParent
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $model = $this->_coreRegistry->registry('sitemap_sitemap');

        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $fieldset = $form->addFieldset('add_sitemap_form', ['legend' => __('Sitemap')]);

        if ($model->getId()) {
            $fieldset->addField('sitemap_id', 'hidden', ['name' => 'sitemap_id']);
        }

        $fieldset->addField(
            'sitemap_filename',
            'text',
            [
                'label' => __('File Name'),
                'name' => 'sitemap_filename',
                'required' => true,
                'note' => __('example: sitemap.xml'),
                'value' => $model->getSitemapFilename(),
                'class' => 'validate-length maximum-length-32'
            ]
        );

        $fieldset->addField(
            'sitemap_path',
            'text',
            [
                'label' => __('Path'),
                'name' => 'sitemap_path',
                'required' => true,
                'note' => __('example: "/sitemap/" or "/" for base path (path must be writeable)'),
                'value' => $model->getSitemapPath()
            ]
        );

        if (!$this->_storeManager->hasSingleStore()) {
            $field = $fieldset->addField(
                'store_id',
                'select',
                [
                    'label' => __('Store View'),
                    'title' => __('Store View'),
                    'name' => 'store_id',
                    'required' => true,
                    'value' => $model->getStoreId(),
                    'values' => $this->_systemStore->getStoreValuesForForm()
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                Element::class
            );
            $field->setRenderer($renderer);
        } else {
            $fieldset->addField(
                'store_id',
                'hidden',
                ['name' => 'store_id', 'value' => $this->_storeManager->getStore(true)->getId()]
            );
            $model->setStoreId($this->_storeManager->getStore(true)->getId());
        }

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return $this;
    }
}
