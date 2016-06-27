<?php

namespace CloudinaryAdapter\Image;

use CloudinaryAdapter\Image\Transformation\Dimensions;
use CloudinaryAdapter\Image\Transformation\Dpr;
use CloudinaryAdapter\Image\Transformation\FetchFormat;
use CloudinaryAdapter\Image\Transformation\Format;
use CloudinaryAdapter\Image\Transformation\Gravity;
use CloudinaryAdapter\Image\Transformation\Quality;
use CloudinaryAdapter\Image\Transformation\Signature;

class Transformation
{
    protected $gravity;

    protected $dimensions;

    protected $crop;

    protected $fetchFormat;

    protected $quality;

    protected $format;

    protected $dpr;

    protected $signature;

    public function __construct()
    {
        $this->fetchFormat = FetchFormat::fromString(Format::FETCH_FORMAT_AUTO);
        $this->crop = 'pad';
        $this->format = Format::fromExtension('jpg');
        $this->validFormats = array('gif', 'jpg', 'png', 'svg');
    }

    public function withGravity(Gravity $gravity)
    {
        $this->gravity = $gravity;
        $this->crop = ((string) $gravity) ? 'crop' : 'pad';

        return $this;
    }

    public function withDimensions(Dimensions $dimensions)
    {
        $this->dimensions = $dimensions;

        return $this;
    }

    public function withFetchFormat(FetchFormat $fetchFormat)
    {
        $this->fetchFormat = $fetchFormat;

        return $this;
    }

    public function withFormat(Format $format)
    {
        if (in_array((string) $format, $this->validFormats)) {
            $this->format = $format;
        }

        return $this;
    }

    public function withQuality(Quality $quality)
    {
        $this->quality = $quality;

        return $this;
    }

    public function withDpr(Dpr $dpr)
    {
        $this->dpr = $dpr;

        return $this;
    }

    public function withSignature(Signature $signature)
    {
        $this->signature = $signature;

        return $this;
    }

    public function withOptimisationDisabled()
    {
        $this->withFetchFormat(FetchFormat::fromString(''));
        return $this;
    }

    public static function builder()
    {
        return new Transformation();
    }

    public function build()
    {
        return array(
            'fetch_format' => (string) $this->fetchFormat,
            'quality' => (string) $this->quality,
            'crop' => (string) $this->crop,
            'gravity' => (string) $this->gravity ?: null,
            'width' => $this->dimensions ? $this->dimensions->getWidth() : null,
            'height' => $this->dimensions ? $this->dimensions->getHeight() : null,
            'format' => (string) $this->format,
            'dpr' => (string) $this->dpr,
            'sign_url' => (string) $this->signature || $this->quality->isJpegMini()
        );
    }
}

