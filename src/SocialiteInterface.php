<?php
namespace Uuling\Socialite;

/**
 * Interface Socialite
 */
interface SocialiteInterface
{
    /**
     * Get an Oauth2 adapter implementation
     * @param null $adapter
     * @return Socialite
     */
    public function adapter($adapter = null);
}