<?php

namespace Elastica\Exception;

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
     */
    public static function mapFrom(ResponseException $exception): ResponseException
    {
        $error = $exception->getResponse()->getFullError();

        if (!\is_array($error)) {
            return $exception;
        }

        $type = $error['root_cause'][0]['type'] ?? $error['type'] ?? null;

        if (self::ERROR_TYPE !== $type) {
            return $exception;
        }

        return new self($exception->getRequest(), $exception->getResponse());
    }
}
