<?php

namespace App\Core\Model;

interface ModelInterface
{
    public function load(...$parameters);
    public function save();
}
