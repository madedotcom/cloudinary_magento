<?php

namespace spec\CloudinaryAdapter;

use CloudinaryAdapter\Security\Key;
use CloudinaryAdapter\Security\Secret;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CredentialsSpec extends ObjectBehavior
{
    protected $key = 'aKey';
    protected $secret = 'aSecret';

    function let()
    {
        $this->beConstructedWith(Key::fromString($this->key), Secret::fromString($this->secret));
    }

    function it_returns_the_correct_key()
    {
        $this->getKey()->shouldBeLike($this->key);
    }

    function it_returns_the_correct_secret()
    {
        $this->getSecret()->shouldBeLike($this->secret);
    }
}
