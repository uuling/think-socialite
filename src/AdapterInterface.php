<?php
namespace Uuling\Socialite;

interface AdapterInterface
{
    /**
     * Redirect the user to the authentication page for the adapter.
     * @return mixed
     */
    public function redirect();

    /**
     * Get the User instance for the authenticated user.
     *
     * @param \Uuling\Socialite\AccessTokenInterface $token
     *
     * @return \Uuling\Socialite\User
     */
    public function user(AccessTokenInterface $token = null);
}