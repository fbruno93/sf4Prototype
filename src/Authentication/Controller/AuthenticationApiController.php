<?php

namespace App\Authentication\Controller;

use App\Authentication\Api\Request\AuthenticationLoginRequest;
use App\Authentication\Api\Request\AuthenticationRefreshTokenRequest;
use App\Authentication\Api\Request\AuthenticationRegisterRequest;
use App\Authentication\Message;
use App\Authentication\Model\Entity\RefreshToken;
use App\Authentication\Model\Repository\RefreshTokenRepository;
use App\Authentication\Model\Repository\UserValidationRepository;
use App\Authentication\Notification\UserCreateNotification;
use App\Core\Api\Response\InfoResponse;
use App\Core\Exception\ApiException;
use App\Core\Exception\UserAlreadyValidateException;
use App\Core\Service\JwtService;
use App\Core\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @OA\Tag(name="auth")
 * @Security()
 *
 * @Route("/auth")
 */
class AuthenticationApiController extends AbstractController
{
    /**
     * @OA\Post(
     * @OA\RequestBody(
     *     @Model(type=AuthenticationRegisterRequest::class, groups={})
     * ),
     *
     * @OA\Response(
     *     response=201,
     *     description="success response",
     *     @Model(type=InfoResponse::class, groups={})
     * ),
     *
     * @OA\Response(
     *     response="400",
     *     description="User already exists but user is not validate",
     *     @Model(type=ApiException::class, groups={})
     * ),
     *
     * @OA\Response(
     *     response="409",
     *     description="User already exists",
     *     @Model(type=ApiException::class, groups={})
     * )
     * )
     *
     * @Route("/register", name="auth_register", methods={"POST"})
     *
     * @param AuthenticationRegisterRequest $request
     * @param UserService $userService
     * @param MessageBusInterface $bus
     *
     * @return JsonResponse
     *
     * @throws ApiException
     */
    public function register(AuthenticationRegisterRequest $request,
                             UserService $userService,
                             MessageBusInterface $bus): Response
    {
        $response = new InfoResponse(Message::AUTH_REGISTER_SUCCESS);
        $httpCode = Response::HTTP_OK;

        try {
            $user = $userService->checkRegister($request->getEmail(), $request->getPassword());

            if (null !== $user->getValidation()) {
                $response = new InfoResponse(Message::AUTH_REGISTER_RESEND_CONFIRM_EMAIL, false);
                $httpCode = Response::HTTP_BAD_REQUEST;
            }

            $bus->dispatch(new UserCreateNotification($user->getId()));

        } catch (UserAlreadyValidateException $e) {
            throw new ApiException(Message::AUTH_REGISTER_USER_EXISTS, Response::HTTP_CONFLICT);
        }

        return $this->json($response, $httpCode);
    }

    /**
     * @OA\RequestBody(
     *     @Model(type=AuthenticationLoginRequest::class, groups={})
     * ),
     *
     * @OA\Response(
     *     response=200,
     *     description="success response",
     *     @OA\JsonContent(
     *         @OA\Property(property="token", type="string", description="The authentication token", default=true),
     *         @OA\Property(property="refresh_token", type="string",
     *              description="Then refreshing authentication token. Use it to refresh authentication token"
     *         )
     *     )
     * )
     *
     * @OA\Response(
     *     response="400",
     *     description="error response",
     *     @Model(type=ApiException::class, groups={})
     * )
     *
     * @Route("/login", name="auth_login", methods={"POST"})
     *
     * @param AuthenticationLoginRequest $request
     * @param UserService $userService
     * @param JwtService $jwtService
     * @param EntityManagerInterface $entityManager
     *
     * @return JsonResponse
     *
     * @throws ApiException
     */
    public function login(AuthenticationLoginRequest $request,
                          UserService $userService,
                          JwtService $jwtService,
                          EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $userService->checkLogin($request->getEmail(), $request->getPassword());

        if (is_null($user)) {
            throw new ApiException(Message::AUTH_LOGIN_WRONG_CREDENTIALS, Response::HTTP_BAD_REQUEST);
        }

        $jwt = $jwtService->encode([
            "user" => $user->getUsername()
        ]);

        $refreshToken = new RefreshToken($user->getId());

        $entityManager->persist($refreshToken);
        $entityManager->flush();

        return $this->json([
            'token' => $jwt,
            'refresh_token' => $refreshToken->getToken()
        ], Response::HTTP_OK);
    }

    /**
     * @OA\RequestBody(
     *     @Model(type=AuthenticationRefreshTokenRequest::class, groups={})
     * ),
     *
     * @OA\Response(
     *     response=201,
     *     description="success response",
     *     @OA\JsonContent(
     *         @OA\Property(property="token", type="string", description="The newer authentication token"),
     *         @OA\Property(property="refresh_token", type="string", description="The refresh token")
     *     )
     * )
     *
     * @OA\Response(
     *     response="400",
     *     description="error response",
     *     @Model(type=ApiException::class, groups={})
     * )
     *
     * @Route("/refresh", name="auth_refresh", methods={"POST"})
     *
     * @param AuthenticationRefreshTokenRequest $request
     * @param UserService $userService
     * @param RefreshTokenRepository $refreshTokenRepository
     * @param JwtService $jwtService
     *
     * @return JsonResponse
     *
     * @throws ApiException
     */
    public function refreshToken(AuthenticationRefreshTokenRequest $request,
                                 UserService $userService,
                                 RefreshTokenRepository $refreshTokenRepository,
                                 JwtService $jwtService): JsonResponse
    {
        $refreshToken = $refreshTokenRepository->findValidToken($request->getRefreshToken());

        if (null === $refreshToken) {
            throw new ApiException(Message::AUTH_REFRESH_TOKEN_NOT_FOUND, Response::HTTP_BAD_REQUEST);
        }

        if ($refreshToken->isExpired()) {
            throw new ApiException(Message::AUTH_REFRESH_TOKEN_EXPIRED, Response::HTTP_BAD_REQUEST);
        }

        $user = $userService->checkLogin($request->getEmail(), $request->getPassword());

        if (null === $user) {
            throw new ApiException(Message::AUTH_LOGIN_WRONG_CREDENTIALS, Response::HTTP_BAD_REQUEST);
        }

        $jwt = $jwtService->encode([
            "user" => $user->getUsername()
        ]);

        return $this->json([
            'token' => $jwt,
            'refresh_token' => $request->getRefreshToken()
        ], Response::HTTP_OK, []);
    }

    /**
     * @Route("/validation/{hash}", name="user_validation", methods={"GET"})
     *
     * @param UserValidationRepository $userValidationRepository
     * @param EntityManagerInterface $entityManager
     * @param string|null $hash
     *
     * @return JsonResponse
     *
     * @throws ApiException
     */
    public function userValidation(UserValidationRepository $userValidationRepository,
                                   EntityManagerInterface $entityManager,
                                   string $hash = null): JsonResponse
    {
        $validation = $userValidationRepository->findOneBy([
            'hash' => $hash
        ]);

        if (null === $validation) {
            throw new ApiException(Message::AUTH_VALIDATION_FAILED, Response::HTTP_BAD_REQUEST);
        }

        $entityManager->remove($validation);
        $entityManager->flush();

        return $this->json(new InfoResponse(Message::AUTH_VALIDATION_SUCCESS), Response::HTTP_OK);
    }
}
