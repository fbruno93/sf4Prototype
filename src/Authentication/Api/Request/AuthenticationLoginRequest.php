<?php

namespace App\Authentication\Api\Request;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(name="request")
 * @OA\Schema(
 *     required={"email", "password"},
 *     @OA\Property(property="email", type="string", description="user email"),
 *     @OA\Property(property="password", type="string", description="user password"),
 * )
 */
class AuthenticationLoginRequest extends AbstractJsonRequest
{
    /**
     * @Assert\NotBlank(message="PARAMETER_EMAIL_REQUIRED")
     * @Assert\Email(message="PAREMETER_EMAIL_NOT_VALID")
     */
    protected ?string $email;

    /**
     * @Assert\NotBlank(message="PARAMETER_PASSWORD_REQUIRED")
     * @Assert\Length(min="6", minMessage="PARAMETER_PASSWORD_MIN_6_CHAR", max=4096)
     */
    protected ?string $password;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->email = $this->get('email');
        $this->password = $this->get('password');
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
