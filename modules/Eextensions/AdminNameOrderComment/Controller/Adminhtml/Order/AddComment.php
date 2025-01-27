<?php
namespace Eextensions\AdminNameOrderComment\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Sales\Model\Order\Email\Sender\OrderCommentSender;

use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\InputException;
use Psr\Log\LoggerInterface;

class AddComment extends \Magento\Sales\Controller\Adminhtml\Order
{
	/**
	 * Authorization level of a basic admin session
	 *
	 * @see _isAllowed()
	 */
	const ADMIN_RESOURCE = 'Magento_Sales::comment';

	/**
	 * Core registry
	 *
	 * @var \Magento\Framework\Registry
	 */
	protected $_coreRegistry = null;

	/**
	 * @var \Magento\Framework\App\Response\Http\FileFactory
	 */
	protected $_fileFactory;

	/**
	 * @var \Magento\Framework\Translate\InlineInterface
	 */
	protected $_translateInline;

	/**
	 * @var \Magento\Framework\View\Result\PageFactory
	 */
	protected $resultPageFactory;

	/**
	 * @var \Magento\Framework\Controller\Result\JsonFactory
	 */
	protected $resultJsonFactory;

	/**
	 * @var \Magento\Framework\View\Result\LayoutFactory
	 */
	protected $resultLayoutFactory;

	/**
	 * @var \Magento\Framework\Controller\Result\RawFactory
	 */
	protected $resultRawFactory;

	/**
	 * @var OrderManagementInterface
	 */
	protected $orderManagement;

	/**
	 * @var OrderRepositoryInterface
	 */
	protected $orderRepository;

	/**
	 * @var LoggerInterface
	 */
	protected $logger;

	protected $authSession;

	public function __construct(
		Action\Context $context,
		\Magento\Framework\Registry $coreRegistry,
		\Magento\Framework\App\Response\Http\FileFactory $fileFactory,
		\Magento\Framework\Translate\InlineInterface $translateInline,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
		\Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
		\Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
		OrderManagementInterface $orderManagement,
		OrderRepositoryInterface $orderRepository,
		LoggerInterface $logger,
		\Magento\Backend\Model\Auth\Session $authSession
	) {
		$this->authSession = $authSession;
		parent::__construct($context, $coreRegistry,$fileFactory,$translateInline,$resultPageFactory,$resultJsonFactory,$resultLayoutFactory,$resultRawFactory,$orderManagement,$orderRepository,$logger);
	}

	/**
	 * Add order comment action
	 *
	 * @return \Magento\Framework\Controller\ResultInterface
	 */
	public function execute()
	{
		$order = $this->_initOrder();
		if ($order) {
			try {
				$data = $this->getRequest()->getPost('history');
				if (empty($data['comment']) && $data['status'] == $order->getDataByKey('status')) {
					throw new \Magento\Framework\Exception\LocalizedException(__('Please enter a comment.'));
				}
				
				// pr($data);die;

				$notify = isset($data['is_customer_notified']) ? $data['is_customer_notified'] : false;
				$visible = isset($data['is_visible_on_front']) ? $data['is_visible_on_front'] : false;

				$username = $this->authSession->getUser()->getUsername();
				$append = " (by ".$username.")";

				$history = $order->addStatusHistoryComment($data['comment'].$append, $data['status']);
				$history->setIsVisibleOnFront($visible);
				$history->setIsCustomerNotified($notify);
				$history->save();

				$comment = trim(strip_tags($data['comment']));

				$order->save();
				/** @var OrderCommentSender $orderCommentSender */
				$orderCommentSender = $this->_objectManager
					->create(\Magento\Sales\Model\Order\Email\Sender\OrderCommentSender::class);

				$orderCommentSender->send($order, $notify, $comment);

				return $this->resultPageFactory->create();
			} catch (\Magento\Framework\Exception\LocalizedException $e) {
				$response = ['error' => true, 'message' => $e->getMessage()];
			} catch (\Exception $e) {
				$response = ['error' => true, 'message' => __('We cannot add order history.')];
			}
			if (is_array($response)) {
				$resultJson = $this->resultJsonFactory->create();
				$resultJson->setData($response);
				return $resultJson;
			}
		}
		return $this->resultRedirectFactory->create()->setPath('sales/*/');
	}
}