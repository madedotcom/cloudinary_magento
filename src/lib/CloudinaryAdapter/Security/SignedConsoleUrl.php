<?php

namespace CloudinaryAdapter\Security;

use CloudinaryAdapter\Credentials;

class SignedConsoleUrl
{

    protected $signedConsoleUrl;

    protected function __construct(ConsoleUrl $url, Credentials $credentials)
    {
        $params = array("timestamp" => time(), "mode" => "check");
        $params["signature"] = (string)ApiSignature::fromSecretAndParams($credentials->getSecret(), $params);
        $params["api_key"] = (string)$credentials->getKey();
        $query = http_build_query($params);

        $this->signedConsoleUrl = (string)$url . '?' . $query;
    }

    public static function fromConsoleUrlAndCredentials(ConsoleUrl $url, Credentials $credentials)
    {
        return new SignedConsoleUrl($url, $credentials);
    }

    public function __toString()
    {
        return $this->signedConsoleUrl;
    }
}
