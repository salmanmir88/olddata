<?php
/**
 * Webkul Odoomagentoconnect Attribute Save Controller
 *
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Odoomagentoconnect\Controller\Adminhtml\Attribute;

class Save extends \Webkul\Odoomagentoconnect\Controller\Adminhtml\Attribute
{
    /**
     * @return                                       void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {

        $userId = (int)$this->getRequest()->getParam('user_id');
        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            $this->_redirect('odoomagentoconnect/*/');
            return;
        }
        /**
 * Before updating admin user data, ensure that password of current admin user is entered and is correct
*/
        try {
            $this->messageManager->addSuccess(__('You saved the attribute.'));
            
            $attributeId = (int)$this->getRequest()->getParam('entity_id');
            $attributemodel = $this->_attributeMapping;
            if ($attributeId) {
                $attributemodel->load($attributeId);
                $attributemodel->setId($attributemodel);
                $data['id']=$attributeId;
            }
            if ($attributeId && $attributemodel->isObjectNew()) {
                $this->messageManager->addError(__('This attribute no longer exists.'));
                $this->_redirect('odoomagentoconnect/*/');
                return;
            }
            $collection = $this->_catalogModel->getResource()
                ->getAttribute($data['magento_id']);
            $code = $collection->getAttributeCode();
            $data['created_by'] = 'Manual Mapping';
            $data['name'] = $code;
            $attributemodel->setData($data);
            $attributemodel->save();

            $this->_mapOnErp($data['magento_id'], $data['odoo_id'], $code);
            
            $this->_getSession()->setUserData(false);
            $this->_redirect('odoomagentoconnect/*/');
        } catch (\Magento\Framework\Validator\Exception $e) {
            $messages = $e->getMessages();
            $this->messageManager->addMessages($messages);
            $this->redirectToEdit($data);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($e->getMessage()) {
                $this->messageManager->addError($e->getMessage());
            }
            $this->redirectToEdit($data);
        }
    }

    protected function _mapOnErp($magentoId, $odooId, $code)
    {
        $attrMapArray = [
            'name'=>$odooId,
            'odoo_id'=>$odooId,
            'ecomm_id'=>$magentoId,
            'ecomm_attribute_code'=>$code,
            'created_by'=>'Manual Mapping',
        ];
        $resp = $this->_connection->callOdooMethod('connector.attribute.mapping', 'create', [$attrMapArray]);
    }
    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Odoomagentoconnect::attribute_save');
    }

    /**
     * @param  \Magento\User\Model\User $model
     * @param  array                    $data
     * @return void
     */
    protected function redirectToEdit(array $data)
    {
        $this->_getSession()->setUserData($data);
        $data['entity_id']=isset($data['entity_id'])?$data['entity_id']:0;
        $arguments = $data['entity_id'] ? ['id' => $data['entity_id']]: [];
        $arguments = array_merge($arguments, ['_current' => true, 'active_tab' => '']);
        if ($data['entity_id']) {
            $this->_redirect('odoomagentoconnect/*/edit', $arguments);
        } else {
            $this->_redirect('odoomagentoconnect/*/index', $arguments);
        }
    }
}
