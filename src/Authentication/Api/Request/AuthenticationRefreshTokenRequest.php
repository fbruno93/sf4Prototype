<?php

namespace App\Authentication\Api\Request;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;

/**
 * Class UserRefreshTokenRequest
 * @package App\Entity\Request
 *
 * @OA\Tag(name="request")
 * @OA\Schema(
 *     required={"email", "password", "refresh_token"},
 *     allOf={
 *          @OA\Schema(
 *              ref=@Model(type=AuthenticationLoginRequest::class, groups={})
 *          ),
 *          @OA\Schema(
 *              @OA\Property(property="refresh_token", type="string", description="must be true")
 *          )
 *     }
 * )
 */
class AuthenticationRefreshTokenRequest extends AuthenticationLoginRequest
{
    /**
     * @Assert\NotBlank(message="PARAMETER_REFRESH_TOKEN_REQUIRED")
     */
    private ?string $refreshToken;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->refreshToken = $this->get('refresh_token');
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }
}
