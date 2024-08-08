<?php


namespace Saee\ShipmentMethod\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;
use Saee\ShipmentMethod\Helper\SaeeUtils;

/***
 * Class Index
 * @package Saee\ShipmentMethod\Controller\Index
 */
class Index extends Action {


    CONST TRACKING_ORDER = '/tracking?trackingnum=';
    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var SaeeUtils
     */
    protected $saeeData;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /***
     * Index constructor.
     * @param LoggerInterface $logger
     * @param Context $context
     * @param SaeeUtils $saeeData
     * @param PageFactory $pageFactory
     */
    public function __construct(
        LoggerInterface $logger,
        Context $context,
        SaeeUtils $saeeData,
        PageFactory $pageFactory
    )
    {
        $this->saeeData = $saeeData;
        $this->pageFactory = $pageFactory;
        $this->logger = $logger;
        parent::__construct($context);

    }


    public function execute()
    {
        $post = (array) $this->getRequest()->getPost();

        if (!empty($post)) {

            //trim leading zeros from order ID
            $trackingId = ltrim($post['trackingnum'], "0");

            $waybill = $this->saeeData->getSaeeWaybill($trackingId);

            $this->logger->info('tracking ID:        '.$trackingId);
            $this->logger->info('tracking Waybill:   '.$waybill);

            $trackingUrl = $this->saeeData->getSaeeUrl(). self::TRACKING_ORDER .$waybill;

            $jsonResponse = $this->saeeData->saeeCurlExec($trackingUrl, "GET");
            $this->logger->info($jsonResponse);
            $response=json_decode($jsonResponse, true);

            $this->messageManager->addSuccessMessage('Tracking done !');

                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
                $block = $resultRedirect->getLayout()->getBlock('tracking');
            if($response['success']) {

                $details = $response['details'];
                $block->setData('details',$details);
                $block->setData('company_id', $response['company_id']);
                $block->setData('order_id', $trackingId);
                $block->setData('Failed_Delivery_Attempts', $response['Failed_Delivery_Attempts']);
                $block->setData('reason_code', $response['reason_code']);
                $block->setData('waybill', $waybill);

            }else{

                $block->setData('error', $response['error']);
            }
            return $resultRedirect;
        }
            $page = $this->pageFactory->create();
            return $page;
    }
}
