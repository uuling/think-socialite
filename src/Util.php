<?php
namespace Uuling\Socialite;

use LogicException;

class Util
{
    /**
     * Ensure a Config instance.
     * @param null|array|Config $config
     * @return Config config instance
     * @throw  LogicException
     */
    public static function ensureConfig($config)
    {
        if ($config === null) {
            return new Config();
        }

        if ($config instanceof Config) {
            return $config;
        }

        if (is_array($config)) {
            return new Config($config);
        }

        throw new LogicException('A config should either be an array or a Uuling\Config object.');
    }
}