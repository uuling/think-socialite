<?php
namespace Uuling\Socialite\Adapter;

use Uuling\Socialite\AdapterInterface;
use Uuling\Socialite\AccessTokenInterface;
use Uuling\Socialite\User;

class WeiboAdapter extends AbstractAdapter implements AdapterInterface
{
    /**
     * The base url of Weibo API.
     *
     * @var string
     */
    protected $baseUrl = 'https://api.weibo.com';

    /**
     * The API version for the request.
     *
     * @var string
     */
    protected $version = '2';

    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = ['email'];

    /**
     * The uid of user authorized.
     *
     * @var int
     */
    protected $uid;

    /**
     * Get the authentication URL for the provider.
     *
     * @param string $state
     *
     * @return string
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase($this->baseUrl . '/oauth2/authorize', $state);
    }

    /**
     * Get the token URL for the provider.
     *
     * @return string
     */
    protected function getTokenUrl()
    {
        return $this->baseUrl . '/' . $this->version . '/oauth2/access_token';
    }

    /**
     * Get the Post fields for the token request.
     *
     * @param string $code
     *
     * @return array
     */
    protected function getTokenFields($code)
    {
        return parent::getTokenFields($code) + ['grant_type' => 'authorization_code'];
    }

    /**
     * Get the raw user for the given access token.
     *
     * @param \Uuling\Socialite\AccessTokenInterface $token
     *
     * @return array
     */
    protected function getUserByToken(AccessTokenInterface $token)
    {
        $response = $this->http($this->baseUrl . '/' . $this->version . '/users/show.json', [
            'uid' => $token['uid'],
            'access_token' => $token->getToken(),
        ], 'GET', [
            'Accept' => 'application/json',
        ]);

        return json_decode($response, true);
    }

    /**
     * Map the raw user array to a Socialite User instance.
     *
     * @param array $user
     *
     * @return \Uuling\Socialite\User
     */
    protected function mapUserToObject(array $user)
    {
        return new User([
            'id' => $this->getArrayItem($user, 'id'),
            'nickname' => $this->getArrayItem($user, 'screen_name'),
            'name' => $this->getArrayItem($user, 'name'),
            'email' => $this->getArrayItem($user, 'email'),
            'avatar' => $this->getArrayItem($user, 'avatar_large'),
        ]);
    }
}
