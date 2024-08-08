<?php

namespace Meetanshi\GoogleSitemap\Controller\Adminhtml\Sitemap;

use \Magento\Sitemap\Controller\Adminhtml\Sitemap\NewAction as NewActionParent;

/**
 * Class NewAction
 * @package Meetanshi\GoogleSitemap\Controller\Adminhtml\Sitemap
 */
class NewAction extends NewActionParent
{

    public function execute()
    {
        parent::execute();
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Meetanshi_GoogleSitemap::main_menu');
    }
}
