<?php
//// src/IServ/CrudBundle/Controller/CrudController.php
//namespace IServ\NachschreibarbeitenBundle\Controller;
//
//use IServ\CrudBundle\Controller\CrudController as BaseController;
//use IServ\NachschreibarbeitenBundle\Form\Type\SelectType;
//use IServ\NachschreibarbeitenBundle\Form\Type\UploadType;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
//use Symfony\Component\HttpFoundation\Request;
//
//class CrudController extends BaseController
//{
//    /**
//     * Class the show action
//     *
//     * @Template
//     *
//     * @param $request \Symfony\Component\HttpFoundation\Request
//     * @param string $id
//     * @return \Symfony\Component\HttpFoundation\Response
//     */
//    public function showAction(Request $request, $id)
//    {
//        $response = parent::showAction($request, $id);
//
//        if (is_array($response)) {
//            $response['downloadForm'] = $this->createForm(
//                SelectType::class,
//                null,
//                array(
//                    'action' => $this->generateUrl('exercise_download', array('id' => $id)),
//                    'elements' => $response['item']->getSubmissions(),
//                    'class' => 'IServExerciseBundle:ExerciseSubmission',
//                    'confirm' => false,
//                    'options' => array('buttonLabel' => _('Download'), 'buttonIcon' => 'download'),
//                )
//            )->createView()
//            ;
//        }
//
//        // Get array with users without submissions
//        $userWOSubmission = $this->getDoctrine()->getManager()->getRepository('IServExerciseBundle:Exercise')->findUsersWithoutSubmissions($response['item']);
//        $response['missingSubmissions'] = $userWOSubmission;
//
//        return $response;
//    }
//
//    /**
//     * Calls the edit action
//     *
//     * @Template
//     *
//     * @param $request \Symfony\Component\HttpFoundation\Request
//     * @param string $id
//     * @return \Symfony\Component\HttpFoundation\Response
//     */
//    public function editAction(Request $request, $id)
//    {
//        $response = parent::editAction($request, $id);
//
//        if (is_array($response)) {
//            $response['uploadForm'] = $this->createForm(
//                UploadType::class,
//                array('id' => $id, 'type' => 'attachment'),
//                array(
//                    'action' => $this->generateUrl('exercise_upload'),
//                    'attr' => array('class' => 'form-inline'),
//                )
//            )->createView()
//            ;
//
//            $response['deleteForm'] = $this->createForm(
//                SelectType::class,
//                null,
//                array(
//                    'action' => $this->generateUrl(
//                        'exercise_delete_confirm',
//                        array('id' => $id, 'type' => 'attachment')
//                    ),
//                    'elements' => $response['item']->getAttachments(),
//                    'class' => 'IServExerciseBundle:ExerciseAttachment',
//                )
//            )->createView()
//            ;
//        }
//
//        return $response;
//    }
//
//}
