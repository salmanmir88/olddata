<?php

namespace Dakha\CustomWork\Ui\Component\Listing\Column\Helpdesk;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\User\Model\UserFactory;

class SubAssign extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var UserFactory
     * 
     */
    protected $userFactory;

    /**
     * SubAssign constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UserFactory $userFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UserFactory $userFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->userFactory = $userFactory;
    }

    /**
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$this->getData('name')] = $this->userFactory->create()->load($item['sub_assign'])->getUsername();
            }
        }

        return $dataSource;
    }
}
