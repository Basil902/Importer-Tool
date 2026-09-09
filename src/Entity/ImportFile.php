<?php

namespace App\Entity;

use App\Enum\FileTypeEnum;
use App\Enum\ImportStatusEnum;
use App\Repository\ImportFileRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[ORM\Entity(repositoryClass: ImportFileRepository::class)]
#[Vich\Uploadable]
class ImportFile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public int $id;

    #[Vich\UploadableField(mapping: 'import_files', fileNameProperty: 'fileName')]
    public ?File $file = null;

    #[ORM\Column(length: 255)]
    public ?string $fileName = null;

    #[ORM\Column(length: 255, enumType: FileTypeEnum::class)]
    public FileTypeEnum $fileType;

    #[ORM\Column]
    public \DateTimeImmutable $uploadedAt;

    #[ORM\Column]
    public \DateTimeImmutable $updatedAt;

    #[ORM\Column(length: 255, enumType: ImportStatusEnum::class)]
    public ImportStatusEnum $status;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'importFiles')]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $owner;

    public function getOwner(): User
    {
        return $this->owner;
    }

    public function setOwner(User $user): self
    {
        $this->owner = $user;

        return $this;
    }

    public function setFile(?File $file = null): void
    {
        $this->file = $file;

        if ($file !== null) {
            $this->updatedAt = new \DateTimeImmutable();
            $this->uploadedAt = new \DateTimeImmutable();
        }
    }

    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'file_name' => $this->fileName,
        ];
    }

    public function __unserialize(array $serialized): void
    {
        $this->id = $serialized['id'];
        $this->fileName = $serialized['file_name'];
    }
}
