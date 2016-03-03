<?php

use CloudinaryExtension\Cloud;
use CloudinaryExtension\CloudinaryImageProvider;
use CloudinaryExtension\Image;

class Made_Cloudinary_Model_Catalog_Product_Media_Config extends Mage_Catalog_Model_Product_Media_Config
{
    use Made_Cloudinary_Model_PreConditionsValidator;

    public function getMediaUrl($file)
    {
        if ($this->_imageShouldComeFromCloudinary($file)) {
            return $this->_getUrlForImage($file);
        }

        return parent::getMediaUrl($file);
    }

    public function getTmpMediaUrl($file)
    {
        if ($this->_imageShouldComeFromCloudinary($file)) {
            return $this->_getUrlForImage($file);
        }

        return parent::getTmpMediaUrl($file);
    }

    protected function _getUrlForImage($file)
    {
        $imageProvider = CloudinaryImageProvider::fromConfig($this->_getConfigHelper()->buildConfig());

        return (string)$imageProvider->transformImage(Image::fromPath($file));
    }
}
