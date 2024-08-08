<?php

namespace Meetanshi\GoogleSitemap\Block\Adminhtml\Grid\Renderer;

use Magento\Framework\DataObject;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\Action as ActionParent;

/**
 * Class Action
 * @package Meetanshi\GoogleSitemap\Block\Adminhtml\Grid\Renderer
 */
class Action extends ActionParent
{
    /**
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $this->getColumn()->setActions(
            [
                [
                    'url' => $this->getUrl(
                        'google_sitemap/sitemap/generate',
                        ['sitemap_id' => $row->getSitemapId()]
                    ),
                    'caption' => __('Generate'),
                ],
            ]
        );
        return parent::render($row);
    }
}
