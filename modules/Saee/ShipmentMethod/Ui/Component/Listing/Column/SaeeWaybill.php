<?php
namespace Saee\ShipmentMethod\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;
use Saee\ShipmentMethod\Helper\SaeeUtils;
use Saee\ShipmentMethod\Model\DbDataFactory;
use Psr\Log\LoggerInterface;

/**
 * Class SaeeWaybill
 * @package Saee\ShipmentMethod\Ui\Component\Listing\Column
 */
class SaeeWaybill extends Column
{

    const ROW_EDIT_URL = 'grid/grid/addrow';

    /**
     * @var OrderRepositoryInterface
     */
    protected $_orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $_searchCriteria;

    /**
     * @var DbDataFactory
     */
    protected $_customfactory;

    /**
     * @var SaeeUtils
     */
    protected $saeeUtils;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var string
     */
    private $_editUrl;


    /**
     * SaeeWaybill constructor.
     * @param LoggerInterface $logger
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param UrlInterface $urlBuilder
     * @param SearchCriteriaBuilder $criteria
     * @param SaeeUtils $saeeUtils
     * @param DbDataFactory $customFactory
     * @param string $editUrl
     * @param array $components
     * @param array $data
     */
    public function __construct(
        LoggerInterface $logger,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderRepositoryInterface $orderRepository,
        UrlInterface $urlBuilder,
        SearchCriteriaBuilder $criteria,
        SaeeUtils $saeeUtils,
        DbDataFactory $customFactory,
        $editUrl = self::ROW_EDIT_URL,
        array $components = [],
        array $data = []
    )
    {

        $this->logger = $logger;
        $this->_orderRepository = $orderRepository;
        $this->_searchCriteria  = $criteria;
        $this->_customfactory = $customFactory;
        $this->urlBuilder = $urlBuilder;
        $this->saeeUtils = $saeeUtils;
        $this->_editUrl = $editUrl;
        parent::__construct(
            $context,
            $uiComponentFactory,
            $components,
            $data
        );
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) foreach ($dataSource['data']['items'] as & $item) {
            $order  = $this->_orderRepository->get($item["entity_id"]);
            $order_id = $order->getEntityId();

            $waybill = $this->saeeUtils->getSaeeWaybill($order_id);
            $stickerEndpoint = '/deliveryrequest/printsticker/pdf/';
            $stickerUrl = $this->saeeUtils->getSaeeUrl() . $stickerEndpoint .$waybill;
            $item[$this->getData('name')] = [
                $waybill=> [
                    'href' => $stickerUrl,
                    'target' => '_blank',
                    'label' => __($waybill)
                ]
            ];

        }
        return $dataSource;
    }
}
