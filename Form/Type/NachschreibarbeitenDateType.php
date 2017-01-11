<?php

namespace binaro\NachschreibarbeitenBundle\Form\Type;


use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NachschreibarbeitenDateType extends AbstractType {

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(['class', 'confirm', 'options']);

        $resolver->setDefaults(array(
            'confirm' => false,
            'class' => 'IServNachschreibarbeitenBundle:NachschreibarbeitenDate',
            'select2' => true,
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('d')->where('d.date >= CURRENT_DATE()')->orderBy('d.date', 'ASC')->setMaxResults(10);
            },
            'choice_label' => function($date) {
                setlocale(LC_TIME, 'de_DE');
                return strftime('%A, %d. %B %Y', $date->getDate()->getTimestamp()) . ' ' . strftime('%H:%M', $date->getTime()->getTimestamp());
            }
        ));
    }

    public function getParent()
    {
        return EntityType::class;
    }

    public function getBlockPrefix()
    {
        return 'nachschreibarbeiten_date_select';
    }
}
