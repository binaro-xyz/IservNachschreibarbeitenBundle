<?php
//// src/IServ/ExerciseBundle/Controller/ManageController.php
//namespace IServ\NachschreibarbeitenBundle\Controller;
//
//use IServ\CoreBundle\Entity\User;
//use IServ\NachschreibarbeitenBundle\Form\Type\ChangeOwnerType;
//use IServ\NachschreibarbeitenBundle\Security\Privilege;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
//use Symfony\Component\HttpFoundation\Request;
//use Symfony\Component\HttpFoundation\Response;
//
///**
// * @Route("manage/exercise")
// */
//class ManageController extends AbstractNachschreibarbeitenController
//{
//    /**
//     * Changes the owner of an exercise
//     *
//     * @Route("/owner/{id}", name="exercise_manage_owner")
//     * @Template("IServExerciseBundle:Manage:owner.html.twig")
//     *
//     * @param Request $request
//     * @param $id
//     *
//     * @return Response
//     */
//    public function changeOwnerAction(Request $request, $id)
//    {
//        $exercise = $this->getExercise($id);
//
//        $query = $this->getDoctrine()->getRepository('IServCoreBundle:User')->createPrivilegeQueryBuilder(Privilege::MANAGE_EXERCISES);
//        $choices = $query->getQuery()->getResult();
//        unset($choices[array_search($this->getUser(), $choices)]);
//
//        $changeForm = $this->createForm(ChangeOwnerType::class, null, array('choices' => $choices));
//        $changeForm->handleRequest($request);
//
//        if ($changeForm->isSubmitted()) {
//            if ($changeForm->getClickedButton()->getName() === 'cancel') {
//                return $this->redirectToRoute('manage_exercise_show', ['id' => $id]);
//            } elseif ($changeForm->isValid()) {
//                /* @var $owner User */
//                $formData = $changeForm->getData();
//                $owner = $formData['owner'];
//
//                $body = _(
//                    "Hello %s,\n\n" .
//                    "%s has granted you the ownership of the exercise \"%s\".\n\n" .
//                    "--\n" .
//                    "*This e-mail was generated automatically*"
//                );
//                $swiftmessage = new \Swift_Message();
//                $swiftmessage
//                    ->addFrom($this->getUser()->getUsername() . '@' . $this->get('iserv.config')->get('domain'))
//                    ->addTo($owner->getUsername() . '@' . $this->get('iserv.config')->get('domain'))
//                    ->setSubject(__('You are now the owner of the exercise "%s"', $exercise->getTitle()))
//                    ->setBody(sprintf($body, $owner->getName(), $exercise->getOwner()->getName(), $exercise->getTitle()))
//                ;
//
//                $exercise->setOwner($owner);
//                $em = $this->getDoctrine()->getManager();
//                $em->persist($exercise);
//                $em->flush();
//
//                $this->get('mailer')->send($swiftmessage);
//                $this->addFlash('success', _('The owner has successfully been changed. You no longer have access to this exercise.'));
//
//                return $this->redirectToRoute('manage_exercise_index');
//            }
//        }
//
//        $this->addBreadcrumb(_('Exercises'), $this->generateUrl('exercise_index'));
//        $this->addBreadcrumb(_('Manage'), $this->generateUrl('manage_exercise_index'));
//        $this->addBreadcrumb($exercise->getTitle(), $this->generateUrl('manage_exercise_show', array('id' => $exercise->getId())));
//        $this->addBreadcrumb(_('Change owner'));
//
//        return array(
//            'form' => $changeForm->createView(),
//        );
//    }
//
//}
