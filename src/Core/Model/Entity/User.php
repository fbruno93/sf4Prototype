<?php

namespace App\Core\Model\Entity;

use App\Authentication\Model\Entity\UserValidation;
use App\Core\Model\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @Groups({"user_profile"})
     */
    protected int $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     *
     * @Groups({"user_profile"})
     */
    private string $email;

    /**
     * @ORM\Column(type="json")
     */
    private array $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private string $password;

    /**
     * @ORM\OneToOne(targetEntity=UserValidation::class, mappedBy="user", cascade={"persist"})
     */
    private ?UserValidation $validation;

    /**
     * @ORM\Column(type="integer")
     */
    private int $cityId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getValidation(): ?UserValidation
    {
        return $this->validation;
    }

    public function setValidation(UserValidation $validation): self
    {
        // set the owning side of the relation if necessary
        if ($validation->getUser() !== $this) {
            $validation->setUser($this);
        }

        $this->validation = $validation;

        return $this;
    }

    public function getUsername(): string
    {
        return $this->email;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials()
    {
    }

    /**
     * @return int
     */
    public function getCityId(): int
    {
        return $this->cityId;
    }
}
