<?php

declare(strict_types=1);

namespace Amasty\Reports\Model\Source\Date;

class Period implements \Magento\Framework\Data\OptionSourceInterface
{
    const DAY = 'day';

    const WEEK = 'week';

    const MONTH = 'month';

    const YEAR = 'year';

    /**
     * @return array|array[]
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::DAY,
                'label' => __('Day')
            ],
            [
                'value' => self::WEEK,
                'label' => __('Week')
            ],
            [
                'value' => self::MONTH,
                'label' => __('Month')
            ],
            [
                'value' => self::YEAR,
                'label' => __('Year')
            ]
        ];
    }
}
