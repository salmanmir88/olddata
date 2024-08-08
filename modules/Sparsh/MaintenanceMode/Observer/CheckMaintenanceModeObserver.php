<?php
/**
 * Class CheckMaintenanceModeObserver Doc Comment
 *
 * PHP version 7
 *
 * @category Sparsh Technologies
 * @package  Sparsh_MaintenanceMode
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */

namespace Sparsh\MaintenanceMode\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\HTTP\PhpEnvironment\Request;

/**
 * Class CheckMaintenanceModeObserver Doc Comment
 *
 * @category Sparsh Technologies
 * @package  Sparsh_MaintenanceMode
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class CheckMaintenanceModeObserver implements ObserverInterface
{

    /**
     * MaintenanceRedirect Block
     *
     * @var \Sparsh\MaintenanceMode\Block\MaintenanceRedirect
     */
    protected $blockData;

    /**
     * Http Request
     *
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * RedirectInterface
     *
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime|\Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $date;
    /**
     * @var Request
     */
    private $httpRequest;

    /**
     * Locale Date/Timezone
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_timezone;

    /**
     * CheckMaintenanceModeObserver constructor.
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Sparsh\MaintenanceMode\Block\MaintenanceRedirect $block
     * @param \Magento\Framework\App\Response\Http $response
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param Request $httpRequest
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Sparsh\MaintenanceMode\Block\MaintenanceRedirect $block,
        \Magento\Framework\App\Response\Http $response,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Catalog\Block\Product\Context $context,
        Request $httpRequest
    ) {
        $this->blockData = $block;
        $this->request = $request;
        $this->redirect = $redirect;
        $this->response = $response;
        $this->date =  $date;
        $this->_timezone = $context->getLocaleDate();
        $this->httpRequest = $httpRequest;
    }

    /**
     * Maintenance Mode Observer Event
     *
     * @param \Magento\Framework\Event\Observer $observer observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $isMaintenanceModeEnabled = $this->blockData->getConfigData('enable');
        $exemptMaintenanceModeIps = $this->blockData->getConfigData('allow_ips');
        $maintenanceModeStartDateTime = $this->blockData->getConfigData('start_date');
        $maintenanceModeEndDateTime = $this->blockData->getConfigData('end_date');
        $storeDateTime = $this->_timezone->date()->format('Y-m-d H:i:s');

        $exemptMaintenanceModeIpsArray = explode(',', trim($exemptMaintenanceModeIps));

        if (!empty($this->httpRequest->getServerValue('HTTP_CLIENT_IP'))) {
            $userIPAddresses = $this->httpRequest->getServerValue('HTTP_CLIENT_IP');
        } elseif (!empty($this->httpRequest->getServerValue('HTTP_X_FORWARDED_FOR'))) {
            $userIPAddresses = $this->httpRequest->getServerValue('HTTP_X_FORWARDED_FOR');
        } else {
            $userIPAddresses = $this->httpRequest->getServerValue('REMOTE_ADDR');
        }

        $userIPAddressesArray = array_map('trim', explode(',', $userIPAddresses));

        if ($isMaintenanceModeEnabled && (!$maintenanceModeStartDateTime || strtotime($storeDateTime) >= strtotime($maintenanceModeStartDateTime)) && (!$maintenanceModeEndDateTime || strtotime($storeDateTime) <= strtotime($maintenanceModeEndDateTime)) && !array_intersect($userIPAddressesArray, $exemptMaintenanceModeIpsArray)) {
            $route = $this->request->getRouteName();
            if ($route!='maintenance') {
                $this->redirect->redirect(
                    $this->response,
                    'maintenance/'
                );
            }
        }
    }
}
