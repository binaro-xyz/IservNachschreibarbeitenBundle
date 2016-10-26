<?php
// /src\IServ\ExerciseBundle\Form\Type\UploadType.php
namespace IServ\NachschreibarbeitenBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class UploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', FileType::class, array(
                'label' => _('Files'),
                'multiple' => true,
                'constraints' => array(
                    //new Constraints\Count(array('min' => 1)), // min=1 not working? :/
                    new Constraints\All(array(
                        'constraints' => array(
                            new Constraints\NotNull(array('message' => _('Please choose a file to upload.'))),
                        ),
                    )),
                )
            ))
            ->add('id', HiddenType::class)
            ->add('type', HiddenType::class)
            ->add('submit', SubmitType::class, array(
                'label' => _('Upload'),
                'buttonClass' => 'btn-success',
                'icon' => 'ok',
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'iserv_exercise_upload';
    }
}
