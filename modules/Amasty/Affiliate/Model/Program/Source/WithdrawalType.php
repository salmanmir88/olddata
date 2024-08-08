<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Affiliate for Magento 2
*/
declare(strict_types=1);

namespace Amasty\Affiliate\Model\Program\Source;

use Amasty\Affiliate\Model\ProgramFactory;

class WithdrawalType extends AbstractOptions
{
    /**
     * @var ProgramFactory
     */
    private $programFactory;

    public function __construct(
        ProgramFactory $programFactory
    ) {
        $this->programFactory = $programFactory;
    }

    public function toArray(): array
    {
        return $this->programFactory->create()->getAvailableWithdrawalTypes();
    }
}
