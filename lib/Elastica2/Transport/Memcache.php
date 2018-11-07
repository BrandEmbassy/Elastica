<?php
namespace Elastica2\Transport;

use Elastica2\Exception\Connection\MemcacheException;
use Elastica2\Exception\InvalidException;
use Elastica2\Exception\PartialShardFailureException;
use Elastica2\Exception\ResponseException;
use Elastica2\JSON;
use Elastica2\Request;
use Elastica2\Response;

/**
 * Elastica2 Memcache Transport object.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @deprecated The memcached transport is deprecated as of ES 1.5, and will be removed in ES 2.0
 */
class Memcache extends AbstractTransport
{
    const MAX_KEY_LENGTH = 250;

    /**
     * Makes calls to the elasticsearch server.
     *
     * @param \Elastica2\Request $request
     * @param array             $params  Host, Port, ...
     *
     * @throws \Elastica2\Exception\ResponseException
     * @throws \Elastica2\Exception\InvalidException
     *
     * @return \Elastica2\Response Response object
     */
    public function exec(Request $request, array $params)
    {
        $memcache = new \Memcache();
        $memcache->connect($this->getConnection()->getHost(), $this->getConnection()->getPort());

        $data = $request->getData();

        $content = '';

        if (!empty($data) || '0' === $data) {
            if (is_array($data)) {
                $content = JSON::stringify($data);
            } else {
                $content = $data;
            }

            // Escaping of / not necessary. Causes problems in base64 encoding of files
            $content = str_replace('\/', '/', $content);
        }

        $responseString = '';

        $start = microtime(true);

        switch ($request->getMethod()) {
            case Request::POST:
            case Request::PUT:
                $key = $request->getPath();
                $this->_checkKeyLength($key);
                $memcache->set($key, $content);
                break;
            case Request::GET:
                $key = $request->getPath().'?source='.$content;
                $this->_checkKeyLength($key);
                $responseString = $memcache->get($key);
                break;
            case Request::DELETE:
                $key = $request->getPath().'?source='.$content;
                $this->_checkKeyLength($key);
                $responseString = $memcache->delete($key);
                break;
            default:
            case Request::HEAD:
                throw new InvalidException('Method '.$request->getMethod().' is not supported in memcache transport');
        }

        $end = microtime(true);

        $response = new Response($responseString);
        $response->setQueryTime($end - $start);

        if ($response->hasError()) {
            throw new ResponseException($request, $response);
        }

        if ($response->hasFailedShards()) {
            throw new PartialShardFailureException($request, $response);
        }

        return $response;
    }

    /**
     * Check if key that will be used dont exceed 250 symbols.
     *
     * @param string $key
     *
     * @throws Elastica\Exception\Connection\MemcacheException If key is too long
     */
    private function _checkKeyLength($key)
    {
        if (strlen($key) >= self::MAX_KEY_LENGTH) {
            throw new MemcacheException('Memcache key is too long');
        }
    }
}
