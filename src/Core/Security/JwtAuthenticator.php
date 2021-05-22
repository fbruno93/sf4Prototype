<?php

namespace App\Core\Security;

use App\Core\Api\Response\InfoResponse;
use App\Core\Message;
use App\Core\Model\Entity\User;
use App\Core\Model\Repository\UserRepository;
use App\Core\Service\JwtService;

use Firebase\JWT\ExpiredException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class JwtAuthenticator extends AbstractGuardAuthenticator
{
    private UserRepository $userRepository;

    private JwtService $jwtService;

    public function __construct(UserRepository $userRepository, JwtService $jwtService)
    {
        $this->userRepository = $userRepository;
        $this->jwtService = $jwtService;
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        $data = new InfoResponse(Message::SECURITY_AUTH_REQUIRED, false);
        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function supports(Request $request): bool
    {
        return $request->headers->has('Authorization');
    }

    public function getCredentials(Request $request): string
    {
        return str_replace('Bearer ', '', $request->headers->get('Authorization'));
    }

    public function getUser($credentials, UserProviderInterface $userProvider): ?User
    {
        try {
            $jwt = $this->jwtService->decode($credentials);
        } catch (ExpiredException $e) {
            throw new AuthenticationException(Message::SECURITY_AUTH_TOKEN_EXPIRED);
        }

        $user = $this->userRepository->findOneBy(["email" => $jwt->getUser()]);

        if (null !== $user->getValidation()) {
            throw new AuthenticationException(Message::SECURITY_AUTH_WAITING_VALIDATION);
        }

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user): bool
    {
        try {
            $this->jwtService->decode($credentials);
        } catch (ExpiredException $e) {
            throw new AuthenticationException(Message::SECURITY_AUTH_TOKEN_EXPIRED);
        }

        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $data = new InfoResponse($exception->getMessage(), false);
        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
    {
        return null;
    }

    public function supportsRememberMe(): bool
    {
        return false;
    }
}
