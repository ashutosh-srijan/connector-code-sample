<?php

/**
 * Class Client.
 *
 * Default API client implementation for Apigee Edge.
 */
class BaseConnectorClient implements ClientInterface
{
    public const CONFIG_USER_AGENT_PREFIX = 'user_agent_prefix';

    public const CONFIG_HTTP_CLIENT_BUILDER = 'http_client_builder';

    public const CONFIG_URI_FACTORY = 'uri_factory';

    public const CONFIG_REQUEST_FACTORY = 'request_factory';

    public const CONFIG_JOURNAL = 'journal';

    public const CONFIG_ERROR_FORMATTER = 'error_formatter';

    public const CONFIG_RETRY_PLUGIN_CONFIG = 'retry_plugin_config';

    /** @var \Http\Message\UriFactory */
    private $uriFactory;

    /** @var string|null */
    private $userAgentPrefix;

    /**
     * Apigee Edge endpoint.
     *
     * @var string
     */
    private $endpoint;

    /** @var \Http\Message\Authentication */
    private $authentication;

    /**
     * Http client builder.
     *
     * @var \Apigee\Edge\HttpClient\Utility\BuilderInterface
     */
    private $httpClientBuilder;

    /** @var \Apigee\Edge\HttpClient\Utility\JournalInterface */
    private $journal;

    /** @var bool */
    private $httpClientNeedsBuild = true;

    /**
     * @var \Http\Message\RequestFactory
     */
    private $requestFactory;

    /**
     * @var \Http\Message\Formatter|null
     */
    private $errorFormatter;

    /** @var array|null */
    private $retryPluginConfig;

    /**
     * Client constructor.
     */
    public function __construct(
        Authentication $authentication,
        string $endpoint = null,
        array $options = []
    ) {
        $this->authentication = $authentication;
        $this->endpoint = $endpoint ?: self::DEFAULT_ENDPOINT;
        $this->resolveConfiguration($options);
    }

    /**
     * @inheritdoc
     */
    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * @inheritdoc
     */
    public function get($uri, array $headers = []): ResponseInterface
    {
        return $this->send('GET', $uri, $headers, null);
    }

    /**
     * @inheritdoc
     */
    public function head($uri, array $headers = []): ResponseInterface
    {
        return $this->send('HEAD', $uri, $headers, null);
    }

    /**
     * @inheritdoc
     */
    public function post($uri, $body = null, array $headers = []): ResponseInterface
    {
        if (!isset($headers['Content-Type'])) {
            $headers['Content-Type'] = 'application/json';
        }

        return $this->send('POST', $uri, $headers, $body);
    }

    /**
     * @inheritdoc
     */
    public function put($uri, $body = null, array $headers = []): ResponseInterface
    {
        if (!isset($headers['Content-Type'])) {
            $headers['Content-Type'] = 'application/json';
        }

        return $this->send('PUT', $uri, $headers, $body);
    }

    /**
     * @inheritdoc
     */
    public function delete($uri, $body = null, array $headers = []): ResponseInterface
    {
        return $this->send('DELETE', $uri, $headers, $body);
    }

    /**
     * @inheritdoc
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        return $this->getHttpClient()->sendRequest($request);
    }


    /**
     * Returns default HTTP headers sent by the underlying HTTP client.
     *
     * @return array
     */
    protected function getDefaultHeaders(): array
    {
        return [
            'User-Agent' => $this->getUserAgent(),
            'Accept' => 'application/json; charset=utf-8',
        ];
    }

    /**
     * @inheritdoc
     *
     * @throws \Http\Client\Exception
     */
    private function send($method, $uri, array $headers = [], $body = null): ResponseInterface
    {
        return $this->sendRequest($this->requestFactory->createRequest($method, $uri, $headers, $body));
    }

    /**
     * Returns Apigee Edge endpoint as an URI.
     *
     * @return \Psr\Http\Message\UriInterface
     */
    private function getBaseUri(): UriInterface
    {
        return $this->uriFactory->createUri($this->getEndpoint());
    }
}
