<?php

namespace Evince\AWBnumber\Controller\Adminhtml\Create;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as OrderGridCollection;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Filesystem\DirectoryList;
use SB\PDFMerger\PDFMerger;
use setasign\Fpdi\Fpdi;


class Awbmerger extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction {

    protected $dateTime;
    protected $fileFactory;
    protected $resourceConnection;
    protected $request;
    protected $orderFactory;

   
    public function __construct(
        \Magento\Backend\App\Action\Context $context, 
        \Magento\Ui\Component\MassAction\Filter $filter, 
        \Magento\Backend\Model\Auth\Session $authSession, 
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory, 
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime, 
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Sales\Model\OrderFactory $orderFactory
    ) {
        parent::__construct($context, $filter);
        $this->authSession = $authSession;
        $this->collectionFactory = $collectionFactory;
        $this->dateTime = $dateTime;
        $this->fileFactory = $fileFactory;
        $this->directory   = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
        $this->resourceConnection = $resourceConnection;
        $this->request = $request;
        $this->orderFactory = $orderFactory;
    }

    protected function massAction(AbstractCollection $collection) {
        $resultRedirect = $this->resultRedirectFactory->create();
        $pdf = new \SB\PDFMerger\PDFMerger;
        $pdfArr = [];
        if(!isset($this->request->getPost()['search']) && count($this->request->getPost()['search'])>0)
        {
           return;
        } 
        
        $incrementIds = explode(" ",$this->request->getPost()['search']);
        
        $incrementIds = array_unique($incrementIds);
        
        \Magento\Framework\App\ObjectManager::getInstance()
        ->get(\Psr\Log\LoggerInterface::class)->info(print_r($incrementIds,true));

        foreach ($incrementIds as $key=>$incrementId) {
            $order = $this->orderFactory->create()->loadByIncrementId(trim($incrementId));
            
            \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Psr\Log\LoggerInterface::class)->info('idsssssssssss '.$order->getId().' incre '.$incrementId);

            if ($order->getId()){  
            $sql            = "Select awb_link FROM  sales_order_grid WHERE entity_id ='".$order->getId()."'";
            $singleRow      = $this->resourceConnection->getConnection()->fetchRow($sql);
            $exportFolder   = $this->directory->getAbsolutePath('importexport/');

            try {

                if(isset($singleRow['awb_link'])){ 
                 $headers = get_headers($singleRow['awb_link'], 1);
                 if ($headers[0] == 'HTTP/1.1 200 OK') {
                     $pdfFolder = $exportFolder.basename($singleRow['awb_link']);
                     
                     \Magento\Framework\App\ObjectManager::getInstance()
                     ->get(\Psr\Log\LoggerInterface::class)->info('awb link '.$singleRow['awb_link']);
                     \Magento\Framework\App\ObjectManager::getInstance()
                     ->get(\Psr\Log\LoggerInterface::class)->info(print_r($headers,true));

                     try {
                         $content = file_get_contents($singleRow['awb_link']);
                         file_put_contents($pdfFolder, $content);

                     } catch (Exception $e) {
                         continue;
                     }

                     $pdfArr[] = $pdfFolder;
                     $pdf->addPDF($pdfFolder, 'all');
                  }   
                }
            } catch (Exception $e) {
                \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Psr\Log\LoggerInterface::class)->info('error report '.$e);
            }

           }

        }

        if(count($pdfArr)>0){
         $fileName = $exportFolder.date("Y-m-d h:i:sa").'merged.pdf';   
         $pdf->merge('file', $exportFolder.date("Y-m-d h:i:sa").'merged.pdf', 'P');
         $this->download($fileName);
        }
        return $resultRedirect->setPath('sales/order/index');
    }

    public function download($file)
    {
        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='.basename($file));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            ob_clean();
            flush();
            readfile($file);
            exit;
        }
    }

}