<?php

use CloudinaryExtension\CloudinaryImageProvider;
use CloudinaryExtension\Image;


class Made_Cloudinary_Model_Image extends Mage_Core_Model_Abstract
{
    use Made_Cloudinary_Model_PreConditionsValidator;

    public function upload(array $imageDetails)
    {
        $imageManager = $this->_getImageProvider();
        $imageManager->upload(Image::fromPath($this->_imageFullPathFromImageDetails($imageDetails)));

        Mage::getModel('made_cloudinary/synchronisation')
            ->setValueId($imageDetails['value_id'])
            ->setValue($imageDetails['file'])
            ->tagAsSynchronized();
    }

    protected function _imageFullPathFromImageDetails($imageDetails)
    {
        return  $this->_getMediaBasePath() . $this->_getImageDetailFromKey($imageDetails, 'file');
    }

    protected function _getImageDetailFromKey(array $imageDetails, $key)
    {
        if (!array_key_exists($key, $imageDetails)) {
            throw new Made_Cloudinary_Model_Exception_BadFilePathException("Invalid image data structure. Missing " . $key);
        }
        return $imageDetails[$key];
    }

    protected function _getMediaBasePath()
    {
        return Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath();
    }

    public function deleteImage($imageName)
    {
        $this->_getImageProvider()->deleteImage(Image::fromPath($imageName));
    }

    public function getUrl($imagePath)
    {
        $imageProvider = $this->_getImageProvider();
        return (string)$imageProvider->transformImage(Image::fromPath($imagePath));
    }

    protected function _getImageProvider()
    {
        return CloudinaryImageProvider::fromConfiguration($this->_getConfigHelper()->buildConfiguration());
    }
}
