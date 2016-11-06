<?php
//  src/IServ/NachschreibarbeitenBundle/Form/Type/ChangeOwnerType.php
namespace IServ\NachschreibarbeitenBundle\Form\Type;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\FormActionsType;
use IServ\CoreBundle\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ChangeOwnerType extends  AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('owner', EntityType::class, array(
                'placeholder' => _('Please choose...'),
                'class' => User::class,
                'label' => _('New owner'),
                'choices' => $options['choices'],
                'constraints' => new NotBlank(),
            ))
            ->add('actions', FormActionsType::class)
        ;

        $builder
            ->get('actions')
            ->add('submit', SubmitType::class, [
                'label' => _('Change'),
                'buttonClass' => 'btn-warning',
                'icon' => 'ok',
            ])
            ->add('cancel', SubmitType::class, [
                'label' => _('Cancel'),
                'buttonClass' => 'btn-default',
                'icon' => 'remove',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('choices');
        $resolver->setAllowedTypes('choices', array('array'));
    }

}
