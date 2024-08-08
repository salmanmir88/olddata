<?php 
namespace Evincemage\AlbumSorter\Plugin\Catalog\Model;

class Config
{
	public function afterGetAttributeUsedForSortByArray(
		\Magento\Catalog\Model\Config $catalogConfig,
        $options,
        $requestInfo = null
	)
	{
		$options['new_first'] = __('New First');
		return $options;
	}
}