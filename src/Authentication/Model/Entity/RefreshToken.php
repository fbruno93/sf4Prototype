<?php

namespace App\Authentication\Model\Entity;

use App\Core\Model\Entity\User;
use App\Authentication\Repository\RefreshTokenRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RefreshTokenRepository::class)
 */
class RefreshToken
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private string $token;

    /**
     * @ORM\Column(type="datetime")
     */
    private DateTime $valid;

    public function __construct(int $userId)
    {
        $this->token = bin2hex(openssl_random_pseudo_bytes(64));
        $this->valid = (new DateTime())->modify("+1 sec");
        $this->id = $userId;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function isExpired(): bool
    {
        $now = new DateTime();
        dump($now, $this->valid);
        return $now > $this->valid;
    }

    public function getValid(): ?\DateTimeInterface
    {
        return $this->valid;
    }
}
