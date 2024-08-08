<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-report-api
 * @version   1.0.57
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\ReportApi\Config\Aggregator;

use Mirasvit\ReportApi\Api\Config\AggregatorInterface;

class DayOfWeek implements AggregatorInterface
{
    /**
     * @return string
     */
    public function getType()
    {
        return self::TYPE_DAY_OF_WEEK;
    }

    /**
     * @return array|string
     */
    public function getExpression()
    {
        return 'WEEKDAY(%1)';
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return 'Day of week';
    }
}
