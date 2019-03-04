<?php

namespace App\Http\JsonApi;

use Illuminate\Http\Request;

class MediaTypeGuard
{
    protected $contentType;

    public function __construct(string $contentType, string $acceptHeaderPolicy)
    {
        $this->contentType = $contentType;
        $this->acceptHeaderPolicy = $acceptHeaderPolicy;
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    public function getAcceptHeaderPolicy(): string
    {
        return $this->acceptHeaderPolicy;
    }

    public function validateExistingContentType(Request $request): bool
    {
        return str_is($this->getContentType(), $request->header('Accept')) || str_is('', $request->header('Accept'));
    }

    public function clientRequestMustHaveContentTypeHeader(Request $request)
    {
        $method = $request->method();
        return $method === 'POST' || $method === 'PATCH';
    }

    public function contentTypeIsValid(string $contentType): bool
    {
        return str_is($this->getContentType(), $contentType);
    }

    public function hasCorrectHeadersForData(Request $request): bool
    {
        if ($this->clientRequestMustHaveContentTypeHeader($request)) {
            return $request->hasHeader('Content-Type') && $this->contentTypeIsValid($request->header('Content-Type'));
        }
        return true;
    }

    public function hasCorrectlySetAcceptHeader(Request $request): bool
    {
        if ($this->acceptHeaderPolicy === 'ignore') {
            return true;
        }

        $accept = $request->header('Accept');
        if (empty($accept)) {
            return $this->acceptHeaderPolicy !== 'require';
        }

        if ('*/*' === $accept) {
            return true;
        }

        return substr_count($accept, $this->getContentType()) > substr_count($accept, $this->getContentType() . ';');
    }
}
