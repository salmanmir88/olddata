<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 */

namespace MagePal\EnhancedEcommerce\Block\Data;

class Home extends CatalogWidget
{
    public function addImpressionList()
    {
        $this->setImpressionList(
            $this->getListType(),
            $this->_eeHelper->getHomeWidgetClassName(),
            $this->_eeHelper->getHomeWidgetContainerClass()
        );
    }

    protected function _init()
    {
        $this->setListType($this->_eeHelper->getHomeWidgetListType());
        $this->setUseWidgetTitle($this->_eeHelper->getHomeWidgetUseWidgetTitle());
        return $this;
    }
}
