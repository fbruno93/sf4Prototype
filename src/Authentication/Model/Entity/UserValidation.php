<?php

namespace App\Authentication\Model\Entity;

use App\Core\Model\Entity\User;
use App\Authentication\Model\Repository\UserValidationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserValidationRepository::class)
 */
class UserValidation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\OneToOne(targetEntity=User::class, inversedBy="validation", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private ?User $user;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private string $hash;

    public function __construct()
    {
        $this->user = null;
        $this->generateHash();
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function generateHash()
    {
        $this->hash = bin2hex(openssl_random_pseudo_bytes(64));
    }
}
