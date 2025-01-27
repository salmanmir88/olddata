<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-feed
 * @version   1.1.38
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Feed\Controller\Adminhtml\Dynamic;

use Magento\Backend\App\Action;
use Magento\Framework\Registry;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Mirasvit\Feed\Model\Dynamic\AttributeFactory;

abstract class Attribute extends Action
{
    /**
     * @var AttributeFactory
     */
    protected $attributeFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @param AttributeFactory $variableFactory
     * @param Registry         $registry
     * @param Context          $context
     * @param ForwardFactory   $resultForwardFactory
     */
    public function __construct(
        AttributeFactory $variableFactory,
        Registry         $registry,
        Context          $context,
        ForwardFactory   $resultForwardFactory
    ) {
        $this->attributeFactory     = $variableFactory;
        $this->registry             = $registry;
        $this->context              = $context;
        $this->resultForwardFactory = $resultForwardFactory;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     * @param \Magento\Backend\Model\View\Result\Page\Interceptor $resultPage
     * @return \Magento\Backend\Model\View\Result\Page\Interceptor
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Magento_Catalog::catalog');
        $resultPage->getConfig()->getTitle()->prepend(__('Advanced Product Feeds'));
        $resultPage->getConfig()->getTitle()->prepend(__('Dynamic Attributes'));

        return $resultPage;
    }

    /**
     * Current feed model
     * @return \Mirasvit\Feed\Model\Dynamic\Attribute
     */
    protected function initModel()
    {
        $model = $this->attributeFactory->create();

        if ($this->getRequest()->getParam('id')) {
            $model->load($this->getRequest()->getParam('id'));
        }

        $this->registry->register('current_model', $model);

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Mirasvit_Feed::feed_dynamic_attribute');
    }
}
