<?php

use CloudinaryAdapter\CloudinaryImageProvider;
use CloudinaryAdapter\Image;
use CloudinaryAdapter\Image\Transformation;
use CloudinaryAdapter\Image\Transformation\Dimensions;

class Made_Cloudinary_Model_Cms_Wysiwyg_Images_Storage extends Mage_Cms_Model_Wysiwyg_Images_Storage
{
    use Made_Cloudinary_Model_PreConditionsValidator;

    public function getThumbnailUrl($filePath, $checkFile = false)
    {
        if ($this->_serveFromCloud($filePath)) {
            $imageProvider = $this->_buildImageProvider();
            $imageDimensions = $this->_buildImageDimensions();
            $defaultTransformation = $this->_getConfigHelper()->buildConfig()->getDefaultTransform();

            return (string)$imageProvider->getTransformedImageUrl(
                $this->_getImage($filePath), $defaultTransformation->withDimensions($imageDimensions)
            );
        }
        return parent::getThumbnailUrl($filePath, $checkFile);
    }

    protected function _buildImageProvider()
    {
        return CloudinaryImageProvider::fromConfig($this->_getConfigHelper()->buildConfig());
    }

    protected function _buildImageDimensions()
    {
        return Dimensions::fromWidthAndHeight(
            $this->getConfigData('resize_width'),
            $this->getConfigData('resize_height')
        );
    }

    public function uploadFile($targetPath, $type = null, $newFilename = null)
    {

        if(!$this->_getConfigHelper()->isEnabled()) {
           return parent::uploadFile($targetPath, $type);
        }

        $uploader = new Made_Cloudinary_Model_Cms_Uploader('image');
        if ($allowed = $this->getAllowedExtensions($type)) {
            $uploader->setAllowedExtensions($allowed);
        }
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(false);
        $result = $uploader->save($targetPath, $newFilename);

        if (!$result) {
            Mage::throwException( Mage::helper('cms')->__('Cannot upload file.') );
        }

        // create thumbnail
        $this->resizeFile($targetPath . DS . $uploader->getUploadedFileName(), true);

        $result['cookie'] = array(
            'name'     => session_name(),
            'value'    => $this->getSession()->getSessionId(),
            'lifetime' => $this->getSession()->getCookieLifetime(),
            'path'     => $this->getSession()->getCookiePath(),
            'domain'   => $this->getSession()->getCookieDomain()
        );

        return $result;
    }

    /**
     * Delete file (and its thumbnail if exists) from storage as per stock parent method
     * Also remove wysiwyg image from Cloudinary, and remove sync table record
     *
     * @param string $target File path to be deleted
     * @return $this
     */
    public function deleteFile($target)
    {
        parent::deleteFile($target);
        // delete from cloudinary
        Mage::getModel('made_cloudinary/image')->deleteImage($target);
        // delete sync record
        $sync = Mage::getModel('made_cloudinary/cms_sync');
        $sync->load($sync->removeMediaPrefix($target), 'media_path')->delete();
        return $this;
    }

}