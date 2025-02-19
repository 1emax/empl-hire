<?php

namespace App\Entity;

use App\Repository\EmployeeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: EmployeeRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Employee
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Имя не может быть пустым')]
    #[Groups(['create', 'update', 'read'])]
    private string $firstName;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Фамилия не может быть пустой')]
    #[Groups(['create', 'update', 'read'])]
    private string $lastName;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Email не может быть пустым')]
    #[Assert\Email(message: 'Некорректный формат email')]
    #[Groups(['create', 'update', 'read'])]
    private string $email;

    #[ORM\Column(type: 'date')]
    #[Assert\NotBlank(message: 'Дата зачисления не может быть пустой')]
    #[Assert\GreaterThanOrEqual('today', message: 'Дата зачисления не может быть в прошлом')]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
    #[Groups(['create', 'update', 'read'])]
    private \DateTimeInterface $hireDate;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Assert\NotBlank(message: 'Зарплата не может быть пустой')]
    #[Assert\GreaterThanOrEqual(100, message: 'Зарплата должна быть не менее 100')]
    #[Groups(['create', 'update', 'read'])]
    private string $salary;

    #[ORM\Column]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s'])]
    #[Groups(['read'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s'])]
    #[Groups(['read'])]
    private \DateTimeImmutable $updatedAt;

    #[ORM\PrePersist]
    #[Ignore]
    /**
     *  Автоматически устанавливает значения createdAt и updatedAt перед созданием сущности.
     *  Doctrine вызывает этот метод перед выполнением операции persist.
     */
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    #[Ignore]
    /**
     * Автоматически обновляет значение updatedAt перед каждым обновлением сущности.
     * Doctrine вызывает этот метод перед выполнением операции update.
     */    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getHireDate(): \DateTimeInterface
    {
        return $this->hireDate;
    }

    public function setHireDate(\DateTimeInterface $hireDate): static
    {
        $this->hireDate = $hireDate;
        return $this;
    }

    public function getSalary(): string
    {
        return $this->salary;
    }

    public function setSalary(string $salary): static
    {
        $this->salary = $salary;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
