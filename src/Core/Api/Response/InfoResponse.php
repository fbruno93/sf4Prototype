<?php

namespace App\Core\Api\Response;

use JsonSerializable;

use OpenApi\Annotations as OA;

/**
 * @OA\Tag(name="response")
 * @OA\Schema(
 *      @OA\Property(property="status", type="boolean", description="must be true", default=true),
 *      @OA\Property(property="message", type="string", description="a cool message")
 * )
 */
class InfoResponse implements JsonSerializable
{
    private bool $status;

    private string $message;

    public function __construct(string $message, bool $status = true)
    {
        $this->status = $status;
        $this->message = $message;
    }

    public function jsonSerialize(): array
    {
        return [
            'status' => $this->status,
            'message' => $this->message
        ];
    }
}