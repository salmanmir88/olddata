<?php
/**
 * Copyright © CustomWork All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dakha\CustomWork\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\RequestInterface;
use Mirasvit\Helpdesk\Model\ResourceModel\Ticket\Collection as TicketCollection;

class Checkorder implements HttpPostActionInterface
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var Json
     */
    protected $serializer;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var Http
     */
    protected $http;
    /**
     * @var RequestInterface
     */
    protected $request;
    /**
     * @var TicketCollection
     */
    protected $ticketCollection;

    /**
     * Constructor
     *
     * @param PageFactory $resultPageFactory
     * @param Json $json
     * @param LoggerInterface $logger
     * @param Http $http
     * @param RequestInterface $request
     * @param TicketCollection $ticketCollection
     */
    public function __construct(
        PageFactory $resultPageFactory,
        Json $json,
        LoggerInterface $logger,
        Http $http,
        RequestInterface $request,
        TicketCollection $ticketCollection
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->serializer = $json;
        $this->logger = $logger;
        $this->http = $http;
        $this->request = $request;
        $this->ticketCollection = $ticketCollection;
    }

    /**
     * Execute view action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        try {
            $orderNumber = $this->request->getParam('order_number');
            if(empty($orderNumber)){
             $data['status'] = false;   
             return $this->jsonResponse(['data'=>$data]);   
            }
            $trackCollection = $this->ticketCollection;
            $trackCollection->addFieldToFilter('order_number',['eq'=>$orderNumber]);
            $data['status'] = false;
            if(count($trackCollection)>0){
               $data['status'] = true;
            }       
            return $this->jsonResponse(['data'=>$data]);
        } catch (LocalizedException $e) {
            $data['status'] = false;
            return $this->jsonResponse(['data'=>$data]);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $data['status'] = false;
            return $this->jsonResponse(['data'=>$data]);
        }
    }

    /**
     * Create json response
     *
     * @return ResultInterface
     */
    public function jsonResponse($response = '')
    {
        $this->http->getHeaders()->clearHeaders();
        $this->http->setHeader('Content-Type', 'application/json');
        return $this->http->setBody(
            $this->serializer->serialize($response)
        );
    }
}
