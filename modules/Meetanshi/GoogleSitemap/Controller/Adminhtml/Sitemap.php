<?php
namespace Meetanshi\GoogleSitemap\Controller\Adminhtml;

use Magento\Backend\App\Action;

/**
 * Class Sitemap
 * @package Meetanshi\GoogleSitemap\Controller\Adminhtml
 */
abstract class Sitemap extends Action
{

    const ADMIN_RESOURCE = 'Meetanshi_GoogleSitemap::sitemap';

    /**
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Meetanshi_GoogleSitemap::main_menu'
        )->_addBreadcrumb(
            __('Catalog'),
            __('Catalog')
        )->_addBreadcrumb(
            __('XML Sitemap'),
            __('XML Sitemap')
        );
        return $this;
    }
}
