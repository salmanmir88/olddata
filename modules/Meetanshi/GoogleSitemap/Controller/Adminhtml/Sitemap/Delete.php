<?php

namespace Meetanshi\GoogleSitemap\Controller\Adminhtml\Sitemap;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Meetanshi\GoogleSitemap\Controller\Adminhtml\Sitemap;
use Meetanshi\GoogleSitemap\Model\SitemapFactory as GoogleSitemapFactory;

/**
 * Class Delete
 * @package Meetanshi\GoogleSitemap\Controller\Adminhtml\Sitemap
 */
class Delete extends Sitemap
{
    /**
     * @var
     */
    private $filesystem;
    /**
     * @var GoogleSitemapFactory
     */
    private $googleSitemapFactory;

    /**
     * Delete constructor.
     * @param Context $context
     * @param GoogleSitemapFactory $googleSitemapFactory
     * @param Filesystem $filesystem
     */
    public function __construct(
        Context $context,
        GoogleSitemapFactory $googleSitemapFactory,
        Filesystem $filesystem
    ) {
        parent::__construct($context);
        $this->googleSitemapFactory = $googleSitemapFactory;
        $this->filesystem = $filesystem;
    }

    /**
     * @throws FileSystemException
     */
    public function execute()
    {
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $id = $this->getRequest()->getParam('sitemap_id');
        if ($id) {
            try {
                $sitemap = $this->googleSitemapFactory->create();
                $sitemap->load($id);
                $sitemapPath = $sitemap->getSitemapPath();
                if ($sitemapPath && $sitemapPath[0] === DIRECTORY_SEPARATOR) {
                    $sitemapPath = mb_substr($sitemapPath, 1);
                }
                $sitemapFilename = $sitemap->getSitemapFilename();
                $path = $directory->getRelativePath(
                    $sitemapPath . $sitemapFilename
                );
                if ($sitemap->getSitemapFilename() && $directory->isFile($path)) {
                    $directory->delete($path);
                }
                $sitemap->delete();
                $this->messageManager->addSuccessMessage(__('You deleted the sitemap.'));
                $this->_redirect('google_sitemap/*/');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_redirect('google_sitemap/*/edit', ['sitemap_id' => $id]);
                return;
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a sitemap to delete.'));
        $this->_redirect('google_sitemap/*/');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Meetanshi_GoogleSitemap::main_menu');
    }
}
