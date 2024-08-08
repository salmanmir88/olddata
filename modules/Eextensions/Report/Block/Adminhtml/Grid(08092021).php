<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Eextensions\Report\Block\Adminhtml;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\Url\DecoderInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Stdlib\Parameters;
use Magento\Sales\Model\ResourceModel\Order\Status\Collection as OrderStatusCollection;

/**
 * Backend report grid block
 *
 * @api
 * @author     Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Grid extends \Magento\Reports\Block\Adminhtml\Grid
{
    /**
     * @var DecoderInterface
     */
    private $urlDecoder;

    /**
     * @var Parameters
     */
    private $parameters;

    /**
     * Should Store Switcher block be visible
     *
     * @var bool
     */
    protected $_storeSwitcherVisibility = true;

    /**
     * Should Date Filter block be visible
     *
     * @var bool
     */
    protected $_dateFilterVisibility = true;

    /**
     * Filters array
     *
     * @var array
     */
    protected $_filters = [];

    /**
     * Default filters values
     *
     * @var array
     */
    protected $_defaultFilters = ['report_from' => '', 'report_to' => '', 'order_status' => '0', 'report_period' => 'day'];
	
	private $orderStatusCollection;

    /**
     * Sub-report rows count
     *
     * @var int
     */
    protected $_subReportSize = 5;

    /**
     * Errors messages aggregated array
     *
     * @var array
     */
    protected $_errors = [];

    /**
     * Block template file name
     *
     * @var string
     */
    protected $_template = 'Eextensions_Report::grid.phtml';

    /**
     * Filter values array
     *
     * @var array
     */
    protected $_filterValues;

    /**
     * @param Context $context
     * @param Data $backendHelper
     * @param array $data
     * @param DecoderInterface|null $urlDecoder
     * @param Parameters|null $parameters
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        array $data = [],
        DecoderInterface $urlDecoder = null,
        Parameters $parameters = null,
		OrderStatusCollection $orderStatusCollection
    ) {
        $this->urlDecoder = $urlDecoder ?? ObjectManager::getInstance()->get(
            DecoderInterface::class
        );

        $this->parameters = $parameters ?? ObjectManager::getInstance()->get(
            Parameters::class
        );

        parent::__construct($context, $backendHelper, $data);
		$this->orderStatusCollection=$orderStatusCollection;
    }

    /**
     * Apply sorting and filtering to collection
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _prepareCollection()
    {
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/report_filter.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);
		
        $filter = $this->getParam($this->getVarNameFilter(), null);

        if (null === $filter) {
            $filter = $this->_defaultFilter;
        }

        if (is_string($filter)) {
            // this is a replacement for base64_decode()
            $filter = $this->urlDecoder->decode($filter);

            // this is a replacement for parse_str()
            $this->parameters->fromString(urldecode($filter));
            $data = $this->parameters->toArray();

            if (!isset($data['report_from'])) {
                // Get records for the past month
                $date = new \DateTime('-1 month');
                $data['report_from'] = $this->_localeDate->formatDateTime(
                    $date,
                    \IntlDateFormatter::SHORT,
                    \IntlDateFormatter::NONE
                );
            }

            if (!isset($data['report_to'])) {
                $date = new \DateTime();
                $data['report_to'] = $this->_localeDate->formatDateTime(
                    $date,
                    \IntlDateFormatter::SHORT,
                    \IntlDateFormatter::NONE
                );
            }

            $this->_setFilterValues($data);
        } elseif ($filter && is_array($filter)) {
            $this->_setFilterValues($filter);
        } elseif (0 !== count($this->_defaultFilter)) {
            $this->_setFilterValues($this->_defaultFilter);
        }

        /** @var $collection \Magento\Reports\Model\ResourceModel\Report\Collection */
        $collection = $this->getCollection();
        if ($collection) {
            $collection->setPeriod($this->getFilter('report_period'));
            // $collection->setStatus($this->getFilter('order_status'));
			
			// $logger->info("filter value -- ". __FILE__ . " :: ".__LINE__); 
			// $logger->info($this->getFilter('order_status'));

            if ($this->getFilter('report_from') && $this->getFilter('report_to')) {
                /**
                 * Validate from and to date
                 */
                try {
                    $from = $this->_localeDate->date($this->getFilter('report_from'), null, true, false);
                    $to = $this->_localeDate->date($this->getFilter('report_to'), null, true, false);
                    $collection->setInterval($from, $to);
                } catch (\Exception $e) {
                    $this->_errors[] = __('Invalid date specified');
                }
            }

            $collection->setStoreIds($this->_getAllowedStoreIds());

            if ($this->getSubReportSize() !== null) {
                $collection->setPageSize($this->getSubReportSize());
            }

            $this->_eventManager->dispatch(
                'adminhtml_widget_grid_filter_collection',
                ['collection' => $this->getCollection(), 'filter_values' => $this->_filterValues]
            );
        }

        return $this;
    }
	
	public function getStatusValue()
    {
		$filter = $this->getParam($this->getVarNameFilter(), null);

        if (null === $filter) {
            $filter = $this->_defaultFilter;
        }

        if (is_string($filter)) {
            // this is a replacement for base64_decode()
            $filter = $this->urlDecoder->decode($filter);

            // this is a replacement for parse_str()
            $this->parameters->fromString(urldecode($filter));
            $data = $this->parameters->toArray();

            if (!isset($data['report_from'])) {
                // Get records for the past month
                $date = new \DateTime('-1 month');
                $data['report_from'] = $this->_localeDate->formatDateTime(
                    $date,
                    \IntlDateFormatter::SHORT,
                    \IntlDateFormatter::NONE
                );
            }

            if (!isset($data['report_to'])) {
                $date = new \DateTime();
                $data['report_to'] = $this->_localeDate->formatDateTime(
                    $date,
                    \IntlDateFormatter::SHORT,
                    \IntlDateFormatter::NONE
                );
            }

            $this->_setFilterValues($data);
        } elseif ($filter && is_array($filter)) {
            $this->_setFilterValues($filter);
        } elseif (0 !== count($this->_defaultFilter)) {
            $this->_setFilterValues($this->_defaultFilter);
        }
        $order_status = $this->getFilter('order_status');
		
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/report_filter.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);
		
		$logger->info("getStatusValue -- ". __FILE__ . " :: ".__LINE__); 
		$logger->info($order_status);
		
        return $order_status;
		
		// "order.state = 'complete'";
    }

    /**
     * Get allowed stores
     *
     * @return array|\int[]
     */
    protected function _getAllowedStoreIds()
    {
        /**
         * Getting and saving store ids for website & group
         */
        $storeIds = [];
        if ($this->getRequest()->getParam('store')) {
            $storeIds = [$this->getParam('store')];
        } elseif ($this->getRequest()->getParam('website')) {
            $storeIds = $this->_storeManager->getWebsite($this->getRequest()->getParam('website'))->getStoreIds();
        } elseif ($this->getRequest()->getParam('group')) {
            $storeIds = $storeIds = $this->_storeManager->getGroup(
                $this->getRequest()->getParam('group')
            )->getStoreIds();
        }

        // By default storeIds array contains only allowed stores
        $allowedStoreIds = array_keys($this->_storeManager->getStores());
        // And then array_intersect with post data for prevent unauthorized stores reports
        $storeIds = array_intersect($allowedStoreIds, $storeIds);
        // If selected all websites or unauthorized stores use only allowed
        if (empty($storeIds)) {
            $storeIds = $allowedStoreIds;
        }
        // reset array keys
        $storeIds = array_values($storeIds);

        return $storeIds;
    }

    /**
     * Set filter values
     *
     * @param array $data
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _setFilterValues($data)
    {
        foreach ($data as $name => $value) {
            $this->setFilter($name, $data[$name]);
        }
        return $this;
    }

    /**
     * Set visibility of store switcher
     *
     * @param bool $visible
     * @codeCoverageIgnore
     * @return void
     */
    public function setStoreSwitcherVisibility($visible = true)
    {
        $this->_storeSwitcherVisibility = $visible;
    }

    /**
     * Return visibility of store switcher
     *
     * @codeCoverageIgnore
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getStoreSwitcherVisibility()
    {
        return $this->_storeSwitcherVisibility;
    }

    /**
     * Return store switcher html
     *
     * @codeCoverageIgnore
     * @return string
     */
    public function getStoreSwitcherHtml()
    {
        return $this->getChildHtml('store_switcher');
    }

    /**
     * Set visibility of date filter
     *
     * @param bool $visible
     * @return void
     * @codeCoverageIgnore
     */
    public function setDateFilterVisibility($visible = true)
    {
        $this->_dateFilterVisibility = $visible;
    }

    /**
     * Return visibility of date filter
     *
     * @codeCoverageIgnore
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getDateFilterVisibility()
    {
        return $this->_dateFilterVisibility;
    }

    /**
     * Return date filter html
     *
     * @codeCoverageIgnore
     * @return string
     */
    public function getDateFilterHtml()
    {
        return $this->getChildHtml('date_filter');
    }

    /**
     * Get periods
     *
     * @return mixed
     */
    public function getPeriods()
    {
        return $this->getCollection()->getPeriods();
    }
	
    /**
     * Get status
     *
     * @return mixed
     */
    public function getStatus()
    {
		$orderStatus = $this->orderStatusCollection->toOptionArray();
		
		$orderStatusArray[] = '';
		$orderStatusArray[0] = __('Please Select Order Status');
		
		foreach($orderStatus as $key=>$value){
			$orderStatusArray[$value['value']] = $value['label'];
		}
		
		return $orderStatusArray;
		
		/* return ['0' => __('Please Select Order Status'), 'processing' => __('Processing'), 'complete' => __('Complete'), 'holded' => __('On Hold'), 'closed' => __('IT WAS RETURNED')];
		
        return $collection->getStatus(); */
    }

    /**
     * Get date format according the locale
     *
     * @return string
     */
    public function getDateFormat()
    {
        return $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
    }

    /**
     * Return refresh button html
     *
     * @codeCoverageIgnore
     * @return string
     */
    public function getRefreshButtonHtml()
    {
        return $this->getChildHtml('refresh_button');
    }

    /**
     * Set filter
     *
     * @param string $name
     * @param string $value
     * @return void
     * @codeCoverageIgnore
     */
    public function setFilter($name, $value)
    {
        if ($name) {
            $this->_filters[$name] = $value;
        }
    }

    /**
     * Get filter by key
     *
     * @param string $name
     * @return string
     */
    public function getFilter($name)
    {
        if (isset($this->_filters[$name])) {
            return $this->_filters[$name];
        } else {
            return $this->getRequest()->getParam($name) ? $this->escapeHtml($this->getRequest()->getParam($name)) : '';
        }
    }

    /**
     * Set sub-report rows count
     *
     * @param int $size
     * @return void
     * @codeCoverageIgnore
     */
    public function setSubReportSize($size)
    {
        $this->_subReportSize = $size;
    }

    /**
     * Return sub-report rows count
     *
     * @codeCoverageIgnore
     * @return int
     */
    public function getSubReportSize()
    {
        return $this->_subReportSize;
    }

    /**
     * Retrieve errors
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * Prepare grid filter buttons
     *
     * @return void
     */
    protected function _prepareFilterButtons()
    {
        $this->addChild(
            'refresh_button',
            \Magento\Backend\Block\Widget\Button::class,
            ['label' => __('Refresh'), 'onclick' => "{$this->getJsObjectName()}.doFilter();", 'class' => 'task']
        );
    }
}
