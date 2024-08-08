<?php

namespace  Meetanshi\GoogleSitemap\Controller\Adminhtml\Sitemap;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Validator\StringLength;
use Magento\MediaStorage\Model\File\Validator\AvailablePath;
use Magento\Sitemap\Helper\Data;
use Meetanshi\GoogleSitemap\Helper\Data as GoogleSitemapHelper;
use Meetanshi\GoogleSitemap\Model\Sitemap;
use Meetanshi\GoogleSitemap\Model\SitemapFactory as GoogleSitemapFactory;
use Psr\Log\LoggerInterface;
use Zend_Validate_Exception;

/**
 * Class Save
 * @package Meetanshi\GoogleSitemap\Controller\Adminhtml\Sitemap
 */
class Save extends Action
{
    /**
     *
     */
    const MAX_FILENAME_LENGTH = 32;
    /**
     * @var GoogleSitemapFactory
     */
    private $googleSitemapFacotry;

    /**
     * @var GoogleSitemapHelper
     */
    private $googleSitemapHelper;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var StringLength
     */
    private $stringValidator;

    /**
     * @var AvailablePath
     */
    private $pathValidator;

    /**
     * @var Data
     */
    private $sitemapHelper;

    /**
     * Save constructor.
     * @param Context $context
     * @param GoogleSitemapFactory $googleSitemapFacotry
     * @param GoogleSitemapHelper $googleSitemapHelper
     * @param StringLength $stringValidator
     * @param AvailablePath $pathValidator
     * @param Data $sitemapHelper
     * @param Filesystem $filesystem
     */
    public function __construct(
        Context $context,
        GoogleSitemapFactory $googleSitemapFacotry,
        GoogleSitemapHelper $googleSitemapHelper,
        StringLength $stringValidator,
        AvailablePath $pathValidator,
        Data $sitemapHelper,
        Filesystem $filesystem
    ) {
        parent::__construct($context);
        $this->googleSitemapFacotry = $googleSitemapFacotry;
        $this->googleSitemapHelper = $googleSitemapHelper;
        $this->filesystem = $filesystem;
        $this->stringValidator = $stringValidator;
        $this->pathValidator = $pathValidator;
        $this->sitemapHelper = $sitemapHelper;
    }

    /**
     * @param array $data
     * @return bool
     * @throws Zend_Validate_Exception
     */
    protected function validatePath(array $data)
    {
        if (!empty($data['sitemap_filename']) && !empty($data['sitemap_path'])) {
            $data['sitemap_path'] = '/' . ltrim($data['sitemap_path'], '/');
            $path = rtrim($data['sitemap_path'], '\\/') . '/' . $data['sitemap_filename'];
            $this->pathValidator->setPaths($this->sitemapHelper->getValidPaths());
            if (!$this->pathValidator->isValid($path)) {
                foreach ($this->pathValidator->getMessages() as $message) {
                    $this->messageManager->addErrorMessage($message);
                }
                $this->_session->setFormData($data);
                return false;
            }

            $filename = rtrim($data['sitemap_filename']);
            $this->stringValidator->setMax(self::MAX_FILENAME_LENGTH);
            if (!$this->stringValidator->isValid($filename)) {
                foreach ($this->stringValidator->getMessages() as $message) {
                    $this->messageManager->addErrorMessage($message);
                }
                $this->_session->setFormData($data);
                return false;
            }
        }
        return true;
    }

    /**
     * @param Sitemap $model
     */
    protected function clearGoogleSitemap(Sitemap $model)
    {
        try {
            $directory = $this->filesystem->getDirectoryWrite(DirectoryList::ROOT);
            if ($this->getRequest()->getParam('sitemap_id')) {
                $model->load($this->getRequest()->getParam('sitemap_id'));
                $fileName = $model->getSitemapFilename();

                $path = $model->getSitemapPath() . '/' . $fileName;
                if ($fileName && $directory->isFile($path)) {
                    $directory->delete($path);
                }
            }
        } catch (FileSystemException $e) {
            ObjectManager::getInstance()->get(LoggerInterface::class)->info($e->getMessage());
        } catch (\Exception $e) {
            ObjectManager::getInstance()->get(LoggerInterface::class)->info($e->getMessage());
        }
    }

    /**
     * @param $data
     * @return bool|mixed
     */
    public function saveData($data)
    {
        $model = $this->googleSitemapFacotry->create();
        $this->clearGoogleSitemap($model);
        $model->setData($data);

        try {
            $model->save();
            $this->messageManager->addSuccessMessage(__('You saved the sitemap.'));
            $this->_session->setFormData(false);
            return $model->getId();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_session->setFormData($data);
        }
        return false;
    }

    /**
     * @param $id
     * @return Controller\ResultInterface
     */
    protected function getResult($id)
    {
        $resultRedirect = $this->resultFactory->create(Controller\ResultFactory::TYPE_REDIRECT);
        if ($id) {
            if ($this->getRequest()->getParam('back')) {
                $resultRedirect->setPath('google_sitemap/*/edit', ['sitemap_id' => $id]);
                return $resultRedirect;
            }
            if ($this->getRequest()->getParam('generate')) {
                $this->getRequest()->setParam('sitemap_id', $id);
                return $this->resultFactory->create(Controller\ResultFactory::TYPE_FORWARD)
                    ->forward('generate');
            }
            $resultRedirect->setPath('google_sitemap/*/');
            return $resultRedirect;
        }
        $resultRedirect->setPath(
            'google_sitemap/*/edit',
            ['sitemap_id' => $this->getRequest()->getParam('sitemap_id')]
        );
        return $resultRedirect;
    }

    /**
     * @return ResponseInterface|Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(Controller\ResultFactory::TYPE_REDIRECT);
        try {
            $data = $this->getRequest()->getPostValue();
            if ($data) {
                if (!$this->validatePath($data)) {
                    $resultRedirect->setPath(
                        'google_sitemap/*/edit',
                        ['sitemap_id' => $this->getRequest()->getParam('sitemap_id')]
                    );
                    return $resultRedirect;
                }
                return $this->getResult($this->saveData($data));
            }
            $resultRedirect->setPath('google_sitemap/*/');
            return $resultRedirect;
        } catch (\Exception $e) {
            ObjectManager::getInstance()->get(LoggerInterface::class)->info($e->getMessage());
        } finally {
            $resultRedirect->setPath('google_sitemap/*/');
            return $resultRedirect;
        }
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Meetanshi_GoogleSitemap::main_menu');
    }
}
