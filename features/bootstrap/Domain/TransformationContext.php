<?php

namespace Domain;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use CloudinaryAdapter\Cloud;
use CloudinaryAdapter\Config;
use CloudinaryAdapter\Credentials;
use CloudinaryAdapter\Image;
use CloudinaryAdapter\Image\Transformation;
use CloudinaryAdapter\Image\Transformation\Dimensions;
use CloudinaryAdapter\Image\Transformation\Dpr;
use CloudinaryAdapter\Image\Transformation\Quality;
use CloudinaryAdapter\Security\Key;
use CloudinaryAdapter\Security\Secret;
use ImageProviders\TransformingImageProvider;

require_once 'PHPUnit/Framework/Assert/Functions.php';

/**
 * Defines application features from the specific context.
 */
class TransformationContext implements Context
{

    protected $imageProvider;

    protected $image;

    protected $imageUrl;

    protected $config;

    public function __construct()
    {
        $this->config = Config::fromCloudAndCredentials(
            Cloud::fromName('aCloudName'),
            new Credentials(Key::fromString('aKey'), Secret::fromString('aSecret'))
        );

        $this->config->getDefaultTransformation()
            ->withQuality(Quality::fromString('80'))
            ->withDpr(Dpr::fromString('1.0'))
        ;

        $this->imageProvider = new TransformingImageProvider($this->config);
    }

    /**
     * @Transform :aDpr
     */
    public function transformStringToDpr($string)
    {
        return Dpr::fromString($string);
    }

    /**
     * @Transform :aQuality
     */
    public function transformStringToQuality($string)
    {
        return Quality::fromString($string);
    }

    /**
     * @Transform :aDimension
     */
    public function transformStringToDimensions($string)
    {
        $dimensions = explode('x', $string);

        return Dimensions::fromWidthAndHeight($dimensions[0], $dimensions[1]);
    }

    /**
     * @Given there's an image :anImage in the image provider
     */
    public function thereIsAnImageInTheImageProvider(Image $anImage)
    {
        $this->image = $anImage;
        $this->imageProvider->upload($this->image);
    }

    /**
     * @When I request the image from the image provider
     */
    public function iRequestTheImageFromTheImageProvider()
    {
        $this->imageUrl = $this->imageProvider->transformImage(
            $this->image,
            $this->config->getDefaultTransformation()
        );
    }

    /**
     * @Then I should get an optimised image from the image provider
     */
    public function iShouldGetAnOptimisedImageFromTheImageProvider()
    {
        expect($this->urlIsOptimised())->toBe(true);
    }

    /**
     * @Given image optimisation is disabled
     */
    public function imageOptimisationIsDisabled()
    {
        $this->config->getDefaultTransformation()->withOptimisationDisabled();
    }

    /**
     * @Then I should get the original image from the image provider
     */
    public function iShouldGetTheOriginalImageFromTheImageProvider()
    {
        expect($this->urlIsOptimised())->toBe(false);
    }

    /**
     * @Then I should get an image with :aQuality percent quality from the image provider
     */
    public function iShouldGetAnImageWithPercentQualityFromTheImageProvider(Quality $aQuality)
    {
        expect($this->isPercentageQuality((string)$aQuality))->toBe(true);
    }

    /**
     * @Given I set image quality to :aQuality percent
     */
    public function iTransformTheImageToHavePercentQuality(Quality $aQuality)
    {
        $this->config->getDefaultTransformation()->withQuality($aQuality);
    }

    /**
     * @When I ask the image provider for :imageName transformed to :aDimension
     */
    public function iRequestTheImageProvideForTransformedTo($imageName, Dimensions $aDimension)
    {
        $this->imageUrl = $this->imageProvider->transformImage(
            Image::fromPath($imageName),
            Transformation::builder()->withDimensions($aDimension)
        );
    }

    /**
     * @Then I should receive that image with the dimensions :aDimension
     */
    public function iShouldReceiveThatImageWithTheDimensions(Dimensions $aDimension)
    {
        expect($this->hasDimensions($aDimension))->toBe(true);
    }

    /**
     * @Then I should get the image :image with the default DPR
     */
    public function iShouldGetAnImageWithTheDefaultDpr($image)
    {
        expect(basename($this->imageUrl))->toBe($image);
        expect($this->hasDefaultDpr())->toBe(true);
    }

    /**
     * @Given my DPR is set to :aDpr in the configuration
     */
    public function myDprIsSetToInTheConfig(Dpr $aDpr)
    {
        $this->config->getDefaultTransformation()->withDpr($aDpr);
    }

    /**
     * @Then I should get an image with DPR :aDpr
     */
    public function iShouldGetAnImageWithDpr(Dpr $aDpr)
    {
        expect($this->hasDpr($aDpr))->toBe(true);
    }

    protected function urlIsOptimised()
    {
        return strpos($this->imageUrl, 'fetch_format=auto') !== false;
    }

    protected function isPercentageQuality($percentage)
    {
        return strpos($this->imageUrl, "quality=$percentage") !== false;
    }

    protected function hasDimensions(Dimensions $dimension)
    {
        $hasWidth = strpos($this->imageUrl, "width={$dimension->getWidth()}") !== false;
        $hasHeight = strpos($this->imageUrl, "height={$dimension->getHeight()}") !== false;
        return $hasWidth && $hasHeight;
    }

    protected function hasDefaultDpr()
    {
        return $this->hasDpr('1.0');
    }

    protected function hasDpr($dpr)
    {
        return strpos($this->imageUrl, "dpr=$dpr") !== false;
    }
}