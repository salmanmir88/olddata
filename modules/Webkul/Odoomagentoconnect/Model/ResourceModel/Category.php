<?php
/**
 * Webkul Odoomagentoconnect Category ResourceModel
 *
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Odoomagentoconnect\Model\ResourceModel;

/**
 * Webkul Odoomagentoconnect Category ResourceModel Class
 */
class Category extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var eventManager
     */
    protected $_eventManager;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param string|null                                       $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Event\Manager $eventManager,
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Catalog\Model\Category $categoryModel,
        \Webkul\Odoomagentoconnect\Helper\Connection $connection,
        $resourcePrefix = null
    ) {
        $this->_eventManager = $eventManager;
        $this->_connection = $connection;
        $this->_categoryModel = $categoryModel;
        parent::__construct($context, $resourcePrefix);
    }

    public function getMageCategoryArray()
    {

        $Product = [];
        $Product[''] ='--Select Magento Category--';
        $mageIds = $this->_categoryModel->getCollection()
            ->addAttributeToFilter('level', ['neq' => 0])
            ->getAllIds();

        foreach ($mageIds as $mageId) {
            $categoryObj = $this->_categoryModel->load($mageId);
            
            $categoryId = $categoryObj->getId();
            $categoryName = $categoryObj->getName();
            $Product[$categoryId] = $categoryName;
        }
        
        return $Product;
    }

    public function getOdooCategoryArray()
    {
        $categoryArray = [];
        $resp = $this->_connection->callOdooMethod('product.category', 'search_read', [[],['id', 'display_name']]);
        if ($resp && $resp[0]) {
            $categoryArray[''] ='--Select Odoo Category--';
            $odooCategories = $resp[1];
            foreach ($odooCategories as $odooCategory) {
                $categoryArray[$odooCategory['id']] = $odooCategory['display_name'];
            }
        } else {
            $categoryArray['error'] = $resp[1];
        }
        return $categoryArray;
    }

    public function updateMapping($model, $status = 'yes')
    {
        $model->setNeedSync($status);
        $model->save();
        return true;
    }

    public function getCategoryArray($categoryId)
    {
        $categoryObj = $this->_categoryModel->load($categoryId);
        $name = urlencode($categoryObj->getName());
        $categArray = ['name'=>$name, 'ecomm_id'=>$categoryId];
        $parentId = $categoryObj->getParentId();
        if ($parentId) {
            $categoryMapModel = $this->_connection->getModel(\Webkul\Odoomagentoconnect\Model\Category::class)
                ->getCollection()
                ->addFieldToFilter('magento_id', ['eq'=>$parentId]);
            foreach ($categoryMapModel as $map) {
                $odooParentId = (int)$map->getOdooId();
                $categArray['parent_id'] = $odooParentId;
                break;
            }
        }
        return $categArray;
    }

    public function createSpecificCategory($mageId, $method, $odooId = 0, $categMapModel = 0)
    {
        $response = [];
        $helper = $this->_connection;
        if ($mageId) {
            $context = $helper->getOdooContext();
            $categoryArray = $this->getCategoryArray($mageId);
            $categoryArray['method'] = $method;
            if ($odooId) {
                $categoryArray['category_id'] = $odooId;
            }
            $context['created_by'] = 'Magento';
            $resp = $helper->callOdooMethod('connector.category.mapping', 'create_category', [$categoryArray], $context);

            if ($resp && $resp[0]) {
                if (!$odooId) {
                    $odooId = $resp[1];
                }
                if ($odooId > 0) {
                    $mappingData = [
                        'odoo_id'=>$odooId,
                        'magento_id'=>$mageId,
                        'created_by'=>$helper::$mageUser
                    ];
                    if ($categMapModel) {
                        $this->updateMapping($categMapModel, 'no');
                    } else {
                        $helper->createMapping(\Webkul\Odoomagentoconnect\Model\Category::class, $mappingData);
                    }

                    $this->_eventManager
                        ->dispatch('catalog_category_sync_after', ['mage_id' => $mageId, 'odoo_id' => $odooId,]);
                    $response['odoo_id'] = $odooId;
                }
            } else {
                $respMessage = $resp[1];
                if ($method == 'create') {
                    $error = "Category Export Error, Category Id ".$mageId." >> ".$respMessage;
                } else {
                    $error = "Category Update Error, Category Id ".$mageId." >> ".$respMessage;
                }
                $helper->addError($error);
                $response['odoo_id'] = 0;
                $response['error'] = $respMessage;
            }
        }
        return $response;
    }
    
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('odoomagentoconnect_category', 'entity_id');
    }
}
