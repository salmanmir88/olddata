<?php


namespace Meetanshi\GoogleSitemap\Model;

use Exception;
use Magento\Config\Model\Config\Reader\Source\Deployed\DocumentRoot;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\UrlInterface;
use Magento\Robots\Model\Config\Value;
use Magento\Sitemap\Helper\Data;
use Magento\Sitemap\Model\ResourceModel\Catalog\CategoryFactory;
use Magento\Sitemap\Model\ResourceModel\Catalog\ProductFactory;
use Magento\Sitemap\Model\ResourceModel\Cms\PageFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Meetanshi\GoogleSitemap\Helper\Data as GoogleSitemapHelper;
use Meetanshi\GoogleSitemap\Model\ResourceModel\Catalog\ProductFactory as ProductPageFactory;
use Meetanshi\GoogleSitemap\Model\ResourceModel\Cms\PageFactory as CmsPageFactory;
use Psr\Log\LoggerInterface;

/**
 * Sitemap model
 *
 * @method string getSitemapType()
 * @method \Magento\Sitemap\Model\Sitemap setSitemapType(string $value)
 * @method string getSitemapFilename()
 * @method \Magento\Sitemap\Model\Sitemap setSitemapFilename(string $value)
 * @method string getSitemapPath()
 * @method \Magento\Sitemap\Model\Sitemap setSitemapPath(string $value)
 * @method string getSitemapTime()
 * @method \Magento\Sitemap\Model\Sitemap setSitemapTime(string $value)
 * @method int getStoreId()
 * @method \Magento\Sitemap\Model\Sitemap setStoreId(int $value)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @api
 * @since 100.0.2
 */


class Sitemap extends AbstractModel implements IdentityInterface
{
    /**
     * @var GoogleSitemapHelper
     */
    private $googleSitemapHelper;

    /**
     *
     */
    const OPEN_TAG_KEY = 'start';

    /**
     *
     */
    const CLOSE_TAG_KEY = 'end';

    /**
     *
     */
    const TYPE_INDEX = 'sitemap';

    /**
     *
     */
    const TYPE_URL = 'url';

    /**
     *
     */
    const LAST_MOD_MIN_VAL = '0000-01-01 00:00:00';

    /**
     * @var
     */
    protected $_filePath;

    /**
     * @var array
     */
    protected $_sitemapItems = [];

    /**
     * @var int
     */
    protected $_sitemapIncrement = 0;

    /**
     * @var array
     */
    protected $_tags = [];

    /**
     * @var int
     */
    protected $_lineCount = 0;

    /**
     * @var int
     */
    protected $_fileSize = 0;

    /**
     * @var Filesystem\Directory\WriteInterface
     */
    protected $_directory;

    /**
     * @var
     */
    protected $_stream;

    /**
     * @var Data
     */
    protected $_sitemapData;

    /**
     * @var Escaper
     */
    protected $_escaper;

    /**
     * @var CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * @var PageFactory
     */
    protected $_cmsFactory;

    /**
     * @var DateTime
     */
    protected $_dateModel;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var array
     */
    protected $_cacheTag = [Value::CACHE_TAG];


    /**
     * @var
     */
    private $sitemapItemFactory;

    /**
     * @var
     */
    private $lastModMinTsVal;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var mixed
     */
    private $documentRoot;

    /**
     * @var
     */
    private $urls;

    /**
     * @var State
     */
    private $appState;
    /**
     * @var CmsPageFactory
     */
    private $cmsPageFactory;
    /**
     * @var Product
     */
    private $productPageFactory;

    /**
     * Sitemap constructor.
     * @param ProductPageFactory $productPageFactory
     * @param CmsPageFactory $cmsPageFactory
     * @param Context $context
     * @param Registry $registry
     * @param Escaper $escaper
     * @param Data $sitemapData
     * @param Filesystem $filesystem
     * @param CategoryFactory $categoryFactory
     * @param ProductFactory $productFactory
     * @param PageFactory $cmsFactory
     * @param DateTime $modelDate
     * @param StoreManagerInterface $storeManager
     * @param RequestInterface $request
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param GoogleSitemapHelper $googleSitemapHelper
     * @param DocumentRoot $documentRoot
     * @param State $appState
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @throws FileSystemException
     */
    public function __construct(
        ProductPageFactory $productPageFactory,
        CmsPageFactory $cmsPageFactory,
        Context $context,
        Registry $registry,
        Escaper $escaper,
        Data $sitemapData,
        Filesystem $filesystem,
        CategoryFactory $categoryFactory,
        ProductFactory $productFactory,
        PageFactory $cmsFactory,
        DateTime $modelDate,
        StoreManagerInterface $storeManager,
        RequestInterface $request,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        GoogleSitemapHelper $googleSitemapHelper,
        DocumentRoot $documentRoot,
        State $appState,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_escaper = $escaper;
        $this->_sitemapData = $sitemapData;
        $this->documentRoot = $documentRoot ?: ObjectManager::getInstance()->get(DocumentRoot::class);
        $this->filesystem = $filesystem;
        $this->_directory = $filesystem->getDirectoryWrite($this->documentRoot->getPath());
        $this->_categoryFactory = $categoryFactory;
        $this->_productFactory = $productFactory;
        $this->_cmsFactory = $cmsFactory;
        $this->_dateModel = $modelDate;
        $this->_storeManager = $storeManager;
        $this->_request = $request;
        $this->dateTime = $dateTime;
        $this->googleSitemapHelper = $googleSitemapHelper;
        $this->appState = $appState;
        $this->cmsPageFactory = $cmsPageFactory;
        $this->productPageFactory = $productPageFactory;
    }

    /**
     *
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Sitemap::class);
    }

    /**
     * @return mixed
     * @throws LocalizedException
     */
    protected function _getStream()
    {
        if ($this->_stream) {
            return $this->_stream;
        } else {
            throw new LocalizedException(__('File handler unreachable'));
        }
    }

    /**
     * Add a sitemap item to the array of sitemap items
     *
     * @param DataObject $sitemapItem
     * @return Sitemap
     * @since 100.2.0
     */
    public function addSitemapItem(DataObject $sitemapItem)
    {
        $this->_sitemapItems[] = $sitemapItem;

        return $this;
    }

    /**
     * Collect all sitemap items
     *
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws \Zend_Db_Statement_Exception
     * @since 100.2.0
     */
    public function collectSitemapItems()
    {
        /** @var $helper \Magento\Sitemap\Helper\Data */
        $helper = $this->_sitemapData;
        $storeId = $this->getStoreId();
        $this->_storeManager->setCurrentStore($storeId);

        $this->addSitemapItem(new DataObject(
            [
                'changefreq' => $helper->getCategoryChangefreq($storeId),
                'priority' => $helper->getCategoryPriority($storeId),
                'collection' => $this->_categoryFactory->create()->getCollection($storeId),
            ]
        ));

        $this->addSitemapItem(new DataObject(
            [
                'changefreq' => $helper->getProductChangefreq($storeId),
                'priority' => $helper->getProductPriority($storeId),
                'collection' => $this->productPageFactory->create()->getCollection($storeId),
            ]
        ));

        $this->addSitemapItem(new DataObject(
            [
                'changefreq' => $helper->getPageChangefreq($storeId),
                'priority' => $helper->getPagePriority($storeId),
                'collection' => $this->cmsPageFactory->create()->getCollection($storeId),
            ]
        ));
    }

    /**
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws \Zend_Db_Statement_Exception
     */
    protected function _initSitemapItems()
    {
        $this->collectSitemapItems();

        $this->_tags = [
            self::TYPE_INDEX => [
                self::OPEN_TAG_KEY => '<?xml version="1.0" encoding="UTF-8"?>' .
                    PHP_EOL .
                    '<?xml-stylesheet type="text/xsl" href="google-sitemap-index.xsl"?>' .
                    PHP_EOL .
                    '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' .
                    PHP_EOL,
                self::CLOSE_TAG_KEY => '</sitemapindex>',
            ],
            self::TYPE_URL => [
                self::OPEN_TAG_KEY => '<?xml version="1.0" encoding="UTF-8"?>' .
                    PHP_EOL .
                    '<?xml-stylesheet type="text/xsl" href="google-sitemap-index.xsl"?>' .
                    PHP_EOL .
                    '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' .
                    ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' .
                    PHP_EOL,
                self::CLOSE_TAG_KEY => '</urlset>',
            ],
        ];
    }

    /**
     * @return AbstractModel
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        $path = $this->getSitemapPath();

        if ($path && preg_match('#\.\.[\\\/]#', $path)) {
            throw new LocalizedException(__('Please define a correct path.'));
        }

        if (!$this->_directory->isExist($path)) {
            throw new LocalizedException(
                __(
                    'Please create the specified folder "%1" before saving the sitemap.',
                    $this->_escaper->escapeHtml($this->getSitemapPath())
                )
            );
        }

        if (!$this->_directory->isWritable($path)) {
            throw new LocalizedException(
                __('Please make sure that "%1" is writable by the web-server.', $this->getSitemapPath())
            );
        }

        if (!preg_match('#^[a-zA-Z0-9_\.]+$#', $this->getSitemapFilename())) {
            throw new LocalizedException(
                __(
                    'Please use only letters (a-z or A-Z), numbers (0-9) or underscores (_) in the filename.'
                    . ' No spaces or other characters are allowed.'
                )
            );
        }

        if (!preg_match('#\.xml$#', $this->getSitemapFilename())) {
            $this->setSitemapFilename($this->getSitemapFilename() . '.xml');
        }

        $this->setSitemapPath(rtrim(str_replace(str_replace('\\', '/', $this->_getBaseDir()), '', $path), '/') . '/');

        return parent::beforeSave();
    }

    /**
     * @param $store_id
     * @param bool $isInstalling
     * @return $this|Exception|FileSystemException|LocalizedException
     */
    public function generateXml($store_id, $isInstalling = false)
    {
        try {
            try {
                $this->appState->setAreaCode('adminhtml');
            } catch (LocalizedException $e) {
            }
            $this->_initSitemapItems();

            foreach ($this->_sitemapItems as $sitemapItem) {
                $changefreq = $sitemapItem->getChangefreq();
                foreach ($sitemapItem->getCollection() as $item) {
                    if ($images = $item->getImages()) {
                        foreach ($images->getCollection() as $image) {
                            $image->setUrl(htmlspecialchars($image->getUrl()));

                            if ($image->getCaption()) {
                                $image->setCaption(htmlspecialchars($image->getCaption()));
                            }
                        }
                        $images->setThumbnail(htmlspecialchars($images->getThumbnail()));
                        $images->setTitle(htmlspecialchars($images->getTitle()));
                    }

                    $xml = $this->_getSitemapRow(
                        $item->getUrl(),
                        $item->getUpdatedAt(),
                        $changefreq,
                        $item->getImages()
                    );
                    if ($this->_isSplitRequired($xml) && $this->_sitemapIncrement > 0) {
                        $this->_finalizeSitemap();
                    }
                    if (!$this->_fileSize) {
                        $this->_createSitemap();
                    }
                    $this->_writeSitemapRow($xml);
                    $this->_lineCount++;
                    $this->_fileSize += strlen($xml);
                }
            }
            if ($this->googleSitemapHelper->getConfigData(
                GoogleSitemapHelper::GOOGLE_SITEMAP_XML_SETTINGS_ENABLE_ADDITIONAL_LINKS,
                ScopeInterface::SCOPE_STORES,
                $store_id
            )) {
                $links = explode(
                    "\n",
                    $this->googleSitemapHelper->getConfigData(
                        GoogleSitemapHelper::GOOGLE_SITEMAP_XML_SETTINGS_ADDITIONAL_LINKS,
                        ScopeInterface::SCOPE_STORES,
                        $store_id
                    )
                );
                foreach ($links as $link) {
                    $link = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $link);
                    $xml = "<url><loc>$link</loc></url>";
                    $this->_writeSitemapRow($xml);
                    $this->_lineCount++;
                    $this->_fileSize += strlen($xml);
                }
            }

            $this->_finalizeSitemap();
            if ($this->_sitemapIncrement == 1) {
                $path = rtrim(
                    $this->getSitemapPath(),
                    '/'
                ) . '/' . $this->_getCurrentSitemapFilename(
                    $this->_sitemapIncrement
                );
                $destination = rtrim($this->getSitemapPath(), '/') . '/' . $this->getSitemapFilename();

                $this->_directory->renameFile($path, $destination);
            } else {
                $this->_createSitemapIndex();
            }
            $this->setSitemapTime($this->_dateModel->gmtDate('Y-m-d H:i:s'));

            $this->save();

            $this->createXslFile($this->googleSitemapHelper->getXslFileContent());

            return $this;
        } catch (FileSystemException $e) {
            ObjectManager::getInstance()->get(LoggerInterface::class)->info($e->getMessage());
            return $e;
        } catch (Exception $e) {
            ObjectManager::getInstance()->get(LoggerInterface::class)->info($e->getMessage());
            return $e;
        }
    }

    /**
     * @return mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws \Zend_Db_Statement_Exception
     */
    public function getUrls()
    {
        $this->_initSitemapItems();

        foreach ($this->_sitemapItems as $item) {
            $this->urls[]=$this->_getUrl($item->getUrl());
        }
        return $this->urls;
    }

    /**
     *
     */
    protected function _createSitemapIndex()
    {
        $this->_createSitemap($this->getSitemapFilename(), self::TYPE_INDEX);
        for ($i = 1; $i <= $this->_sitemapIncrement; $i++) {
            $xml = $this->_getSitemapIndexRow($this->_getCurrentSitemapFilename($i), $this->_getCurrentDateTime());
            $this->_writeSitemapRow($xml);
        }
        $this->_finalizeSitemap(self::TYPE_INDEX);
    }

    /**
     * @return string
     * @throws Exception
     */
    protected function _getCurrentDateTime()
    {
        return (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
    }

    /**
     * @param $row
     * @param bool $isInstalling
     * @return bool
     */
    protected function _isSplitRequired($row, $isInstalling = false)
    {
        $storeId = $this->getStoreId();

        $maximumLinesNumber=$this->googleSitemapHelper->getMaximumLinesNumber($storeId);
        $maximumFileSize=$this->googleSitemapHelper->getMaximumFileSize($storeId);

        if (!$maximumLinesNumber) {
            $maximumLinesNumber=50000;
        }
        if (!$maximumFileSize) {
            $maximumLinesNumber=10485760;
        }
        if ($isInstalling) {
            if ($this->_lineCount + 1 > 50000) {
                return true;
            }

            if ($this->_fileSize + strlen($row) > 10485760) {
                return true;
            }
        } else {
            if ($this->_lineCount + 1 > $maximumLinesNumber) {
                return true;
            }

            if ($this->_fileSize + strlen($row) > $maximumFileSize) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $url
     * @param null $lastmod
     * @param null $changefreq
     * @param null $images
     * @return string
     * @throws NoSuchEntityException
     */
    protected function _getSitemapRow($url, $lastmod = null, $changefreq = null, $images = null)
    {
        $url = $this->_getUrl($url);
        $row = '<loc>' . $this->_escaper->escapeUrl($url) . '</loc>';
        if ($lastmod) {
            $row .= '<lastmod>' . $this->_getFormattedLastmodDate($lastmod) . '</lastmod>';
        }
        if ($changefreq) {
            $row .= '<changefreq>' . $this->_escaper->escapeHtml($changefreq) . '</changefreq>';
        }
        if ($images) {
            foreach ($images->getCollection() as $image) {
                $row .= '<image:image>';
                $row .= '<image:loc>' . $this->_escaper->escapeUrl($image->getUrl()) . '</image:loc>';
                $row .= '<image:title>' . $this->_escaper->escapeHtml($images->getTitle()) . '</image:title>';
                if ($image->getCaption()) {
                    $row .= '<image:caption>' . $this->_escaper->escapeHtml($image->getCaption()) . '</image:caption>';
                }
                $row .= '</image:image>';
            }
            $row .= '<PageMap xmlns="http://www.google.com/schemas/sitemap-pagemap/1.0"><DataObject type="thumbnail">';
            $row .= '<Attribute name="name" value="' . $this->_escaper->escapeHtml($images->getTitle()) . '"/>';
            $row .= '<Attribute name="src" value="' . $this->_escaper->escapeUrl($images->getThumbnail()) . '"/>';
            $row .= '</DataObject></PageMap>';
        }

        return '<url>' . $row . '</url>';
    }


    /**
     * @param $sitemapFilename
     * @param null $lastmod
     * @return string
     * @throws NoSuchEntityException
     */
    protected function _getSitemapIndexRow($sitemapFilename, $lastmod = null)
    {
        $url = $this->getSitemapUrl($this->getSitemapPath(), $sitemapFilename);
        $row = '<loc>' . $this->_escaper->escapeUrl($url) . '</loc>';
        if ($lastmod) {
            $row .= '<lastmod>' . $this->_getFormattedLastmodDate($lastmod) . '</lastmod>';
        }

        return '<sitemap>' . $row . '</sitemap>';
    }

    /**
     * @param null $fileName
     * @param string $type
     * @throws FileSystemException
     */
    protected function _createSitemap($fileName = null, $type = self::TYPE_URL)
    {
        if (!$fileName) {
            $this->_sitemapIncrement++;
            $fileName = $this->_getCurrentSitemapFilename($this->_sitemapIncrement);
        }

        $path = rtrim($this->getSitemapPath(), '/') . '/' . $fileName;
        $this->_stream = $this->_directory->openFile($path);

        $fileHeader = sprintf($this->_tags[$type][self::OPEN_TAG_KEY], $type);
        $this->_stream->write($fileHeader);
        $this->_fileSize = strlen($fileHeader . sprintf($this->_tags[$type][self::CLOSE_TAG_KEY], $type));
    }

    /**
     * @param $row
     * @throws LocalizedException
     */
    protected function _writeSitemapRow($row)
    {
        $this->_getStream()->write($row . PHP_EOL);
    }

    /**
     * @param string $type
     */
    protected function _finalizeSitemap($type = self::TYPE_URL)
    {
        if ($this->_stream) {
            $this->_stream->write(sprintf($this->_tags[$type][self::CLOSE_TAG_KEY], $type));
            $this->_stream->close();
        }

        $this->_lineCount = 0;
        $this->_fileSize = 0;
    }

    /**
     * @param $index
     * @return string
     */
    protected function _getCurrentSitemapFilename($index)
    {
        return str_replace('.xml', '', $this->getSitemapFilename()) . '-' . $this->getStoreId() . '-' . $index . '.xml';
    }

    /**
     * @return string
     */
    protected function _getBaseDir()
    {
        return $this->_directory->getAbsolutePath();
    }

    /**
     * @param string $type
     * @return string
     * @throws NoSuchEntityException
     */
    protected function _getStoreBaseUrl($type = UrlInterface::URL_TYPE_LINK)
    {
        $store = $this->_storeManager->getStore($this->getStoreId());
        $isSecure = $store->isUrlSecure();
        return rtrim($store->getBaseUrl($type, $isSecure), '/') . '/';
    }

    /**
     * @param $url
     * @param string $type
     * @return string
     * @throws NoSuchEntityException
     */
    protected function _getUrl($url, $type = UrlInterface::URL_TYPE_LINK)
    {
        return $this->_getStoreBaseUrl($type) . ltrim($url, '/');
    }

    /**
     * @param $date
     * @return false|string
     */
    protected function _getFormattedLastmodDate($date)
    {
        if ($this->lastModMinTsVal === null) {
            $this->lastModMinTsVal = strtotime(self::LAST_MOD_MIN_VAL);
        }
        $timestamp = max(strtotime($date), $this->lastModMinTsVal);
        return date('c', $timestamp);
    }

    /**
     * @return bool|string|null
     * @throws NoSuchEntityException
     */
    protected function _getDocumentRoot()
    {
        if (PHP_SAPI === 'cli') {
            return $this->getDocumentRootFromBaseDir() ?? '';
        }
        return realpath($this->_request->getServer('DOCUMENT_ROOT'));
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    protected function _getStoreBaseDomain()
    {
        $storeParsedUrl = parse_url($this->_getStoreBaseUrl());
        $url = $storeParsedUrl['scheme'] . '://' . $storeParsedUrl['host'];

        $documentRoot = $this->_getDocumentRoot() ?: false;
        if ($documentRoot) {
            $documentRoot = trim(str_replace(DIRECTORY_SEPARATOR, '/', $documentRoot), '/');
        }
        $baseDir = trim(str_replace(DIRECTORY_SEPARATOR, '/', $this->_getBaseDir()), '/');

        if ($documentRoot !== false && strpos($baseDir, (string)$documentRoot) === 0) {
            $installationFolder = trim(str_replace($documentRoot, '', $baseDir), '/');
            $storeDomain = rtrim($url . '/' . $installationFolder, '/');
        } else {
            $url = $this->_getStoreBaseUrl(UrlInterface::URL_TYPE_WEB);
            $storeDomain = rtrim($url, '/');
        }

        return $storeDomain;
    }

    /**
     * @param $sitemapPath
     * @param $sitemapFileName
     * @return string
     * @throws NoSuchEntityException
     */
    public function getSitemapUrl($sitemapPath, $sitemapFileName)
    {
        return $this->_getStoreBaseDomain() . str_replace('//', '/', $sitemapPath . '/' . $sitemapFileName);
    }

    /**
     * @return array|string[]
     */
    public function getIdentities()
    {
        return [
            Value::CACHE_TAG . '_' . $this->getStoreId(),
        ];
    }

    /**
     * @return string|null
     * @throws NoSuchEntityException
     */
    private function getDocumentRootFromBaseDir()
    {
        $basePath = rtrim(parse_url($this->_getStoreBaseUrl(UrlInterface::URL_TYPE_WEB), PHP_URL_PATH) ?: '', '/');
        $basePath = str_replace('/', DIRECTORY_SEPARATOR, $basePath);
        $basePath = rtrim($basePath, DIRECTORY_SEPARATOR);
        $baseDir = rtrim($this->_getBaseDir(), DIRECTORY_SEPARATOR);
        $length = strlen($basePath);
        if (!$length) {
            $documentRoot = $baseDir;
        } elseif (substr($baseDir, -$length) === $basePath) {
            $documentRoot = rtrim(substr($baseDir, 0, strlen($baseDir) - $length), DIRECTORY_SEPARATOR);
        } else {
            $documentRoot = null;
        }
        return $documentRoot;
    }

    /**
     * @param $content
     * @throws FileSystemException
     */
    protected function createXslFile($content)
    {
        $path = rtrim($this->getSitemapPath(), '/') . '/' . 'google-sitemap-index.xsl';
        $this->_stream = $this->_directory->openFile($path);
        $this->_stream->write($content);
    }
}
