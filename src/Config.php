<?php
namespace Uuling\Socialite;

use InvalidArgumentException;

class Config
{
    /**
     * @var array
     */
    protected $settings = [];

    /**
     * @var Config
     */
    protected $fallback;

    /**
     * Config constructor.
     * @param array $settings
     */
    public function __construct(array $settings = [])
    {
        $this->settings = $settings;
    }

    public function has($key)
    {
        if (array_key_exists($key, $this->settings)) {
            return true;
        }

        return $this->fallback instanceof Config ? $this->fallback->has($key) : false;
    }

    /**
     * Get a setting.
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed config setting or default when not found
     */
    public function get($key, $default = null)
    {
        if (is_null($key)) {
            return $this->settings;
        }

        if (!array_key_exists($key, $this->settings)) {
            return $this->getDefault($key, $default);
        }

        return $this->settings[$key];
    }

    /**
     * Try to retrieve a default setting from a config fallback.
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed config setting or default when not found
     */
    protected function getDefault($key, $default)
    {
        if (!$this->fallback) {
            return $default;
        }

        return $this->fallback->get($key, $default);
    }

    /**
     * Set an array item to a given value using "dot" notation.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return $this
     */
    public function set($key, $value)
    {
        if (is_null($key)) {
            throw new InvalidArgumentException('Invalid config key.');
        }

        $this->settings[$key] = $value;

        return $this;
    }

    /**
     * Set the fallback
     * @param Config $fallback
     * @return $this
     */
    public function setFallback(Config $fallback)
    {
        $this->fallback = $fallback;

        return $this;
    }
}