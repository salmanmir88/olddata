<?php

declare(strict_types=1);

namespace Amasty\Reports\Model;

class ConfigProvider extends \Amasty\Base\Model\ConfigProviderAbstract
{
    const MODULE_SECTION = 'amasty_reports/';
    const XPATH_NOTIFICATION_SENDER = 'general/sender_email_identity';
    const XPATH_NOTIFICATION_TEMPLATE = 'general/email_template';
    const XPATH_ORDER_STATUSES = 'general/reports_statuses';
    const XPATH_REPORT_BRAND = 'general/report_brand';

    protected $pathPrefix = self::MODULE_SECTION;

    public function getNotificationSender(): ?string
    {
        return $this->getValue(self::XPATH_NOTIFICATION_SENDER);
    }

    public function getNotificationTemplate(): ?string
    {
        return $this->getValue(self::XPATH_NOTIFICATION_TEMPLATE);
    }

    public function getOrderStatuses(): array
    {
        $statuses = (string) $this->getValue(self::XPATH_ORDER_STATUSES);

        return !empty($statuses) ? explode(',', $statuses) : [];
    }

    public function getReportBrand(): ?string
    {
        return $this->getValue(self::XPATH_REPORT_BRAND);
    }
}
