<?php

namespace CloudinaryExtension\Image\Transformation;

class Quality
{
    protected $value;

    protected function __construct($value)
    {
        $this->value = $value;
    }

    public static function fromString($value)
    {
        return new Quality($value);
    }

    public function __toString()
    {
        return $this->value;
    }
}
