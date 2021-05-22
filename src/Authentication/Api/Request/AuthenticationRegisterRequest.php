<?php

namespace App\Authentication\Api\Request;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;

/**
 * Class UserRegisterRequest
 * @package App\Entity\Request
 *
 * @OA\Tag(name="request")
 * @OA\Schema(
 *     required={"email", "password", "agreeTerms"},
 *     allOf={
 *          @OA\Schema(
 *              ref=@Model(type=AuthenticationLoginRequest::class, groups={})
 *          ),
 *          @OA\Schema(
 *              @OA\Property(property="agree_terms", type="string", description="must be true")
 *          )
 *     }
 * )
 */
class AuthenticationRegisterRequest extends AuthenticationLoginRequest
{
    /**
     * @Assert\NotBlank(message="PARAMETER_AGREE_TERMS_REQUIRED")
     * @Assert\IsTrue()
     */
    private bool $agreeTerms;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->email = $this->get('email');
        $this->password = $this->get('password');
        $this->agreeTerms = $this->get('agree_terms');
    }

    /** @noinspection PhpUnused */
    public function getAgreeTerms() : bool
    {
        return $this->agreeTerms;
    }
}
