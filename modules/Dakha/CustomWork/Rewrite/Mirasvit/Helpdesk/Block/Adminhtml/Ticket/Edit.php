<?php
/**
 * Copyright © CustomWork All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dakha\CustomWork\Rewrite\Mirasvit\Helpdesk\Block\Adminhtml\Ticket;

use Mirasvit\Helpdesk\Model\Config as Config;

class Edit extends \Mirasvit\Helpdesk\Block\Adminhtml\Ticket\Edit
{
   /**
     * @var \Mirasvit\Helpdesk\Model\Config
     */
    protected $config;

    /**
     * @var \Mirasvit\Helpdesk\Model\Config\Wysiwyg
     */
    protected $configWysiwyg;

    /**
     * @var \Mirasvit\Helpdesk\Helper\Permission
     */
    protected $helpdeskPermission;

    /**
     * @var \Magento\Backend\Model\Url
     */
    protected $backendUrlManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Backend\Block\Widget\Context
     */
    protected $context;
    /**
     * @var \Mirasvit\Helpdesk\Service\Config\RmaConfig
     */
    private $rmaConfig;
    /**
     * @var \Mirasvit\Helpdesk\Model\ResourceModel\Ticket\Grid\Collection
     */
    private $gridCollection;

    /**
     * @param \Mirasvit\Helpdesk\Model\ResourceModel\Ticket\Grid\Collection $gridCollection
     * @param \Mirasvit\Helpdesk\Model\Config $config
     * @param \Mirasvit\Helpdesk\Model\Config\Wysiwyg $configWysiwyg
     * @param \Mirasvit\Helpdesk\Service\Config\RmaConfig $rmaConfig
     * @param \Mirasvit\Helpdesk\Helper\Permission $helpdeskPermission
     * @param \Magento\Backend\Model\Url $backendUrlManager
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Mirasvit\Helpdesk\Model\ResourceModel\Ticket\Grid\Collection $gridCollection,
        \Mirasvit\Helpdesk\Model\Config $config,
        \Mirasvit\Helpdesk\Model\Config\Wysiwyg $configWysiwyg,
        \Mirasvit\Helpdesk\Service\Config\RmaConfig $rmaConfig,
        \Mirasvit\Helpdesk\Helper\Permission $helpdeskPermission,
        \Magento\Backend\Model\Url $backendUrlManager,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->gridCollection     = $gridCollection;
        $this->config             = $config;
        $this->configWysiwyg      = $configWysiwyg;
        $this->rmaConfig          = $rmaConfig;
        $this->helpdeskPermission = $helpdeskPermission;
        $this->backendUrlManager  = $backendUrlManager;
        $this->registry           = $registry;
        $this->context            = $context;

        parent::__construct($gridCollection, $config, $configWysiwyg, $rmaConfig, $helpdeskPermission, $backendUrlManager, $registry, $context, $data);
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_ticket';
        $this->_blockGroup = 'Mirasvit_Helpdesk';
        $this->buttonList->remove('delete');
        $this->buttonList->remove('reset');
        $this->buttonList->remove('save');

        if ($this->getTicket() && $this->getTicket()->getId()) {
            if(!$this->getTicket()->getPermanentClosed()){
             $this->addPrevButton();
             $this->addNextButton();
             $this->addRmaButton();   
             $this->addPermanentClosedNewButton();
             $this->addCreateNewButton();
            }
        }

        return $this;
    }

    /**
     *
     */
    protected function setupButtons()
    {
        $ticket = $this->getTicket();
        if ($ticket && !$this->getTicket()->getPermanentClosed()) {
                $this->addEditButtons();
        } else {
                $this->addNewButtons();
        }
    }

    /**
     * @return void
     */
    public function addCreateNewButton()
    {
        if(empty($this->getTicket())){
            $this->buttonList->add(
                'save_and_new',
                [
                    'label'   => __('Create New Ticket'),
                    'onclick' => 'var win=window.open(\'' . $this->getNewTicketUrl() . '\', \'_blank\'); win.focus();',
                ]
            );
        }
        if($this->getTicket() && !$this->getTicket()->getPermanentClosed()){
            $this->buttonList->add(
                'save_and_new',
                [
                    'label'   => __('Create New Ticket'),
                    'onclick' => 'var win=window.open(\'' . $this->getNewTicketUrl() . '\', \'_blank\'); win.focus();',
                ]
            );
        }
    }

    /**
     * @return void
     */
    public function addPermanentClosedNewButton()
    {
        $param['ticket_id'] = $this->getTicket()->getId();
        $url = $this->context->getUrlBuilder()->getUrl('customwork/index/permanentclosed', $param);
        $this->buttonList->add(
            'permanent_closed',
            [
                'label'   => __('Permanent Closed'),
                'onclick' => 'setLocation(\'' . $url . '\')',
            ]
        );
    }

}
