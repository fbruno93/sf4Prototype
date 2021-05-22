<?php

namespace App\Authentication\Api\Transformer;

use Symfony\Component\HttpFoundation\Request;

interface RequestTransformerInterface
{
    public function __construct(Request $request);
}
