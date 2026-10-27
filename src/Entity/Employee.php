<?php

namespace App\Entity;

use App\Repository\EmployeeRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[ORM\Entity(repositoryClass: EmployeeRepository::class)]
#[ORM\Table(name: '`employee`')]
class Employee
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 255)]
    public string $name;

    #[ORM\Column(length: 255)]
    public string $email;

    #[ORM\Column(length: 255)]
    public string $role;

    #[ORM\Column]
    public bool $isActive;

    #[ManyToOne(targetEntity: ImportFile::class)]
    #[JoinColumn(nullable: false)]
    public ImportFile $importFile;

    public function getId(): ?int
    {
        return $this->id;
    }
}
