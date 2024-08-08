<?php
namespace Meetanshi\GoogleSitemap\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

/**
 * Class Sitemap
 * @package Meetanshi\GoogleSitemap\Block\Adminhtml
 */
class Sitemap extends Container
{
    /**
     *
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_sitemap';
        $this->_blockGroup = 'Meetanshi_GoogleSitemap';
        $this->_headerText = __('XML Sitemap');
        $this->_addButtonLabel = __('Add Sitemap');
        parent::_construct();
    }
}
