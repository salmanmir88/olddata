<?php
/**
 * Copyright © GtmTagEvents All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dakha\GtmTagEvents\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;
use Mageplaza\GoogleTagManager\Helper\Data;

class Trigger implements HttpGetActionInterface
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
     * @var Data
     */
    protected $_helper;

    /**
     * Constructor
     *
     * @param PageFactory $resultPageFactory
     * @param Json $json
     * @param LoggerInterface $logger
     * @param Http $http
     * @param Data $helper
     */
    public function __construct(
        PageFactory $resultPageFactory,
        Json $json,
        LoggerInterface $logger,
        Http $http,
        Data $helper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->serializer = $json;
        $this->logger = $logger;
        $this->http = $http;
        $this->_helper = $helper;
    }

    /**
     * Execute view action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        try {
            $data = [];
            $data['add']['gtm'] = $this->_helper->getSessionManager()->getGTMAddToCartData();
            $data['add']['ga'] = $this->_helper->getSessionManager()->getGAAddToCartData();
            return $this->jsonResponse($data);
        } catch (LocalizedException $e) {
            return $this->jsonResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return $this->jsonResponse($e->getMessage());
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

