<?php
// src/IServ/NachschreibarbeitenBundle/Controller/ExerciseController.php
namespace IServ\NachschreibarbeitenBundle\Controller;

use Doctrine\ORM\EntityRepository;
use IServ\CoreBundle\Controller\PageController;
use IServ\CoreBundle\Entity\User;
use IServ\CoreBundle\Form\Type\UserType;
use IServ\CoreBundle\IServCoreBundle;
use IServ\CrudBundle\Mapper\FormMapper;
use IServ\NachschreibarbeitenBundle\Entity\NachschreibarbeitenDate;
use IServ\NachschreibarbeitenBundle\Entity\NachschreibarbeitenEntry;
use IServ\NachschreibarbeitenBundle\Form\Type\NachschreibarbeitenDateType;
use IServ\NachschreibarbeitenBundle\Security\Privilege;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Debug\Exception\ContextErrorException;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("nachschreibarbeiten")
 */
class NachschreibarbeitenController extends PageController {
    /**
     * @Route("", name="nachschreibarbeiten_index")
     * @Template()
     * @return array|RedirectResponse
     */
    public function indexAction(Request $request, $path) {
        if($this->isGranted(Privilege::ACCESS_NACHSCHREIBARBEITEN) || $this->isGranted(Privilege::ADMIN_NACHSCHREIBARBEITEN)) {
            $manager = $this->getDoctrine()->getManager();
            $repo = $manager->getRepository('IServNachschreibarbeitenBundle:NachschreibarbeitenEntry');

            $query = $repo->createQueryBuilder('e')
                ->join('e.date', 'd', 'WITH', 'd.date >= CURRENT_DATE()')
                ->orderBy('d.date', 'ASC')
                ->getQuery();

            $entries = array();
            $dates = array();

            foreach($query->getResult() as $result) {
                $entries[$result->getDate()->getId()][] = $result;
                $dates[$result->getDate()->getId()] = $result->getDate();
            }

            return array(
                'entries' => $entries,
                'dates' => $dates,
                'isKing' => $this->isGranted(Privilege::ADMIN_NACHSCHREIBARBEITEN),
                'current_user' => $this->getUser(),
                'breadcrumbs' => array(array('name' => _('Nachschreibarbeiten'), 'url' => $this->generateUrl('nachschreibarbeiten_index'))),
                'menu' => $this->createMenu('index')
            );
        } else {
            throw $this->createAccessDeniedException('You are not allowed to view this page.');
        }
    }

    /**
     * @Route("/dates", name="nachschreibarbeiten_dates_manage")
     * @Template()
     * @return array|RedirectResponse
     */
    public function dateManageAction(Request $request) {
        if($this->isGranted(Privilege::ADMIN_NACHSCHREIBARBEITEN)) {
            $manager = $this->getDoctrine()->getManager();
            $repo = $manager->getRepository('IServNachschreibarbeitenBundle:NachschreibarbeitenDate');

            $query = $repo->createQueryBuilder('d')
                ->where('d.date >= CURRENT_DATE()')
                ->orderby('d.date', 'ASC')
                ->setMaxResults(10)
                ->getQuery();

            $date = new NachschreibarbeitenDate();
            $date->setOwner($this->getUser());
            $date->setDate(new \DateTime('next friday'));
            $date->setTime(new \DateTime('14:00'));
            $date->setRoom('151');

            $form = $this->dateManageForm($date, $request, $manager);

            return array(
                'dates' => $query->getResult(),
                'breadcrumbs' => array(array('name' => _('Nachschreibarbeiten'), 'url' => $this->generateUrl('nachschreibarbeiten_index')), array('name' => _('Nachschreibarbeitentermine'), 'url' => $this->generateUrl('nachschreibarbeiten_dates_manage'))),
                'menu' => $this->createMenu('dates'),
                'dateForm' => $form->createView()
            );
        } else {
            throw $this->createAccessDeniedException("You are not allowed to view this page. YOU ARE NOT KING!!!");
        }
    }

    /**
     * @Route("/dates/edit/{id}", name="nachschreibarbeiten_dates_edit")
     * @Template()
     * @return array|RedirectResponse
     */
    public function dateEditAction(Request $request, $id) {
        if($this->isGranted(Privilege::ADMIN_NACHSCHREIBARBEITEN)) {
            $manager = $this->getDoctrine()->getManager();
            $date = $manager->getRepository('IServNachschreibarbeitenBundle:NachschreibarbeitenDate')->find($id);
            if(!$date) throw $this->createNotFoundException(_('Dieser Nachschreibtermin konnte nicht in der Datenbank gefunden werden.'));

            $form = $this->dateManageForm($date, $request, $manager);

            if($form->isSubmitted()) return $this->redirect($this->generateUrl('nachschreibarbeiten_dates_manage'));

            return array(
                'breadcrumbs' => array(array('name' => _('Nachschreibarbeiten'), 'url' => $this->generateUrl('nachschreibarbeiten_index')), array('name' => _('Nachschreibarbeitentermine'), 'url' => $this->generateUrl('nachschreibarbeiten_dates_manage')), array('name' => _('Nachschreibarbeitentermin bearbeiten'), 'url' => $this->generateUrl('nachschreibarbeiten_dates_edit', array('id' => $id)))),
                'menu' => $this->createMenu('dates'),
                'dateForm' => $form->createView()
            );
        } else {
            throw $this->createAccessDeniedException('You are not allowed to view this page. YOU ARE NOT KING!!!');
        }
    }

    /**
     * @Route("/dates/delete/{id}", name="nachschreibarbeiten_dates_delete")
     * @Template()
     * @return array|RedirectResponse
     */
    public function dateDeleteAction(Request $request, $id) {
        if($this->isGranted(Privilege::ADMIN_NACHSCHREIBARBEITEN)) {
            $manager = $this->getDoctrine()->getManager();
            $date = $manager->getRepository('IServNachschreibarbeitenBundle:NachschreibarbeitenDate')->find($id);
            if(!$date) throw $this->createNotFoundException(_('Dieser Nachschreibtermin konnte nicht in der Datenbank gefunden werden.'));

            $manager->remove($date);
            $manager->flush();
            $this->get('iserv.flash')->success(_('Der Termin wurde gelöscht!'));
            $this->get('iserv.logger')->write('Ein Datum wurde gelöscht: ' . $date, null, 'Nachschreibarbeiten');

            return $this->redirect($this->generateUrl('nachschreibarbeiten_dates_manage'));
        } else {
            throw $this->createAccessDeniedException('You are not allowed to view this page. YOU ARE NOT KING!!!');
        }
    }

    /**
     * @Route("/entry/create", name="nachschreibarbeiten_entry_create")
     * @Template()
     * @return array|RedirectResponse
     */
    public function entryCreateAction(Request $request) {
        if($this->isGranted(Privilege::ACCESS_NACHSCHREIBARBEITEN) || $this->isGranted(Privilege::ADMIN_NACHSCHREIBARBEITEN)) {
            $entry = new NachschreibarbeitenEntry();
            $entry->setOwner($this->getUser());
            $entry->setDuration(45);
            $entry->setSubject(_('Physik'));
            if($this->isGranted(\IServ\ExamPlanBundle\Security\Privilege::CREATING_EXAMS)) $entry->setTeacher($this->getUser());

            $form = $this->entryManageForm($entry, $request, $this->getDoctrine()->getManager());

            if($form->isSubmitted()) return $this->redirect($this->generateUrl('nachschreibarbeiten_index'));

            return array(
                'breadcrumbs' => array(array('name' => _('Nachschreibarbeiten'), 'url' => $this->generateUrl('nachschreibarbeiten_index')), array('name' => _('Nachschreiber_in eintragen'), 'url' => $this->generateUrl('nachschreibarbeiten_entry_create'))),
                'menu' => $this->createMenu('index'),
                'entryForm' => $form->createView()
            );

        } else {
            throw $this->createAccessDeniedException('You are not allowed to view this page. YOU ARE NOT KING!!!');
        }
    }

    /**
     * @Route("/entry/edit/{id}", name="nachschreibarbeiten_entry_edit")
     * @Template()
     * @return array|RedirectResponse
     */
    public function entryEditAction(Request $request, $id) {
        if($this->isGranted(Privilege::ACCESS_NACHSCHREIBARBEITEN) || $this->isGranted(Privilege::ADMIN_NACHSCHREIBARBEITEN)) {
            $manager = $this->getDoctrine()->getManager();
            $entry = $manager->getRepository('IServNachschreibarbeitenBundle:NachschreibarbeitenEntry')->find($id);
            if(!$entry) throw $this->createNotFoundException(_('Diese Nachschreiber_in konnte nicht in der Datenbank gefunden werden.'));

            if($this->isGranted(Privilege::ADMIN_NACHSCHREIBARBEITEN) || $entry->getOwner() === $this->getUser()) {
                $form = $this->entryManageForm($entry, $request, $manager);

                if($form->isSubmitted()) return $this->redirect($this->generateUrl('nachschreibarbeiten_index'));

                return array(
                    'breadcrumbs' => array(array('name' => _('Nachschreibarbeiten'), 'url' => $this->generateUrl('nachschreibarbeiten_index')), array('name' => _('Nachschreiber_in bearbeiten'), 'url' => $this->generateUrl('nachschreibarbeiten_entry_edit', array('id' => $id)))),
                    'menu' => $this->createMenu('index'),
                    'entryForm' => $form->createView()
                );
            } else {
                $this->createAccessDeniedException('You are not allowed to edit this entry.');
            }

        } else {
            throw $this->createAccessDeniedException('You are not allowed to view this page. YOU ARE NOT KING!!!');
        }
    }

    /**
     * @Route("/entry/delete/{id}", name="nachschreibarbeiten_entry_delete")
     * @Template()
     * @return RedirectResponse
     */
    public function entryDeleteAction(Request $request, $id) {
        if($this->isGranted(Privilege::ACCESS_NACHSCHREIBARBEITEN) || $this->isGranted(Privilege::ADMIN_NACHSCHREIBARBEITEN)) {
            $manager = $this->getDoctrine()->getManager();
            $entry = $manager->getRepository('IServNachschreibarbeitenBundle:NachschreibarbeitenEntry')->find($id);
            if(!$entry) throw $this->createNotFoundException(_('Diese Nachschreiber_in wurde nicht in der Datenbank gefunden.'));

            if($this->isGranted(Privilege::ADMIN_NACHSCHREIBARBEITEN) || $entry->getOwner() === $this->getUser()) {
                $manager->remove($entry);
                $manager->flush();
                $this->get('iserv.flash')->success(_('Die Nachschreiber_in wurde gelöscht!'));
                $this->get('iserv.logger')->write('Eine Nachschreiber_in wurde gelöscht: ' . $entry, null, 'Nachschreibarbeiten');
             } else {
                throw $this->createAccessDeniedException('You are not allowed to delete this entry.');
            }

            return $this->redirect($this->generateUrl('nachschreibarbeiten_index'));
        } else {
            throw $this->createAccessDeniedException('You are not allowed to view this page.');
        }
    }

    private function createMenu($path) {
        $menu = $this->get('knp_menu.factory')->createItem('page');

        $menu->addChild('nachschreibarbeiten_show', array(
            'label' => _('Entries'),
            'route' => 'nachschreibarbeiten_index',
            'extras' => ['icon' => 'molecule', 'icon_style' => 'fugue'],
            'current' => ('index' == $path),
        ));

        if($this->isGranted(Privilege::ADMIN_NACHSCHREIBARBEITEN)) {
            $menu->addChild('nachschreibarbeiten_dates', array(
                'label' => _('Nachschreibarbeitentermine'),
                'route' => 'nachschreibarbeiten_dates_manage',
                'extras' => ['icon' => 'flask', 'icon_style' => 'fugue'],
                'current' => ('dates' == $path),
            ));
        }

        return $menu;
    }

    private function dateManageForm(NachschreibarbeitenDate $date, Request $request, $manager) {
        $form_builder = $this->createFormBuilder($date)
            ->add('date', DateType::class, array('label' => _('Date')))
            ->add('time', TimeType::class, array('label' => _('Time')))
            ->add('room', TextType::class, array('label' => _('Room')))
            ->add('teacher', UserType::class, array(
                'label' => _('Betreuer_in'),
                'multiple' => false,
                'order_by' => null,
                'query_builder' => function(EntityRepository $er) {
                    return $er->createPrivilegeQueryBuilder(\IServ\ExamPlanBundle\Security\Privilege::CREATING_EXAMS);
                }
            ))
            ->add('save', SubmitType::class, array('label' => _('Absenden')));

        $form = $form_builder->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $manager->persist($date);
            $manager->flush();
            $this->get('iserv.logger')->write('Ein Termin wurde erstellt/bearbeitet: ' . $date, null, 'Nachschreibarbeiten');
            $this->get('iserv.flash')->success(_('Der Termin wurde gespeichert!'));
        }

        return $form;
    }

    private function entryManageForm(NachschreibarbeitenEntry $entry, Request $request, $manager) {
        $form_builder = $this->createFormBuilder($entry)
            ->add('date', NachschreibarbeitenDateType::class, array('label' => _('Date')))
            ->add('student', UserType::class, array(
                'label' => _('Schüler_in'),
                'multiple' => false,
                'order_by' => null,
                'query_builder' => function(EntityRepository $er) {
                    return $er->createPrivilegeQueryBuilder(\IServ\ExamPlanBundle\Security\Privilege::DOING_EXAMS);
                }
            ))
            ->add('class', TextType::class, array('label' => _('Klasse')))
            ->add('subject', TextType::class, array('label' => _('Fach')))
            ->add('additional_material', TextType::class, array('label' => _('Zusatzmaterialien')))
            ->add('duration', NumberType::class, array('label' => _('Dauer [Minuten]')))
            ->add('teacher', UserType::class, array(
                'label' => _('Lehrkraft'),
                'multiple' => false,
                'order_by' => null,
                'query_builder' => function(EntityRepository $er) {
                    return $er->createPrivilegeQueryBuilder(\IServ\ExamPlanBundle\Security\Privilege::CREATING_EXAMS);
                }
            ))
            ->add('save', SubmitType::class, array('label' => _('Absenden')));

        $form = $form_builder->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $manager->persist($entry);
            $manager->flush();
            $this->get('iserv.logger')->write('Eine Nachschreiber_in wurde erstellt/bearbeitet: ' . $entry, null, 'Nachschreibarbeiten');
            $this->get('iserv.flash')->success(_('Die Nachschreiber_in wurde gespeichert!'));
        }

        return $form;
    }

}
