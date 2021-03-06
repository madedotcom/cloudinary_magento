<?php

namespace Domain;

use Behat\Behat\Context\Context;
use CloudinaryAdapter\Image;
use CloudinaryAdapter\Image\Transformation;
use CloudinaryAdapter\Security\CloudinaryEnvVar;
use ImageProviders\FakeImageProvider;

require_once 'PHPUnit/Framework/Assert/Functions.php';

/**
 * Defines application features from the specific context.
 */
class DeleteImageDomainContext implements Context
{
    const IMAGE_PROVIDER_ENVIRONMENT_VARIABLE = 'CLOUDINARY_URL=cloudinary://ABC123:DEF456@session-digital';

    protected $imageProvider;

    /**
     * @Transform :anImage
     */
    public function transformStringToAnImage($string)
    {
        return Image::fromPath($string);
    }

    /**
     * @Given the image provider has an image :anImage
     */
    public function theImageProviderHasAnImage($anImage)
    {

        envVar = CloudinaryEnvVar::fromString(self::IMAGE_PROVIDER_ENVIRONMENT_VARIABLE);
        $this->imageProvider = new FakeImageProvider($envVar);

        $this->imageProvider->upload($anImage);
    }

    /**
     * @When I delete the :anImage image
     */
    public function iDeleteTheImage($anImage)
    {
        $this->imageProvider->deleteImage($anImage);
    }

    /**
     * @Then the image :anImage should no longer be available in the image provider
     */
    public function theImageShouldNoLongerBeAvailableInTheImageProvider($anImage)
    {
        expect($this->imageProvider->transformImage($anImage, Transformation::builder()))->toBe('');
    }

}
