<?php

namespace Dotsqures\CustomInvoiceButton\Plugin\Widget;

use Magento\Backend\Block\Widget\Button\ButtonList;
use Magento\Backend\Block\Widget\Button\Toolbar as ToolbarContext;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\AbstractBlock;

class Context
{
    protected $coreRegistry;
    protected $authSession;
    public function __construct(
        Registry $coreRegistry,
        \Magento\Backend\Model\Auth\Session $authSession
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->authSession = $authSession;
    }

    /**
     * @param ToolbarContext $toolbar
     * @param AbstractBlock $context
     * @param ButtonList $buttonList
     * @return array
     */
    public function beforePushButtons(
        ToolbarContext $toolbar,
        \Magento\Framework\View\Element\AbstractBlock $context,
        \Magento\Backend\Block\Widget\Button\ButtonList $buttonList
    ) {
        if (!$context instanceof \Magento\Sales\Block\Adminhtml\Order\View) {
            return [$context, $buttonList];
        }
        //$user = $this->authSession->getUser();
        //$userName = $user->getUsername();
        //if($userName=='admin' || $userName=='superadmin'){
            $buttonList->remove('order_invoice');
        //}

        return [$context, $buttonList];
    }

}