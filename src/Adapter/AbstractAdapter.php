<?php
namespace Uuling\Socialite\Adapter;

use ArrayAccess;
use Closure;
use Uuling\Socialite\AccessToken;
use Uuling\Socialite\AccessTokenInterface;
use Uuling\Socialite\AdapterInterface;
use think\Session;
use think\Request;
use Exception;
use Uuling\Socialite\InvalidStateException;

abstract class AbstractAdapter implements AdapterInterface
{
    /**
     * Socialite request
     * @var Request
     */
    protected $request;

    /**
     * Socialite client id
     * @var string
     */
    protected $clientId;

    /**
     * Socialite client secret.
     *
     * @var string
     */
    protected $clientSecret;

    /**
     * The redirect URL.
     *
     * @var string
     */
    protected $redirectUrl;

    /**
     * The custom parameters to be sent with the request.
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = [];

    /**
     * The separating character for the requested scopes.
     *
     * @var string
     */
    protected $scopeSeparator = ',';

    /**
     * The type of the encoding in the query.
     *
     * @var int Can be either PHP_QUERY_RFC3986 or PHP_QUERY_RFC1738.
     */
    protected $encodingType = PHP_QUERY_RFC1738;

    /**
     * Indicates if the session state should be utilized.
     *
     * @var bool
     */
    protected $stateless = false;

    /**
     * AbstractAdapter constructor.
     * @param string $clientId
     * @param string $clientSecret
     * @param string|null $redirectUrl
     */
    public function __construct($clientId, $clientSecret, $redirectUrl = null)
    {
        $this->request = Request::instance();
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUrl = $redirectUrl;
    }

    /**
     * Get the authentication URL for the adapter.
     *
     * @param  string $state
     * @return string
     */
    abstract protected function getAuthUrl($state);

    /**
     * Get the token URL for the adapter.
     *
     * @return string
     */
    abstract protected function getTokenUrl();

    /**
     * Get the raw user for the given access token.
     *
     * @param AccessTokenInterface $token
     *
     * @return array
     */
    abstract protected function getUserByToken(AccessTokenInterface $token);

    /**
     * Map the raw user array to a Socialite User instance.
     *
     * @param array $user
     *
     * @return \Uuling\Socialite\User
     */
    abstract protected function mapUserToObject(array $user);

    /**
     * Redirect the user of the application to the provider's authentication screen.
     *
     * @param string $redirectUrl
     * @return void
     */
    public function redirect($redirectUrl = null)
    {
        $state = null;

        if (!is_null($redirectUrl)) {
            $this->redirectUrl = $redirectUrl;
        }

        if ($this->useState()) {
            $state = sha1(uniqid(mt_rand(1, 1000000), true));
            Session::set('state', $state);
        }

        header('Location: ' . $this->getAuthUrl($state));
    }

    /**
     * Get the authentication URL for the adapter.
     * @param string $url
     * @param string $state
     * @return string
     */
    protected function buildAuthUrlFromBase($url, $state)
    {
        return $url . '?' . http_build_query($this->getCodeFields($state), '', '&', $this->encodingType);
    }

    /**
     * Get the GET parameters for the code request.
     *
     * @param string|null $state
     *
     * @return array
     */
    protected function getCodeFields($state = null)
    {
        $fields = array_merge([
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUrl,
            'scope' => $this->formatScopes($this->scopes, $this->scopeSeparator),
            'response_type' => 'code',
        ], $this->parameters);

        if ($this->useState()) {
            $fields['state'] = $state;
        }

        return $fields;
    }

    /**
     * Format the given scopes.
     *
     * @param array $scopes
     * @param string $scopeSeparator
     * @return string
     */
    protected function formatScopes(array $scopes, $scopeSeparator)
    {
        return implode($scopeSeparator, $scopes);
    }

    /**
     * {@inheritdoc}.
     */
    public function user(AccessTokenInterface $token = null)
    {
        if (is_null($token) && $this->hasInvalidState()) {
            throw new InvalidStateException();
        }

        $token = $token ?: $this->getAccessToken($this->getCode());

        $user = $this->getUserByToken($token);

        $user = $this->mapUserToObject($user)->setRaw($user);

        return $user->setToken($token->getToken())
            ->setRefreshToken($token->getItem('refresh_token'))
            ->setExpiresIn($token->getItem('expires_in'));
    }

    /**
     * Get a Social User instance from a known access token.
     *
     * @param  AccessTokenInterface $token
     * @return \Uuling\Socialite\User
     */
    public function userFromToken(AccessTokenInterface $token)
    {
        $user = $this->mapUserToObject($this->getUserByToken($token));

        return $user->setToken($token->getToken());
    }

    /**
     * Determine if the adapter is operating with state.
     *
     * @return bool
     */
    public function useState()
    {
        return !$this->stateless;
    }

    /**
     * Determine if the current request / session has a mismatching "state".
     *
     * @return bool
     */
    protected function hasInvalidState()
    {
        if ($this->isStateless()) {
            return false;
        }

        $state = Session::get('state');

        return !(strlen($state) > 0 && $this->request->get('state') === $state);
    }

    /**
     * Determine if the provider is operating as stateless.
     *
     * @return bool
     */
    protected function isStateless()
    {
        return $this->stateless;
    }

    /**
     * Indicates that the provider should operate as stateless.
     *
     * @return $this
     */
    public function stateless()
    {
        $this->stateless = true;

        return $this;
    }

    /**
     * Get the code from the request.
     *
     * @return string
     */
    protected function getCode()
    {
        return $this->request->get('code');
    }

    /**
     * Set redirect url.
     *
     * @param string $redirectUrl
     *
     * @return $this
     */
    public function setRedirectUrl($redirectUrl)
    {
        $this->redirectUrl = $redirectUrl;

        return $this;
    }

    /**
     * Return the redirect url.
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }

    /**
     * Set the custom parameters of the request.
     *
     * @param array $parameters
     *
     * @return $this
     */
    public function with(array $parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Get the access token for the given refresh access token.
     *
     * @param AccessTokenInterface $token
     */
    public function refreshAccessToken(AccessTokenInterface $token)
    {
        // TODO
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
        $response = $this->http($this->getTokenUrl(), $this->getTokenFields($code), 'POST', ['Accept' => 'application/json']);

        return $this->parseAccessToken($response);
    }

    /**
     * Get the access token from the token response body.
     *
     * @param string $body Curl return content
     * @return \Uuling\Socialite\AccessToken
     */
    protected function parseAccessToken($body)
    {
        return new AccessToken((array)json_decode($body, true));
    }

    /**
     * Get the POST fields for the token request.
     *
     * @param string $code
     *
     * @return array
     */
    protected function getTokenFields($code)
    {
        return [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'redirect_uri' => $this->redirectUrl,
        ];
    }

    /**
     * Set the scopes of the requested access.
     *
     * @param array $scopes
     *
     * @return $this
     */
    public function scopes(array $scopes)
    {
        $this->scopes = $scopes;

        return $this;
    }

    /**
     * Get the current scopes.
     *
     * @return array
     */
    public function getScopes()
    {
        return $this->scopes;
    }

    /**
     * Get an item from an array using "dot" notation.
     *
     * @param  \ArrayAccess|array $array
     * @param  string $key
     * @param  mixed $default
     * @return mixed
     */
    protected function getArrayItem($array, $key, $default = null)
    {
        if (!(is_array($array) || $array instanceof ArrayAccess)) {
            return $default instanceof Closure ? $default() : $default;
        }

        if (is_null($key)) {
            return $array;
        }

        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if ((is_array($array) || $array instanceof ArrayAccess) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return $default instanceof Closure ? $default() : $default;
            }
        }

        return $array;
    }

    /**
     * Http 请求
     * @param string $url
     * @param array $params
     * @param string $method
     * @param array $header
     * @return mixed
     * @throws Exception
     */
    public function http($url, array $params = [], $method = 'GET', $header = [])
    {
        $opts = [
            CURLOPT_TIMEOUT => 30,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => $header,
        ];

        /* 根据请求类型设置特定参数 */
        switch (strtoupper($method)) {
            case 'GET':
                $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
                break;
            case 'POST':
                //判断是否传输文件
                $params = http_build_query($params);
                $opts[CURLOPT_URL] = $url;
                $opts[CURLOPT_POST] = 1;
                $opts[CURLOPT_POSTFIELDS] = $params;
                break;
            default:
                throw new Exception('不支持的请求方式！');
        }

        /* 初始化并执行curl请求 */
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if ($error) {
            throw new Exception('请求发生错误：' . $error);
        }

        return $data;
    }
}