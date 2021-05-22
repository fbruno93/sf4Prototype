<?php

namespace App\Authentication\Api\Request;

use Symfony\Component\HttpFoundation\Request;

abstract class AbstractJsonRequest extends AbstractRequest
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function getContent()
    {
        return json_decode($this->request->getContent(), true);
    }

    public function get($key, $default = null)
    {
        if (isset($this->getContent()[$key])) {
            return $this->getContent()[$key];
        }

        return parent::get($key, $default);
    }
}
