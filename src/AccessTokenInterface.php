<?php
namespace Uuling\Socialite;

/**
 * Interface AccessTokenInterface
 */
interface AccessTokenInterface
{
    /**
     * Return the access token string.
     * @return string
     */
    public function getToken();
}