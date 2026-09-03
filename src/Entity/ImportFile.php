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

    #[ORM\ManyToOne(targetEntity: User::class, cascade: ['persist'], inversedBy: 'import_files')]
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

        /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->fileName,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize(string $serialized)
    {
        list (
            $this->id,
            $this->fileName,
        ) = unserialize($serialized, array('allowed_classes' => false));
    }
}
