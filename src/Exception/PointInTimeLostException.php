<?php

namespace Elastica\Exception;

use Elastica\Response;

/**
 * The Point-in-Time used by a search no longer exists (expired keep-alive or closed).
 *
 * Only thrown by the Point-in-Time helpers, never by the transports: an expired scroll
 * produces the same error type, so existing callers must keep getting a plain ResponseException.
 */
class PointInTimeLostException extends ResponseException
{
    private const ERROR_TYPE = 'search_context_missing_exception';

    /**
     * Returns a PointInTimeLostException when the given exception was caused by a lost search context,
     * the given exception itself otherwise.
     *
     * The search context can be reported in the top-level error type, in any root cause, or in the reason of
     * any shard failure (an HTTP 200 partial response has no top-level error).
     */
    public static function mapFrom(ResponseException $exception): ResponseException
    {
        $response = $exception->getResponse();

        if (!self::hasLostSearchContext($response)) {
            return $exception;
        }

        $mapped = new self($exception->getRequest(), $response);
        $mapped->message = $exception->getMessage();

        return $mapped;
    }

    private static function hasLostSearchContext(Response $response): bool
    {
        $types = [];
        $error = $response->getFullError();

        if (\is_array($error)) {
            $types = \array_column($error['root_cause'] ?? [], 'type');
            $types[] = $error['type'] ?? null;
        }

        foreach ($response->getData()['_shards']['failures'] ?? [] as $failure) {
            $types[] = $failure['reason']['type'] ?? null;
        }

        return \in_array(self::ERROR_TYPE, $types, true);
    }
}
