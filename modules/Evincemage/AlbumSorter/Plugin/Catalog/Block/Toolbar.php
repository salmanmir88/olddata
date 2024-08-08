<?php
namespace Evincemage\AlbumSorter\Plugin\Catalog\Block;

class Toolbar
{
	public function aroundSetCollection(
		\Magento\Catalog\Block\Product\ProductList\Toolbar $subject,
		\Closure $proceed,
		$collection
	){
		$currentOrder = $subject->getCurrentOrder();
		$result = $proceed($collection);
		if($currentOrder)
		{
			if($currentOrder=='new_first'){
				$subject->getCollection()->setOrder('created_at','desc');
			}
		}

		return $result;
	}	
}