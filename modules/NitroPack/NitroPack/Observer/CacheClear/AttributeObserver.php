<?php
namespace NitroPack\NitroPack\Observer\CacheClear;

use Magento\Framework\Event\Observer;

use NitroPack\NitroPack\Model\EntityAttributeRelationFactory;
use NitroPack\NitroPack\Observer\CacheClearObserver;

class AttributeObserver extends CacheClearObserver {

	protected static $eventMap = array(
		'catalog_entity_attribute_save_commit_after'   => 'saved',
		'catalog_entity_attribute_delete_commit_after' => 'deleted'
	);

	protected $attribute; // Magento\Catalog\Model\ResourceModel\Eav\Attribute

	public function prepareData(Observer $observer) {
		if (!$this->shouldAutoClear('attributes')) {
			return false;
		}

		$data = $observer->getEvent()->getData();

		if (!isset($data['attribute'])) {
			return false;
		}

		$this->attribute = $data['attribute'];

		return true;
	}

	public function saved(Observer $observer) {
		$attributeName = $this->attribute->getName();
		if (!$attributeName || $attributeName == '') {
			$attributeName = '#' . $this->attribute->getId();
		}

		$attributeSetIds = $this->findAttributeSetsIncludingAttribute($this->attribute->getId());

		$products = array();
		foreach ($attributeSetsIds as $attributeSetId) {
			$productsWithSet = $this->findProductsWithAttributeSet($attributeSetId);
			$products = array_merge($products, $productsWithSet);
		}

		foreach ($products as $product) {
			$tag = $this->tagger->getProductTag($product);
			$this->invalidateTag($tag, 'attribute', $attributeName);
		}
	}

	public function deleted(Observer $observer) {
		$attributeName = $this->attribute->getName();
		if (!$attributeName || $attributeName == '') {
			$attributeName = '#' . $this->attribute->getId();
		}

		$attributeSetIds = $this->findAttributeSetsIncludingAttribute($this->attribute->getId());

		$products = array();
		foreach ($attributeSetsIds as $attributeSetId) {
			$productsWithSet = $this->findProductsWithAttributeSet($attributeSetId);
			$products = array_merge($products, $productsWithSet);
		}

		foreach ($products as $product) {
			$tag = $this->tagger->getProductTag($product);
			$this->purgeTagPageCache($tag, 'attribute', $attributeName);
		}
	}
	
	protected function findProductsWithAttributeSet($attributeSetId) {
		$productsRepo = $this->objectManager->create(ProductRepositoryInterface::class);
		$searchCriteriaBuilder = $this->objectManager->create(SearchCriteriaBuilder::class);
		$searchCriteria = $searchCriteriaBuilder->addFilter('attribute_set_id', $attributeSetId, 'eq')->create();
		$searchResults = $productsRepo->getList($searchCriteria);
		return $searchResults->getItems();
	}

	protected function findAttributeSetsIncludingAttribute($attributeId) {
		$entityAttributeFactory = $this->objectManager->create(EntityAttributeRelationFactory::class);
		$model = $entityAttributeFactory->create();
		$collection = $model->getCollection();
		$collection->addFieldToFilter('attribute_id',  array('eq' => $attributeId));
		
		$attributeSetIds = array();
		foreach ($collection as $attr) {
			$attributeSetIds[] = $attr->getData('attribute_set_id');
		}

		return $attributeSetsIds;
	}
}
