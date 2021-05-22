<?php

namespace App\Core\Model;

use DateTime;

class JwtPayload
{
    private string $user;

    private int $exp;

    public static function loadFromArray(array $payload): JwtPayload
    {
        $self = new self();
        $self->user = $payload['user'];
        $self->exp = $payload['exp'];

        return $self;
    }
    public function getUser(): string
    {
        return $this->user;
    }

    public function isExpired(): bool
    {
        return $this->exp <  (new DateTime())->getTimestamp();
    }
}
