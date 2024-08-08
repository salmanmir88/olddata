<?php
namespace Evincemage\OrderImage\Plugin\Adminhtml;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class AddRenderer
{
	protected $_imageHelper;
    protected $_coreRegistry = null;
	
	public function __construct
	(
		\Magento\Framework\Registry $registry,
		\Evincemage\OrderImage\Helper\Image $imageHelper,
		\Magento\Catalog\Model\Product $productHelper
	)
	{
		$this->_coreRegistry = $registry;
		$this->_imageHelper = $imageHelper;
		$this->productHelper = $productHelper;	
	}

	public function afterGetColumns($defaultRender, $result)
	{
		if(is_array($result))
		{
			$newResult['image'] = 'col-image';
			foreach ($result as $key => $value) 
			{
				$newResult[$key] = $value;
			}

			$result = $newResult;
		}

		return $result;
	}

	public function beforeGetColumnHtml($defaultRender, \Magento\Framework\DataObject $item, $column, $field = null)
	{
		$html = '';
		switch ($column) 
		{
			case 'image':
				$this->_coreRegistry->register('is_image_renderer', 1);
				$this->_coreRegistry->register('evince_current_order_item', $item);
				break;
		}

		return [$item, $column, $field];
	}

	public function afterGetColumnHtml($defaultRender, $result)
	{
		$is_image = $this->_coreRegistry->registry('is_image_renderer');
		$currentItem = $this->_coreRegistry->registry('evince_current_order_item');
		$this->_coreRegistry->unregister('is_image_renderer');
		$this->_coreRegistry->unregister('evince_current_order_item');
		if ($is_image == 1)
		{
            return  $this->renderImage($currentItem->getProduct());
        }

        return $result;
	}

	protected function renderImage($product)
	{
		if(is_null($product))
		{
			$imgPath = $this->_imageHelper->getPlaceHolderImage('image');
			return "<img src=".$imgPath."  width='150px' height='150px'>";
		}
		$this->_imageHelper->addGallery($product);
		$images = $this->_imageHelper->getGalleryImages($product);
		$objectManager =\Magento\Framework\App\ObjectManager::getInstance();
		$helperImport = $objectManager->get('\Magento\Catalog\Helper\Image');
		$imageUrl = $helperImport->init($product, 'product_page_image_small')
                ->setImageFile($product->getSmallImage()) // image,small_image,thumbnail
                ->resize(380)
                ->getUrl();
		return "<img src=".$imageUrl." alt=".$product->getName().">";
		foreach ($images as $image) 
		{
			$item = $image->getData();
			if(isset($item['media_type'])&&$item['media_type']=='image')
			{
				$imgPath = isset($item['small_image_url']) ? $item['small_image_url'] : null;
				if($this->endsWith($imgPath,'placeholder/.jpg'))
				{
					$newImgPath = str_replace("placeholder/.jpg","placeholder/thumbnail.jpg",$imgPath);
					return "<img src=".$newImgPath." alt=".$product->getName()." width='150px' height='150px'>";
				}

				return "<img src=".$imgPath." alt=".$product->getName()." width='150px' height='150px'>";    				
			}
		}

		return null;
	}

	public function endsWith($haystack, $needle)
	{
    	$length = strlen( $needle );
    	if( !$length )
    	{
        	return true;
    	}
    	return substr( $haystack, -$length ) === $needle;
	}
}

