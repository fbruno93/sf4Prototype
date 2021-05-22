<?php

namespace App\Core\Service;

use App\Authentication\Model\Entity\UserValidation;
use App\Core\Exception\UserAlreadyValidateException;
use App\Core\Model\Entity\User;
use App\Core\Model\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class UserService
 * @package App\Core\Service
 *
 * Service to manage User entity
 */
class UserService
{
    private EntityManagerInterface $entityManager;

    private UserPasswordEncoderInterface $passwordEncoder;

    private UserRepository $userRepository;

    public function __construct(EntityManagerInterface $entityManager,
                                UserPasswordEncoderInterface $passwordEncoder,
                                UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @param string $email
     * @param string $password
     *
     * @return User
     *
     * @throws UserAlreadyValidateException
     */
    public function checkRegister(string $email, string $password): User
    {
        $user = $this->checkLogin($email, $password);

        if (null !== $user) {
            if (false === $this->regenerateValidationHash($user)) {
                throw new UserAlreadyValidateException();
            }
            return $user;
        }

        return $this->register($email, $password);
    }

    /**
     * Verify user credentials
     *
     * @param string $email
     * @param string $password
     *
     * @return User|null
     */
    public function checkLogin(string $email, string $password): ?User
    {
        $user = $this->userRepository->findOneBy(["email" => $email]);

        if (!$user || !$this->passwordEncoder->isPasswordValid($user, $password)) {
            return null;
        }

        return $user;
    }

    /**
     * Regenerate a user validation hash
     *
     * @param User $user
     *
     * @return bool
     */
    public function regenerateValidationHash(User $user): bool
    {
        if (null === $user->getValidation()) {
            return false;
        }

        $user->getValidation()->generateHash();

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return true;
    }

    /**
     * Save new user
     *
     * @param string $email
     * @param string $password
     *
     * @return User
     */
    private function register(string $email, string $password): User
    {
        $user = new User();

        $encodedPassword = $this->passwordEncoder->encodePassword($user, $password);
        $user->setPassword($encodedPassword);
        $user->setEmail($email);
        $user->setValidation(new UserValidation());

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
