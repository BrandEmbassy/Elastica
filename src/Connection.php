<?php

namespace Elastica;

use Elastic\Elasticsearch\Client as ElasticsearchClient;
use Elastic\Elasticsearch\ClientBuilder;
use Elastica\Exception\InvalidException;
use Elastica\Transport\AbstractTransport;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Elastica connection instance to an elasticasearch node.
 *
 * @author   Nicolas Ruflin <spam@ruflin.com>
 */
class Connection extends Param
{
    /**
     * Cached elasticsearch-php v9 client instance.
     */
    private ?ElasticsearchClient $_client = null;

    /**
     * Optional request counter to track HTTP requests made through the ES9 client.
     */
    private ?RequestCounterInterface $_requestCounter = null;

    public function setRequestCounter(RequestCounterInterface $requestCounter): void
    {
        $this->_requestCounter = $requestCounter;
        // Reset cached client so a new one is built with the counter middleware.
        $this->_client = null;
    }

    /**
     * Default elastic search port.
     */
    public const DEFAULT_PORT = 9200;

    /**
     * Default host.
     */
    public const DEFAULT_HOST = 'localhost';

    /**
     * Default transport.
     *
     * @var string
     */
    public const DEFAULT_TRANSPORT = 'Http';

    /**
     * Default compression.
     *
     * @var bool
     */
    public const DEFAULT_COMPRESSION = false;

    /**
     * Number of seconds after a timeout occurs for every request
     * If using indexing of file large value necessary.
     */
    public const TIMEOUT = 300;

    /**
     * Number of seconds after a connection timeout occurs for every request during the connection phase.
     *
     * @see Connection::setConnectTimeout();
     */
    public const CONNECT_TIMEOUT = 0;

    /**
     * Creates a new connection object. A connection is enabled by default.
     *
     * @param array $params OPTIONAL Connection params: host, port, transport, timeout. All are optional
     */
    public function __construct(array $params = [])
    {
        $this->setParams($params);
        $this->setEnabled(true);

        // Set empty config param if not exists
        if (!$this->hasParam('config')) {
            $this->setParam('config', []);
        }
    }

    /**
     * @return int Server port
     */
    public function getPort()
    {
        return $this->hasParam('port') ? $this->getParam('port') : self::DEFAULT_PORT;
    }

    /**
     * @param int $port
     *
     * @return $this
     */
    public function setPort($port)
    {
        return $this->setParam('port', (int) $port);
    }

    /**
     * @return string Host
     */
    public function getHost()
    {
        return $this->hasParam('host') ? $this->getParam('host') : self::DEFAULT_HOST;
    }

    /**
     * @param string $host
     *
     * @return $this
     */
    public function setHost($host)
    {
        return $this->setParam('host', $host);
    }

    /**
     * @return string|null Host
     */
    public function getProxy()
    {
        return $this->hasParam('proxy') ? $this->getParam('proxy') : null;
    }

    /**
     * Set proxy for http connections. Null is for environmental proxy,
     * empty string to disable proxy and proxy string to set actual http proxy.
     *
     * @see http://curl.haxx.se/libcurl/c/curl_easy_setopt.html#CURLOPTPROXY
     *
     * @param string|null $proxy
     *
     * @return $this
     */
    public function setProxy($proxy)
    {
        return $this->setParam('proxy', $proxy);
    }

    /**
     * @return array|string
     */
    public function getTransport()
    {
        return $this->hasParam('transport') ? $this->getParam('transport') : self::DEFAULT_TRANSPORT;
    }

    /**
     * @param array|string $transport
     *
     * @return $this
     */
    public function setTransport($transport)
    {
        return $this->setParam('transport', $transport);
    }

    /**
     * @return bool
     */
    public function hasCompression()
    {
        return (bool) $this->hasParam('compression') ? $this->getParam('compression') : self::DEFAULT_COMPRESSION;
    }

    /**
     * @param bool $compression
     *
     * @return $this
     */
    public function setCompression($compression = null)
    {
        return $this->setParam('compression', $compression);
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->hasParam('path') ? $this->getParam('path') : '';
    }

    /**
     * @param string $path
     *
     * @return $this
     */
    public function setPath($path)
    {
        return $this->setParam('path', $path);
    }

    /**
     * @param int $timeout Timeout in seconds
     *
     * @return $this
     */
    public function setTimeout($timeout)
    {
        return $this->setParam('timeout', $timeout);
    }

    /**
     * @return int Connection timeout in seconds
     */
    public function getTimeout()
    {
        return (int) $this->hasParam('timeout') ? $this->getParam('timeout') : self::TIMEOUT;
    }

    /**
     * Number of seconds after a connection timeout occurs for every request during the connection phase.
     * Use a small value if you need a fast fail in case of dead, unresponsive or unreachable servers (~5 sec).
     *
     * Set to zero to switch to the default built-in connection timeout (300 seconds in curl).
     *
     * @see http://curl.haxx.se/libcurl/c/CURLOPT_CONNECTTIMEOUT.html
     *
     * @param int $timeout Connect timeout in seconds
     *
     * @return $this
     */
    public function setConnectTimeout($timeout)
    {
        return $this->setParam('connectTimeout', $timeout);
    }

    /**
     * @return int Connection timeout in seconds
     */
    public function getConnectTimeout()
    {
        return (int) $this->hasParam('connectTimeout') ? $this->getParam('connectTimeout') : self::CONNECT_TIMEOUT;
    }

    /**
     * Enables a connection.
     *
     * @param bool $enabled OPTIONAL (default = true)
     *
     * @return $this
     */
    public function setEnabled($enabled = true)
    {
        return $this->setParam('enabled', $enabled);
    }

    /**
     * @return bool True if enabled
     */
    public function isEnabled()
    {
        return (bool) $this->getParam('enabled');
    }

    /**
     * Returns an instance of the transport type.
     *
     * @throws InvalidException If invalid transport type
     *
     * @return AbstractTransport Transport object
     */
    public function getTransportObject(LoggerInterface $logger, bool $isRetryFeatureEnabled)
    {
        $transport = $this->getTransport();

        return AbstractTransport::create(transport: $transport, connection: $this, logger: $logger, isRetryFeatureEnabled: $isRetryFeatureEnabled);
    }

    /**
     * Get or create elasticsearch-php v9 Client instance.
     *
     * V9: This method provides access to the native elasticsearch-php v9 client.
     * Used for direct API method calls like $client->indices()->refresh().
     */
    public function getClient(): ElasticsearchClient
    {
        if (null !== $this->_client) {
            return $this->_client;
        }

        $hosts = [];
        $scheme = $this->hasParam('ssl') && $this->getParam('ssl') ? 'https' : 'http';
        $host = $this->getHost();
        $port = $this->getPort();
        $path = $this->getPath();

        if (\is_string($path) && '' !== $path && '/' !== $path[0]) {
            $path = '/'.$path;
        }

        $hostString = \sprintf('%s://%s:%d%s', $scheme, $host, $port, $path);
        $hosts[] = $hostString;

        $stack = HandlerStack::create();

        // When connecting to OpenSearch (which does not send the X-Elastic-Product header
        // required by elasticsearch-php v9), set bypass_product_check=true in connection params.
        if ($this->hasParam('bypass_product_check') && $this->getParam('bypass_product_check')) {
            $stack->push(Middleware::mapResponse(
                static function (ResponseInterface $response): ResponseInterface {
                    return $response->withHeader('X-Elastic-Product', 'Elasticsearch');
                }
            ));
        }

        // If a request counter is provided, increment it for every HTTP request so that
        // direct ES9 client calls are tracked just like legacy Client::request() calls.
        if (null !== $this->_requestCounter) {
            $requestCounter = $this->_requestCounter;
            $stack->push(static function (callable $handler) use ($requestCounter): callable {
                return static function (RequestInterface $request, array $options) use ($handler, $requestCounter) {
                    $requestCounter->incrementCount();

                    return $handler($request, $options);
                };
            });
        }
        $httpClient = new GuzzleClient(['handler' => $stack]);

        $builder = ClientBuilder::create()
            ->setHosts($hosts)
            ->setHttpClient($httpClient)
        ;

        if ($this->hasParam('username') && $this->hasParam('password')) {
            $builder->setBasicAuthentication(
                $this->getParam('username'),
                $this->getParam('password')
            );
        }

        if ($this->hasParam('api_key')) {
            $builder->setApiKey($this->getParam('api_key'));
        }

        $this->_client = $builder->build();

        return $this->_client;
    }

    /**
     * @return bool Returns true if connection is persistent. True by default
     */
    public function isPersistent()
    {
        return (bool) $this->hasParam('persistent') ? $this->getParam('persistent') : true;
    }

    /**
     * @return $this
     */
    public function setConfig(array $config)
    {
        return $this->setParam('config', $config);
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function addConfig($key, $value)
    {
        $this->_params['config'][$key] = $value;

        return $this;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasConfig($key)
    {
        $config = $this->getConfig();

        return isset($config[$key]);
    }

    /**
     * Returns a specific config key or the whole
     * config array if not set.
     *
     * @param string $key Config key
     *
     * @throws InvalidException
     *
     * @return array|bool|int|string|null Config value
     */
    public function getConfig($key = '')
    {
        $config = $this->getParam('config');
        if (empty($key)) {
            return $config;
        }

        if (!\array_key_exists($key, $config)) {
            throw new InvalidException('Config key is not set: '.$key);
        }

        return $config[$key];
    }

    /**
     * @param array|Connection $params Params to create a connection
     *
     * @throws InvalidException
     *
     * @return self
     */
    public static function create($params = [])
    {
        if (\is_array($params)) {
            return new static($params);
        }

        if ($params instanceof self) {
            return $params;
        }

        throw new InvalidException('Invalid data type');
    }

    /**
     * @return string|null User
     */
    public function getUsername()
    {
        return $this->hasParam('username') ? $this->getParam('username') : null;
    }

    /**
     * @return string|null Password
     */
    public function getPassword()
    {
        return $this->hasParam('password') ? $this->getParam('password') : null;
    }

    /**
     * @return string AuthType
     */
    public function getAuthType()
    {
        return $this->hasParam('auth_type') ? \strtolower($this->getParam('auth_type')) : null;
    }
}
