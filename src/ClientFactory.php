<?php

declare(strict_types=1);

namespace Elastica;

use Elastica\Cluster\ClusterConfiguration;
use Psr\Log\LoggerInterface;

class ClientFactory
{
    private ServerConfiguration $serverConfiguration;

    private RequestCounterInterface $requestCounter;

    private LoggerInterface $lazyLogger;

    private bool $isRequestLoggingEnabled;

    private bool $isRetryFeatureEnabled;

    private int $slowRequestThresholdMs;

    private int $largeResponseThresholdBytes;

    public function __construct(
        ServerConfiguration $serverConfiguration,
        RequestCounterInterface $requestCounter,
        LoggerInterface $lazyLogger,
        bool $isRequestLoggingEnabled,
        bool $isRetryFeatureEnabled,
        int $slowRequestThresholdMs = Client::DEFAULT_SLOW_REQUEST_THRESHOLD_IN_MS,
        int $largeResponseThresholdBytes = Client::DEFAULT_LARGE_RESPONSE_THRESHOLD_IN_BYTES
    ) {
        $this->serverConfiguration = $serverConfiguration;
        $this->requestCounter = $requestCounter;
        $this->lazyLogger = $lazyLogger;
        $this->isRequestLoggingEnabled = $isRequestLoggingEnabled;
        $this->isRetryFeatureEnabled = $isRetryFeatureEnabled;
        $this->slowRequestThresholdMs = $slowRequestThresholdMs;
        $this->largeResponseThresholdBytes = $largeResponseThresholdBytes;
    }

    public function createClientForCluster(
        ClusterConfiguration $clusterConfiguration,
        callable $isBrandIndependentIndexByName,
        bool $withRequestCounter = true,
        int $loggingMode = Client::LOG_DISABLED
    ): Client {
        $serverConfiguration = $this->serverConfiguration->getConfiguration($clusterConfiguration);

        $config = [
            'servers' => [$serverConfiguration],
            'apiVersion' => $clusterConfiguration->getVersion()->getValue(),
            'documentTypeResolver' => static fn (string $indexName): string => $isBrandIndependentIndexByName($indexName) ? Type::DOC : Type::DEFAULT,
        ];

        $client = new Client(
            $config,
            null,
            null,
            $withRequestCounter ? $this->requestCounter : null,
            $this->isRetryFeatureEnabled,
            $this->slowRequestThresholdMs,
            $this->largeResponseThresholdBytes
        );

        $client->setLoggingMode($loggingMode);

        if ($this->isRequestLoggingEnabled) {
            $client->setLogger($this->lazyLogger);
        }

        return $client;
    }
}
