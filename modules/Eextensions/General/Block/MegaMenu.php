<?php /** get Category tree for mega menu **/

namespace Eextensions\General\Block;

class MegaMenu extends \Magento\Framework\View\Element\Template
{
    protected $_categoryCollectionFactory;
    protected $_categoryHelper;
    protected $_storeManager;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Helper\Category $categoryHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    )
    {
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_categoryHelper = $categoryHelper;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    /**
     * Get category collection
     *
     * @param bool $isActive
     * @param bool|int $level
     * @param bool|string $sortBy
     * @param bool|int $pageSize
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection or array
     */
    public function getCategoryCollection($isActive = true, $level = false, $sortBy = false, $pageSize = false)
    {
        $collection = $this->_categoryCollectionFactory->create();
        $collection->addAttributeToSelect('*');

        // select only active categories
        if ($isActive) {
            $collection->addIsActiveFilter();
        }

        // select categories of certain level
        if ($level) {
            $collection->addLevelFilter($level);
        }

        // sort categories by some value
        if ($sortBy) {
            $collection->addOrderField($sortBy);
        }

        // select certain number of categories
        if ($pageSize) {
            $collection->setPageSize($pageSize);
        }

		$collection->addFieldToFilter('include_in_menu', 1);
        $collection->addAttributeToSort('position', 'ASC');

        $catData = array();
        foreach ($collection as $key=>$categoryData) {
           
			$catData[$key]['entity_id'] 			= $categoryData->getEntityId();
			$catData[$key]['attribute_set_id'] 		= $categoryData->getAttributeSetId();
			$catData[$key]['parent_id'] 			= $categoryData->getParentId();
			$catData[$key]['created_at'] 			= $categoryData->getCreatedAt();
			$catData[$key]['updated_at'] 			= $categoryData->getUpdatedAt();
			$catData[$key]['path'] 					= $categoryData->getPath();
			$catData[$key]['position'] 				= $categoryData->getPosition();
			$catData[$key]['level'] 				= $categoryData->getLevel();
			$catData[$key]['children_count'] 		= $categoryData->getChildrenCount();
			$catData[$key]['is_active'] 			= $categoryData->getIsActive();
			$catData[$key]['include_in_menu']		= $categoryData->getIncludeInMenu();
			$catData[$key]['name'] 					= $categoryData->getName();
			$catData[$key]['url'] 					= $categoryData->getUrl();
			$catData[$key]['url_key'] 				= $categoryData->getUrlKey();
			$catData[$key]['display_mode'] 			= $categoryData->getDisplayMode();
            $catData[$key]['cat_image'] 			= $categoryData->getImageUrl();
            $catData[$key]['cat_icon'] 		    	= $categoryData["thumbnail"];
        }
        return $catData;
    }

    /**
     * Retrieve current store categories
     *
     * @param bool|string $sorted
     * @param bool $asCollection
     * @param bool $toLoad
     * @return \Magento\Framework\Data\Tree\Node\Collection or
     * \Magento\Catalog\Model\ResourceModel\Category\Collection or array
     */
    public function getStoreCategories($sorted = false, $asCollection = false, $toLoad = true)
    {
        return $this->_categoryHelper->getStoreCategories($sorted = false, $asCollection = false, $toLoad = true);
    }

    /**
     * Retrieve category Tree
    */

    public function buildTree(array $elements, $parentId = 2) {
		$branch = array();

		foreach ($elements as $element) {
			//~ printMethod($element);exit;
			if ($element['parent_id'] == $parentId) {
			   $children = $this->buildTree($elements, $element['entity_id']);
				if ($children) {
					$element['children'] = $children;
				}
				$branch[] = $element;
			}
		}

		return $branch;
	}

}
?>
