<?php
namespace Uuling\Socialite;

use ArrayAccess;
use JsonSerializable;
use InvalidArgumentException;

/**
 * Class AccessToken
 * @package Uuling\Socialite
 */
class AccessToken implements AccessTokenInterface, ArrayAccess, JsonSerializable
{
    use ItemTrait;

    /**
     * AccessToken constructor.
     *
     * @param array $value
     */
    public function __construct(array $value)
    {
        if (!isset($value['access_token']) || empty($value['access_token'])) {
            throw new InvalidArgumentException('The key "access_token" could not be empty.');
        }

        $this->items = $value;
    }

    /**
     * Return the access token string.
     *
     * @return string
     */
    public function getToken()
    {
        return $this->getItem('access_token');
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return strval($this->getItem('access_token', ''));
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return $this->getToken();
    }
}