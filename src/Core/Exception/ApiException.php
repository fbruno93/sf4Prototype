<?php

namespace App\Core\Exception;

use Exception;
use JsonSerializable;

use OpenApi\Annotations as OA;

/**
 * @OA\Tag(name="error")
 * @OA\Schema(
 *     @OA\Property(property="status", type="boolean", description="must be false", default=false),
 *     @OA\Property(property="message", type="string", description="A constante discribing error")
 * )
 */
class ApiException extends Exception implements JsonSerializable
{
    private bool $status = false;

    public function jsonSerialize(): array
    {
        return [
            'status' => $this->status,
            'message' => $this->getMessage(),
        ];
    }
}
