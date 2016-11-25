<?php
namespace Uuling\Socialite\Adapter;

use Uuling\Socialite\AccessToken;
use Uuling\Socialite\AccessTokenInterface;
use Uuling\Socialite\AdapterInterface;
use Uuling\Socialite\User;

class QQAdapter extends AbstractAdapter implements AdapterInterface
{
    /**
     * The base url of QQ API.
     *
     * @var string
     */
    protected $baseUrl = 'https://graph.qq.com';

    /**
     * User openid.
     *
     * @var string
     */
    protected $openId;

    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = ['get_user_info'];

    /**
     * The uid of user authorized.
     *
     * @var int
     */
    protected $uid;

    /**
     * Get the authentication URL for the adapter.
     *
     * @param string $state
     *
     * @return string
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase($this->baseUrl . '/oauth2.0/authorize', $state);
    }

    /**
     * Get the token URL for the adapter.
     *
     * @return string
     */
    protected function getTokenUrl()
    {
        return $this->baseUrl . '/oauth2.0/token';
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
     * Get the access token for the given code.
     *
     * @param string $code
     *
     * @return \Uuling\Socialite\AccessToken
     */
    public function getAccessToken($code)
    {
        $response = $this->http($this->getTokenUrl(), $this->getTokenFields($code));

        return $this->parseAccessToken($response);
    }

    /**
     * Get the access token from the token response body.
     *
     * @param string $body
     *
     * @return \Uuling\Socialite\AccessToken
     */
    public function parseAccessToken($body)
    {
        parse_str($body, $token);

        return new AccessToken($token);
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
        $response = $this->http($this->baseUrl . '/oauth2.0/me?access_token=' . $token->getToken());

        $this->openId = json_decode($this->removeCallback($response->getBody()->getContents()), true)['openid'];

        $queries = [
            'access_token' => $token->getToken(),
            'openid' => $this->openId,
            'oauth_consumer_key' => $this->clientId,
        ];

        $response = $this->http($this->baseUrl . '/user/get_user_info', $queries);

        return json_decode($this->removeCallback($response), true);
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
            'id' => $this->openId,
            'nickname' => $this->getArrayItem($user, 'nickname'),
            'name' => $this->getArrayItem($user, 'nickname'),
            'email' => $this->getArrayItem($user, 'email'),
            'avatar' => $this->getArrayItem($user, 'figureurl_qq_2'),
        ]);
    }

    /**
     * Remove the fucking callback parentheses.
     *
     * @param string $response
     *
     * @return string
     */
    protected function removeCallback($response)
    {
        if (strpos($response, 'callback') !== false) {
            $left_pos = strpos($response, '(');
            $right_pos = strrpos($response, ')');
            $response = substr($response, $left_pos + 1, $right_pos - $left_pos - 1);
        }

        return $response;
    }
}