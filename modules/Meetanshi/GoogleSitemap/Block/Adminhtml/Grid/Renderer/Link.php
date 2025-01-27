<?php
namespace Meetanshi\GoogleSitemap\Block\Adminhtml\Grid\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Config\Model\Config\Reader\Source\Deployed\DocumentRoot;
use Magento\Framework\DataObject;
use Magento\Framework\Filesystem;
use Meetanshi\GoogleSitemap\Model\SitemapFactory;

/**
 * Class Link
 * @package Meetanshi\GoogleSitemap\Block\Adminhtml\Grid\Renderer
 */
class Link extends AbstractRenderer
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var SitemapFactory
     */
    protected $sitemapFactory;

    /**
     * @var DocumentRoot
     */
    protected $documentRoot;

    /**
     * Link constructor.
     * @param Context $context
     * @param SitemapFactory $sitemapFactory
     * @param Filesystem $filesystem
     * @param DocumentRoot $documentRoot
     * @param array $data
     */
    public function __construct(
        Context $context,
        SitemapFactory $sitemapFactory,
        Filesystem $filesystem,
        DocumentRoot $documentRoot,
        array $data = []
    ) {
        $this->sitemapFactory = $sitemapFactory;
        $this->filesystem = $filesystem;
        $this->documentRoot = $documentRoot;

        parent::__construct($context, $data);
    }

    /**
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $sitemap = $this->sitemapFactory->create();
        $sitemap->setStoreId($row->getStoreId());
        $url = $this->escapeHtml($sitemap->getSitemapUrl(
            $row->getSitemapPath(),
            $row->getSitemapFilename()
        ));
        $fileName = preg_replace('/^\//', '', $row->getSitemapPath() . $row->getSitemapFilename());
        $documentRootPath = $this->documentRoot->getPath();
        $directory = $this->filesystem->getDirectoryRead($documentRootPath);
        if ($directory->isFile($fileName)) {
            return sprintf('<a href="%1$s">%1$s</a>', $url);
        }
        return $url;
    }
}
