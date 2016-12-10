<?php

/**
 * Created by ndh
 * Date: 10/12/2016
 */
namespace Demo\MaxQuantity\Model;

class ExtendCart extends \Magento\Checkout\Model\Cart
{
	private function hasMaxQuantity($product) {
		if($product->getData('max_qty') == -1) {
			return false;
		}
		return true;
	}
	
    public function addProduct($productInfo, $requestInfo = null)
    {	
		$product = $this->_getProduct($productInfo);
		$productMaxQty = (int)$product->getData('max_qty');
		$message = 'You can only buy this product '.$productMaxQty.($productMaxQty>1?' times':' time');
		
		if(!$this->hasMaxQuantity($product)) return parent::addProduct($productInfo, $requestInfo);
		
		$request = $this->_getProductRequest($requestInfo);
		$requestQty = 0;
		if($requestInfo != null) $requestQty = $request->getQty();
		
		if($requestQty > $productMaxQty) throw new \Magento\Framework\Exception\LocalizedException(__($message));
		
		$cartItems = $this->getItems();
		foreach($cartItems as $item) {
			if($item->getProductId() == $product->getId()  
					&& $item->getQty() + $requestQty > $productMaxQty) {
				
				throw new \Magento\Framework\Exception\LocalizedException(__($message));
			}
		}
			
        	return parent::addProduct($productInfo, $requestInfo);
    }
	
	public function updateItem($itemId, $requestInfo = null, $updatingParams = null)
	{
		$item = $this->getQuote()->getItemById($itemId);
		$request = $this->_getProductRequest($requestInfo);
		$requestQty = 0;
		if($requestInfo != null) $requestQty = $request->getQty();
		
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$product = $objectManager->get('Magento\Catalog\Model\Product')->load($item->getProductId());
		if(!$this->hasMaxQuantity($product)) return parent::addProduct($productInfo, $requestInfo);
		
		$productMaxQty = (int)$product->getData('max_qty');
		$message = 'You can only buy this product '.$productMaxQty.($productMaxQty>1?' times':' time');
		
		if($requestQty > $productMaxQty) {
			
			throw new \Magento\Framework\Exception\LocalizedException(__($message));
		}
		
		return parent::updateItem($itemId, $requestInfo, $updatingParams);
	}
	
	public function updateItems($data) {
		foreach ($data as $itemId => $itemInfo) {
			$qty = isset($itemInfo['qty']) ? (double)$itemInfo['qty'] : false;
            		if ($qty > 0) {
				$item = $this->getQuote()->getItemById($itemId);
                		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$product = $objectManager->get('Magento\Catalog\Model\Product')->load($item->getProductId());
				if(!$this->hasMaxQuantity($product)) continue;
				$productMaxQty = (int)$product->getData('max_qty');
				
				if($qty > $productMaxQty) {
					$message = 'You can only buy this product '.$productMaxQty.($productMaxQty>1?' times':' time');
					throw new \Magento\Framework\Exception\LocalizedException(__($message));
				}
            		}
		}
		return parent::updateItems($data);
	}
	
	public function addProductsByIds($productIds) {
		if (!empty($productIds)) {
			foreach ($productIds as $productId) {
                		$productId = (int)$productId;
                		if (!$productId) {
                    			continue;
                		}
				
                		$product = $this->_getProduct($productId);
				if(!$this->hasMaxQuantity($product)) continue;
				$cartItems = $this->getItems();
				
                		foreach($cartItems as $item) {
					$productMaxQty = (int)$product->getData('max_qty');
					if($item->getQty() > $productMaxQty
							&& $item->getProductId() == $product->getId()) {
						$message = 'You can only buy '.$product->getName().' '.$productMaxQty.($productMaxQty>1?' times':' time');
						throw new \Magento\Framework\Exception\LocalizedException(__($message));
					}
				}
          		}
		}
	}
}
