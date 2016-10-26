<?php
// /src\IServ\ExerciseBundle\Form\Type\SelectType.php
namespace IServ\NachschreibarbeitenBundle\Form\Type;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\FormActionsType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SelectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Template for entity form field was modified! Not all attributes are functional anymore.
        $builder
            ->add('elements', EntityType::class, array(
                'class' => $options['class'],
                'choices' => $options['elements'],
                'choices_as_values' => true,
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'label' => false,
                'disabled' => $options['confirm'],
                'hidden' => $options['confirm'],
                'hiddenLabel' => !$options['confirm'],
                'select2' => false,
                'attr' => ['widget_col' => 12],
            ))
            ->add('actions', FormActionsType::class);

        $builder->get('actions')->add('executeBtn', SubmitType::class, array(
            'label' => isset($options['options']['buttonLabel']) ? $options['options']['buttonLabel'] : _('Delete'),
            'icon' => isset($options['options']['buttonIcon']) ? $options['options']['buttonIcon'] : 'trash',
            'buttonClass' => isset($options['options']['buttonClass']) ? $options['options']['buttonClass'] : 'btn-default',
        ));

        if ($options['confirm']) {
            $builder->get('actions')->add('cancelBtn', SubmitType::class, array(
                'label' => _('No'),
                'icon' => 'remove',
                'buttonClass' => 'btn-default',
            ));
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(['class', 'confirm', 'options']);
        $resolver->setRequired(['elements']);

        $resolver->setDefaults(array(
            'confirm' => false,
            'options' => [],
            'class' => 'IServExerciseBundle:ExerciseSubmission',
            'data_class' => null,
            'validation_groups' => isset($options['options']['validation']) ? $options['options']['validation'] : true,
        ));
    }

    public function getBlockPrefix()
    {
        return 'iserv_exercise_element';
    }
}
