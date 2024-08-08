<?php

namespace Meetanshi\GoogleSitemap\Controller\Adminhtml\Sitemap;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Meetanshi\GoogleSitemap\Controller\Adminhtml\Sitemap;
use Meetanshi\GoogleSitemap\Model\SitemapFactory as GoogleSitemapFactory;
use Psr\Log\LoggerInterface;

/**
 * Class MassDelete
 * @package Meetanshi\GoogleSitemap\Controller\Adminhtml\Sitemap
 */
class MassDelete extends Sitemap
{
    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @var
     */
    private $directory;
    /**
     * @var
     */
    private $sitemap;
    /**
     * @var int
     */
    private $failedDelete=0;

    /**
     * @var GoogleSitemapFactory
     */
    private $googleSitemapFactory;

    /**
     * MassDelete constructor.
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

    public function execute()
    {
        try {
            $this->directory = $this->filesystem->getDirectoryWrite(DirectoryList::ROOT);
            $this->sitemap = $this->googleSitemapFactory->create();
            if (!is_array($this->getRequest()->getParams()['ids'])) {
                $ids=explode(',', $this->getRequest()->getParams()['ids']);
            } else {
                $ids=$this->getRequest()->getParams()['ids'];
            }

            foreach ($ids as $id) {
                $this->deleteSitemap($id);
            }
            $this->messageManager->addSuccessMessage(
                __('A total of %1 sitemap(s) have been deleted.', sizeof($ids)-$this->failedDelete)
            );
        } catch (FileSystemException $e) {
            ObjectManager::getInstance()->get(LoggerInterface::class)->info($e->getMessage());
        } catch (\Exception $e) {
            ObjectManager::getInstance()->get(LoggerInterface::class)->info($e->getMessage());
        } finally {
            $this->_redirect('*/*/');
        }
    }

    /**
     * @param $id
     */
    private function deleteSitemap($id)
    {
        if ($id) {
            try {
                $sitemap = $this->sitemap;
                $sitemap->load($id);
                $sitemapPath = $sitemap->getSitemapPath();
                if ($sitemapPath && $sitemapPath[0] === DIRECTORY_SEPARATOR) {
                    $sitemapPath = mb_substr($sitemapPath, 1);
                }
                $sitemapFilename = $sitemap->getSitemapFilename();
                $path = $this->directory->getRelativePath(
                    $sitemapPath . $sitemapFilename
                );
                if ($sitemap->getSitemapFilename() && $this->directory->isFile($path)) {
                    $this->directory->delete($path);
                }
                $sitemap->delete();
                return;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return;
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a sitemap to delete which id is ' . $id));
        $this->failedDelete++;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Meetanshi_GoogleSitemap::main_menu');
    }
}
