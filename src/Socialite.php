<?php
namespace Uuling\Socialite;

use InvalidArgumentException;

/**
 * Class Socialite
 */
class Socialite implements SocialiteInterface
{
    use ConfigAwareTrait;

    /**
     * @var array
     */
    protected $adapters = [];

    /**
     * The support adapter
     * @var array
     */
    protected $supportAdapter = [
        'wechat' => 'WeChat',
    ];

    /**
     * Socialite constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->setConfig($config);
    }

    /**
     * Get the default adapter name.
     * @return string
     * @throws InvalidArgumentException
     */
    public function getDefaultAdapter()
    {
        throw new InvalidArgumentException('No Socialite adapter was specified.');
    }

    /**
     * Get a adapter instance
     *
     * @param string $driver
     * @return mixed
     */
    public function adapter($driver = null)
    {
        $driver = $driver ?: $this->getDefaultAdapter();

        if (!isset($this->adapters[$driver])) {
            $this->adapters[$driver] = $this->createAdapter($driver);
        }
        return $this->adapters[$driver];
    }

    /**
     * Create a new adapter instance.
     * @param string $driver
     * @return object AbstractAdapter
     * @throws InvalidArgumentException
     */
    protected function createAdapter($driver)
    {
        if (isset($this->supportAdapter[$driver])) {
            $adapter = $this->supportAdapter[$driver];

            $adapter = __NAMESPACE__ . '\\Adapter\\' . $adapter . 'Adapter';

            return $this->buildAdapter($adapter, $this->formatConfig($this->config->get($driver)));
        }

        throw new InvalidArgumentException("Adapter [$driver] not supported.");
    }

    /**
     * Build an OAuth 2 provider instance.
     * @param string $adapter
     * @param array $config
     * @return \Uuling\Socialite\AbstractAdapter
     */
    public function buildAdapter($adapter, $config)
    {
        return new $adapter($config['client_id'], $config['client_secret'], $config['redirect']);
    }

    /**
     * Format the server configuration.
     *
     * @param array $config
     *
     * @return array
     */
    public function formatConfig(array $config)
    {
        return array_merge([
            'identifier' => $config['client_id'],
            'secret' => $config['client_secret'],
            'callback_uri' => $config['redirect'],
        ], $config);
    }
}