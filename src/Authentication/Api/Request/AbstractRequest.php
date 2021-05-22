<?php

namespace App\Authentication\Api\Request;

use App\Authentication\Api\Transformer\RequestTransformerInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractRequest implements RequestTransformerInterface
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function get($key, $default = null)
    {
        return $this->request->get($key, $default);
    }
}
