<?php
namespace NitroPack\NitroPack\Api;

interface CacheWarmupInterface {
	
	public function getConfig();
	public function setConfig($newConfig);
	public function getEstimate();

}