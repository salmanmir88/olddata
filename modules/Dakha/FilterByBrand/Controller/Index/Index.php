<?php
/**
 * Copyright © dsfs All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dakha\FilterByBrand\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

class Index implements HttpGetActionInterface
{
    protected $request;
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Constructor
     *
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        PageFactory $resultPageFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
    )
    {
        $this->request = $request;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Execute view action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->request->getParam('manufacturer');
        $url = 'https://kpopiashop.com/all-products.html?manufacturer='.$id;
        $resultRedirect->setUrl($url);
        return $resultRedirect;
        //return $this->resultPageFactory->create();
    }
}