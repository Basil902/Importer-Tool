<?php

namespace App\Entity;

use App\Enum\FileTypeEnum;
use App\Repository\FileImportRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[ORM\Entity(repositoryClass: FileImportRepository::class)]
#[Vich\Uploadable]
class FileImport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public int $id;

    #[Vich\UploadableField(mapping: 'file_imports', fileNameProperty: 'fileName')]
    public ?File $file = null;

    #[ORM\Column(length: 255)]
    public ?string $fileName = null;

    #[ORM\Column(length: 255, enumType: FileTypeEnum::class)]
    public FileTypeEnum $fileType;

    #[ORM\Column]
    public \DateTimeImmutable $uploadedAt;

    #[ORM\Column]
    public \DateTimeImmutable $updatedAt;

    #[ORM\Column(length: 255)]
    #[Assert\Choice(choices: ['PENDING', 'SUCCESS', 'ERROR'])]
    public string $status;

    public function setFile(?File $file = null): void
    {
        $this->file = $file;

        if ($file !== null) {
            $this->updatedAt = new \DateTimeImmutable();
            $this->uploadedAt = new \DateTimeImmutable();
        }
    }
}
