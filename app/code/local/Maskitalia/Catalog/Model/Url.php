<?php

class Maskitalia_Catalog_Model_Url extends Mage_Catalog_Model_Url
{
	

    /**
     * Generate either id path, request path or target path for product and/or category
     *
     * For generating id or system path, either product or category is required
     * For generating request path - category is required
     * $parentPath used only for generating category path
     *
     * @param string $type
     * @param Varien_Object $product
     * @param Varien_Object $category
     * @param string $parentPath
     * @return string
     * @throws Mage_Core_Exception
     */
    public function generatePath($type = 'target', $product = null, $category = null, $parentPath = null)
    {
        if (!$product && !$category) {
            Mage::throwException(Mage::helper('core')->__('Please specify either a category or a product, or both.'));
        }

        // generate id_path
        if ('id' === $type) {
            if (!$product) {
                return 'category/' . $category->getId();
            }
            if ($category && $category->getLevel() > 1) {
                return 'product/' . $product->getId() . '/' . $category->getId();
            }
            return 'product/' . $product->getId();
        }

        // generate request_path
        if ('request' === $type) {
            // for category
            if (!$product) {
                if ($category->getUrlKey() == '') {
                    $urlKey = $this->getCategoryModel()->formatUrlKey($category->getName());
                }
                else {
                    $urlKey = $this->getCategoryModel()->formatUrlKey($category->getUrlKey());
                }

                $categoryUrlSuffix = $this->getCategoryUrlSuffix($category->getStoreId());
                //if (null === $parentPath) {
                //    $parentPath = $this->getResource()->getCategoryParentPath($category);
                //}
                //elseif ($parentPath == '/') {
                    $parentPath = '';
                //}
                $parentPath = Mage::helper('catalog/category')->getCategoryUrlPath($parentPath,
                    true, $category->getStoreId());

                return $this->getUnusedPath($category->getStoreId(), $parentPath . $urlKey . $categoryUrlSuffix,
                    $this->generatePath('id', null, $category)
                );
            }

            // for product & category
            if (!$category) {
                Mage::throwException(Mage::helper('core')->__('A category object is required for determining the product request path.')); // why?
            }

            if ($product->getUrlKey() == '') {
                $urlKey = $this->getProductModel()->formatUrlKey($product->getName());
            }
            else {
                $urlKey = $this->getProductModel()->formatUrlKey($product->getUrlKey());
            }
            $productUrlSuffix  = $this->getProductUrlSuffix($category->getStoreId());
            if ($category->getLevel() > 1) {
                // To ensure, that category has url path either from attribute or generated now
                $this->_addCategoryUrlPath($category);
                $categoryUrl = Mage::helper('catalog/category')->getCategoryUrlPath($category->getUrlPath(),
                    false, $category->getStoreId());
                return $this->getUnusedPath($category->getStoreId(), $categoryUrl . '/' . $urlKey . $productUrlSuffix,
                    $this->generatePath('id', $product, $category)
                );
            }

            // for product only
            return $this->getUnusedPath($category->getStoreId(), $urlKey . $productUrlSuffix,
                $this->generatePath('id', $product)
            );
        }

        // generate target_path
        if (!$product) {
            return 'catalog/category/view/id/' . $category->getId();
        }
        if ($category && $category->getLevel() > 1) {
            return 'catalog/product/view/id/' . $product->getId() . '/category/' . $category->getId();
        }
        return 'catalog/product/view/id/' . $product->getId();
    }

}
