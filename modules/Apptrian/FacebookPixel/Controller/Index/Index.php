<?php
/**
 * @category  Apptrian
 * @package   Apptrian_FacebookPixel
 * @author    Apptrian
 * @copyright Copyright (c) Apptrian (http://www.apptrian.com)
 * @license   http://www.apptrian.com/license Proprietary Software License EULA
 */
 
namespace Apptrian\FacebookPixel\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    public $resultJsonFactory;
    
    /**
     * @var \Apptrian\FacebookPixel\Helper\Data
     */
    public $dataHelper;
    
    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Apptrian\FacebookPixel\Helper\Data $dataHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Apptrian\FacebookPixel\Helper\Data $dataHelper
    ) {
        $this->dataHelper = $dataHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        return parent::__construct($context);
    }
    
    /**
     * Controller Index Action
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $firingMode = $this->dataHelper->getFiringMode();
        
        if ($this->getRequest()->isAjax() && $firingMode != 2) {
            $data = $this->getRequest()->getParams();

            $apiData = $this->dataHelper->getEventDataForApi($data);
            
            $this->dataHelper->fireServerEvent($apiData);
            
            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            $resultJson = $this->resultJsonFactory->create();
            
            $response = ['success' => 'true'];
            
            return $resultJson->setData($response);
        }
    }
}
