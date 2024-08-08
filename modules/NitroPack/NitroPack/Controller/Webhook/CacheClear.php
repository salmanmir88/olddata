<?php
namespace NitroPack\NitroPack\Controller\Webhook;

use NitroPack\SDK\PurgeType;

class CacheClear extends WebhookController {

	const REASON_MANUAL_PURGE_URL = "Manual purge of the link %s from the NitroPack.io Dashboard.";
	const REASON_MANUAL_PAGE_CACHE_ONLY_ALL = "Manual page cache clearing of all store pages from the NitroPack.io Dashboard.";

	public function execute() {
		if ($url = $this->getRequest()->getParam('url', false)) {
			$this->nitro->purgeCache($url, null, PurgeType::PAGECACHE_ONLY, sprintf(self::REASON_MANUAL_PURGE_URL, $url));
		} else {
			$this->nitro->purgeLocalCache(true);
		}
		return $this->textResponse('ok');
	}

}