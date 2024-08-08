<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 */

namespace MagePal\EnhancedEcommerce\Plugin\Controller\Cart;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Message\Notice;
use MagePal\EnhancedEcommerce\Model\Session as EnhancedEcommerceSession;
use MagePal\GoogleTagManager\Helper\Data as GtmHelper;

class Add
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var EnhancedEcommerceSession
     */
    protected $enhancedEcommerceSession;
    /**
     * @var ManagerInterface
     */
    private $messageManager;
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var GtmHelper
     */
    private $gtmHelper;

    /**
     * @param  RequestInterface  $request
     * @param  ResponseInterface  $response
     * @param  ManagerInterface  $messageManager
     * @param  EnhancedEcommerceSession  $enhancedEcommerceSession
     * @param  ProductRepositoryInterface  $productRepository
     * @param  GtmHelper  $gtmHelper
     */
    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ManagerInterface $messageManager,
        EnhancedEcommerceSession $enhancedEcommerceSession,
        ProductRepositoryInterface $productRepository,
        GtmHelper $gtmHelper
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->enhancedEcommerceSession = $enhancedEcommerceSession;
        $this->messageManager = $messageManager;
        $this->productRepository = $productRepository;
        $this->gtmHelper = $gtmHelper;
    }

    /**
     * @param Action $subject
     * @return array
     */
    public function afterExecute(Action $subject, $result)
    {
        $response = $this->response->getBody();
        $responseArray = (array) json_decode($response, true);

        if ($product = $this->getProduct()) {
            $this->addTrackingData($product, $responseArray);
        }

        return $result;
    }

    /**
     * @param $product
     * @param $responseArray
     */
    protected function addTrackingData(ProductInterface $product, array $responseArray)
    {
        $item = [
            'name' => $product->getName(),
            'sku' => $product->getSku(),
            'price' => $this->gtmHelper->formatPrice($product->getFinalPrice()),
            'p_id' => $product->getId()
        ];

        if (array_key_exists('product', $responseArray)
            && array_key_exists('statusText', $responseArray['product'])
            && $responseArray['product']['statusText'] === __('Out of stock')
        ) {
            $this->enhancedEcommerceSession->setGenericEvent(
                $item,
                'addToCartItemOutOfStock'
            );
        }

        if ($this->messageManager->hasMessages()) {
            $messages = $this->messageManager->getMessages()->getItemsByType('notice');

            /** @var Notice $message */
            foreach ($messages as $message) {
                if ($message->getText() == __('You need to choose options for your item.')) {
                    $this->enhancedEcommerceSession->setGenericEvent(
                        $item,
                        'addToCartItemOptionRequired'
                    );
                    break;
                }
            }

        }
    }

    /**
     * @return bool|ProductInterface
     */
    public function getProduct()
    {
        $productId = (int)$this->request->getParam('product');

        if ($productId) {
            try {
                return $this->productRepository->getById($productId);
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }

        return false;
    }
}
