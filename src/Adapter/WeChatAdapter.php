<?php
namespace Uuling\Socialite\Adapter;

use Uuling\Socialite\AccessTokenInterface;
use Uuling\Socialite\AdapterInterface;
use Uuling\Socialite\User;

/**
 * Class WechatAdapter.
 *
 * @link http://mp.weixin.qq.com/wiki/9/01f711493b5a02f24b04365ac5d8fd95.html [WeChat - 公众平台OAuth文档]
 * @link https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1419316505&token=&lang=zh_CN [网站应用微信登录开发指南]
 */
class WeChatAdapter extends AbstractAdapter implements AdapterInterface
{
    /**
     * The base url of WeChat API.
     *
     * @var string
     */
    protected $baseUrl = 'https://api.weixin.qq.com/sns';

    /**
     * User openid..
     *
     * @var string
     */
    protected $openId;

    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = ['snsapi_login'];

    /**
     * Indicates if the session state should be utilized.
     *
     * @var bool
     */
    protected $stateless = true;

    /**
     * Get the authentication URL for the adapter.
     *
     * @param string $state
     *
     * @return string
     */
    protected function getAuthUrl($state)
    {
        $path = 'oauth2/authorize';

        if (in_array('snsapi_login', $this->scopes)) {
            $path = 'qrconnect';
        }

        return $this->buildAuthUrlFromBase("https://open.weixin.qq.com/connect/{$path}", $state);
    }

    /**
     * {@inheritdoc}.
     */
    protected function buildAuthUrlFromBase($url, $state)
    {
        $query = http_build_query($this->getCodeFields($state), '', '&', $this->encodingType);

        return $url . '?' . $query . '#wechat_redirect';
    }

    /**
     * {@inheritdoc}.
     */
    protected function getCodeFields($state = null)
    {
        return [
            'appid' => $this->clientId,
            'redirect_uri' => $this->redirectUrl,
            'response_type' => 'code',
            'scope' => $this->formatScopes($this->scopes, $this->scopeSeparator),
            'state' => $state ?: md5(time()),
        ];
    }

    /**
     * Get the token URL for the adapter.
     *
     * @return string
     */
    protected function getTokenUrl()
    {
        return $this->baseUrl . '/oauth2/access_token';
    }

    /**
     * {@inheritdoc}.
     */
    protected function getUserByToken(AccessTokenInterface $token)
    {
        $scopes = explode(',', $token->getItem('scope', ''));

        if (in_array('snsapi_base', $scopes)) {
            return $token->toArray();
        }

        $response = $this->http($this->baseUrl . '/userinfo', [
            'access_token' => $token->getToken(),
            'openid' => $token->getItem('openid'),
            'lang' => 'zh_CN',
        ]);

        return json_decode($response, true);
    }

    /**
     * {@inheritdoc}.
     */
    protected function mapUserToObject(array $user)
    {
        return new User([
            'id' => $this->getArrayItem($user, 'openid'),
            'name' => $this->getArrayItem($user, 'nickname'),
            'nickname' => $this->getArrayItem($user, 'nickname'),
            'avatar' => $this->getArrayItem($user, 'headimgurl'),
            'email' => null,
        ]);
    }

    /**
     * {@inheritdoc}.
     */
    protected function getTokenFields($code)
    {
        return [
            'appid' => $this->clientId,
            'secret' => $this->clientSecret,
            'code' => $code,
            'grant_type' => 'authorization_code',
        ];
    }
}