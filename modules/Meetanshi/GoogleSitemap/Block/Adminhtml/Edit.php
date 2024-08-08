<?php

namespace Meetanshi\GoogleSitemap\Block\Adminhtml;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Registry;
use Meetanshi\GoogleSitemap\Helper\Data as GoogleSitemapHelper;

/**
 * Class Edit
 * @package Meetanshi\GoogleSitemap\Block\Adminhtml
 */
class Edit extends Container
{

    /**
     * @var Registry|null
     */
    protected $_coreRegistry = null;

    /**
     * @var GoogleSitemapHelper
     */
    private $googleSitemapHelper;

    /**
     * Edit constructor.
     * @param Context $context
     * @param Registry $registry
     * @param GoogleSitemapHelper $googleSitemapHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        GoogleSitemapHelper $googleSitemapHelper,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->googleSitemapHelper = $googleSitemapHelper;
        parent::__construct($context, $data);
    }

    /**
     *
     */
    protected function _construct()
    {
        $this->_objectId = 'sitemap_id';
        $this->_controller = 'adminhtml';
        $this->_blockGroup = 'Meetanshi_GoogleSitemap';
        parent::_construct();
        if ($this->googleSitemapHelper->getConfigData(GoogleSitemapHelper::GOOGLE_SITEMAP_XML_SETTINGS_XML_SITEMAP)) {
            $this->buttonList->add(
                'generate',
                [
                    'label' => __('Save & Generate'),
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => [
                                'event' => 'save',
                                'target' => '#edit_form',
                                'eventData' => ['action' => ['args' => ['generate' => '1']]],
                            ],
                        ],
                    ],
                    'class' => 'add'
                ]
            );
        }
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('sitemap_sitemap')->getId()) {
            return __('Edit Site Map');
        } else {
            return __('New Site Map');
        }
    }
}
