<?php
namespace NitroPack\NitroPack\Observer\CacheClear;

use Magento\Framework\Event\Observer;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

use NitroPack\NitroPack\Observer\CacheClearObserver;

class AttributeSetObserver extends CacheClearObserver {

	protected static $eventMap = array(
		'eav_entity_attribute_set_save_commit_after'   => 'saved',
		'eav_entity_attribute_set_delete_commit_after' => 'deleted'
	);

	protected $set; // Magento\Eav\Model\Entity\Attribute\Set

	public function prepareData(Observer $observer) {
		if (!$this->shouldAutoClear('attributeSets')) {
			return false;
		}

		$data = $observer->getEvent()->getData();

		if (!isset($data['object']) || !is_a($data['object'], Set::class)) {
			return false;
		}

		$this->set = $data['object'];

		return true;
	}

	public function saved(Observer $observer) {
		$products = $this->findProductsWithAttributeSet();
		$attributeSetName = $this->set->getAttributeSetName();
		if (!$attributeSetName || $attributeSetName == '') {
			$attributeSetName = '#' . $this->set->getAttributeSetId();
		}
		foreach ($products as $product) {
			$tag = $this->tagger->getProductTag($this->product);
			
			$this->invalidateTag($tag, 'attribute set', $attributeSetName);
		}
	}

	public function deleted(Observer $observer) {
		$products = $this->findProductsWithAttributeSet();
		$attributeSetName = $this->set->getAttributeSetName();
		if (!$attributeSetName || $attributeSetName == '') {
			$attributeSetName = '#' . $this->set->getAttributeSetId();
		}
		foreach ($products as $product) {
			$tag = $this->tagger->getProductTag($product);
			
			$this->invalidateTag($tag, 'attribute set', $attributeSetName);
		}
	}

	protected function findProductsWithAttributeSet() {
		$productsRepo = $this->objectManager->create(ProductRepositoryInterface::class);
		$searchCriteriaBuilder = $this->objectManager->create(SearchCriteriaBuilder::class);
		$searchCriteria = $searchCriteriaBuilder->addFilter('attribute_set_id', $this->set->getAttributeSetId(), 'eq')->create();
		$searchResults = $productsRepo->getList($searchCriteria);
		return $searchResults->getItems();
	}
	
}
