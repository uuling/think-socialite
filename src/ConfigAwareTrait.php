<?php
namespace Uuling\Socialite;

/**
 * Class ConfigAwareTrait
 */
trait ConfigAwareTrait
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * Set the config.
     *
     * @param Config|array|null $config
     */
    protected function setConfig($config)
    {
        $this->config = $config ? Util::ensureConfig($config) : new Config($config);
    }

    /**
     * Get the config
     * @return Config config object
     */
    public function getConfig()
    {
        return $this->config;
    }

    protected function prepareConfig(array $config)
    {
        $config = new Config($config);
        $config->setFallback($this->getConfig());

        return $config;
    }
}