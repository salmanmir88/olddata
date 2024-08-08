<?php
/**
 * Copyright © Dotsqures All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace Dotsqures\CustomInvoiceButton\Plugin\Block\Adminhtml\Order;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\ObjectManagerInterface;
    
class View extends \Magento\Sales\Block\Adminhtml\Order\View
{    

    protected $object_manager;
    
    protected $_backendUrl;
 
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        ObjectManagerInterface $om,
        UrlInterface $backendUrl,
        array $data = []
    ) {
        $this->object_manager = $om;
        $this->_backendUrl = $backendUrl;
    }

    public function beforeSetLayout(\Magento\Sales\Block\Adminhtml\Order\View $view)
    {
        $message ='Are you sure you want to do this?';
        $url = $this->_backendUrl->getUrl('custinvoice/index/index/',['order_id' => $view->getOrderId()]);
        $view->addButton(
            'order_myaction',
            [
                'label' => __('Invoice'),
                'class' => 'myclass',
                'onclick' => "confirmSetLocation('{$message}', '{$url}')"
            ]
        );
    }
}