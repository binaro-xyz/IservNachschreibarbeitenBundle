<?php
//  src/IServ/ExerciseBundle/Form/Type/TextFieldTaskType.php
namespace IServ\NachschreibarbeitenBundle\Form\Type;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\FormActionsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class TextFieldTaskType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('text', TextareaType::class, ['attr' => ['rows' => 16], 'constraints' => [new NotBlank()], 'required' => false])
            ->add('id', HiddenType::class)
            ->add('actions', FormActionsType::class)
        ;

        $builder->get('actions')->add('submit', SubmitType::class, [
            'label' => _('Submit'),
            'buttonClass' => 'btn-success',
            'icon' => 'ok',
        ]);

        /* @var $submission \IServ\NachschreibarbeitenBundle\Entity\ExerciseSubmission */
        $submission = $builder->getData();

        // Only show cancel in edit mode
        if ($submission->getId()) {
            $builder->get('actions')->add('cancel', SubmitType::class, [
                'label' => _('Cancel'),
                'buttonClass' => 'btn-default',
                'icon' => 'remove',
            ]);
        }
    }
}
