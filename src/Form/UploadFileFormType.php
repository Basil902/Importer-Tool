<?php

namespace App\Form;

use App\Entity\ImportFile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Vich\UploaderBundle\Form\Type\VichFileType;

class UploadFileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('file', VichFileType::class, [
            'label' => 'Upload File (CSV, JSON, XML, XLSX)',
            'required' => true,
            'error_bubbling' => true,
            'attr' => [
                'accept' => '.csv,.json,.xml,.xlsx,.xls,.txt'
            ],
            'constraints' => [
                new File(
                    extensions: ['csv' => ["text/plain", "text/csv", "application/csv", "text/x-comma-separated-values", "text/x-csv"], 'json', 'xml', 'xlsx', 'txt'],
                    extensionsMessage: 'File type {{ extension }} is not allowed.',
                ),
            ],
        ]);

        $builder->add('Upload', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ImportFile::class,
        ]);
    }
}