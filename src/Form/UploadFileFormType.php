<?php

namespace App\Form;

use App\Entity\FileImport;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;

class UploadFileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('file', VichFileType::class, [
            'label' => 'Upload File (CSV, JSON, XML, XLSX)',
            'required' => true,
            'attr' => [
                'accept' => '.csv,.json,.xml,.xlsx,.xls,.txt'
            ],
        ]);

        $builder->add('Upload', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FileImport::class,
        ]);
    }
}