<?php
// src/IServ/ExerciseBundle/Controller/ExerciseController.php
namespace IServ\NachschreibarbeitenBundle\Controller;

use IServ\NachschreibarbeitenBundle\Entity\Exercise;
use IServ\NachschreibarbeitenBundle\Entity\ExerciseFile;
use IServ\NachschreibarbeitenBundle\Entity\ExerciseSubmission;
use IServ\NachschreibarbeitenBundle\Form\Type\SelectType;
use IServ\NachschreibarbeitenBundle\Form\Type\TextFieldTaskType;
use IServ\NachschreibarbeitenBundle\Form\Type\UploadType;
use IServ\NachschreibarbeitenBundle\Security\Privilege;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * @Route("exercise")
 */
class ExerciseController extends AbstractExerciseController
{
    /**
     * @Route("", name="exercise_index")
     * @Template()
     * @return array|RedirectResponse
     */
    public function indexAction()
    {
        if (!$this->isGranted(Privilege::EXCLUDED_FROM_EXERCISES)) {
            return array(
                'exercises' => $this->getRepo()->findExercisesByUser($this->getUser()),
                'breadcrumbs' => array(array('name' => _('Exercises'), 'url' => $this->generateUrl('exercise_index'))),
            );
        } else {
            if ($this->isGranted(Privilege::MANAGE_EXERCISES)) {
                return $this->redirect($this->generateUrl('manage_exercise_index'));
            } else {
                throw $this->createAccessDeniedException("You are not allowed to view this page.");
            }
        }
    }

    /**
     * @Route("/show/{id}", name="exercise_show")
     * @Template()
     *
     * @param Request $request
     * @param integer $id
     * @return array
     */
    public function showAction(Request $request, $id)
    {
        /** @var Exercise $exercise */
        $exercise = $this->getRepo()->find($id);

        if (null === $exercise) {
            throw $this->createNotFoundException('The selected exercise could not be found.');
        }

        if (!$this->getHelper()->isAllowedToAccess($exercise, $this->getUser()) || $exercise->getStartDate() > new \DateTime()) {
            throw $this->createAccessDeniedException('You are not allowed to view this page');
        }

        $response = ['exercise' => $exercise];
        if ($exercise->getType() == 'text') {
            $submission = $this->getRepo()->findUserTextSubmission($exercise, $this->getUser());
            $textSubmission = $submission !== null ? $submission->getText() : '';

            if ($request->get('edit', false)) {
                $textForm = $this->createForm(
                    TextFieldTaskType::class,
                    $submission,
                    array('action' => $this->generateUrl('exercise_upload_text', ['id' => $id]))
                );

                $response['textForm'] = $textForm->createView();
            }
            
            $response['text'] = $textSubmission;

        } else {
            $uploadForm = $this->createForm(
                UploadType::class,
                array('id' => $exercise->getId(), 'type' => 'submission'),
                array(
                    'action' => $this->generateUrl('exercise_upload'),
                )
            );

            $submissions = $this->getRepo()->findUserSubmissions($exercise, $this->getUser());
            $response['submissions'] = $submissions;

            $deleteForm = $this->createForm(
                SelectType::class,
                null,
                array(
                    'action' => $this->generateUrl('exercise_delete_confirm', array('id' => $id, 'type' => 'submission')),
                    'elements' => $submissions,
                )
            );

            $response['uploadForm'] = $uploadForm->createView();
            $response['deleteForm'] = $deleteForm->createView();
        }

        // Track path
        $this->addBreadcrumb(_('Exercises'), $this->generateUrl('exercise_index'));
        $this->addBreadcrumb((string)$exercise);

        return $response;
    }

    /**
     * @Route("/text/{id}", name="exercise_text")
     *
     * @param integer $id
     *
     * @return Response
     */
    public function textAction($id)
    {
        /* @var $exercise Exercise */
        $exercise = $this->getExercise($id);
        /* @var $submission ExerciseSubmission */
        $submission = $exercise->getSubmissionsSorted();

        //replacing illegal characters
        $exerciseName = preg_replace('/[^\w._-]/', '', $exercise->getTitle());
        
        mkdir('/tmp/exercise/');
        $zip = new \ZipArchive();
        $filename = '/tmp/exercise/' . $exerciseName . '.zip';
        $zip->open($filename, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        /* @var $sub ExerciseSubmission */
        foreach ($submission as $sub) {
            $zip->addFromString($sub->getUser() . '.txt', $sub->getText());
        }

        $zip->close();

        $response = new Response(readfile($filename), Response::HTTP_OK);
        $response->headers->set('Content-Type', 'application/zip');
        $headers = [
            'Content-Disposition' => $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, basename($filename), preg_replace('/[^\w._-]/', '', basename($filename))),
            'Content-Length' => filesize($filename),
        ];
        $response->headers->add($headers);

        return $response;
    }

    /**
     * @Route("/file/{id}", name="exercise_file")
     *
     * @todo fix pdf files...
     *
     * @param integer $id
     *
     * @return Response
     */
    public function fileAction($id)
    {
        /* @var $file ExerciseFile */
        $file = $this->getDoctrine()->getManager()->getRepository('IServExerciseBundle:ExerciseFile')->find($id);

        if (!$file) {
            throw $this->createNotFoundException('File not found');
        }

        if (!$this->getHelper()->isAllowedToAccessFile($file, $this->getUser())) {
            throw $this->createAccessDeniedException('You are not allowed to access this file');
        }

        // readgzfile($this->helper->getFilePath($file))
        $data = gzread(gzopen($this->getHelper()->getFilePath($file), 'r'), $file->getSize());
        $response = new Response($data, Response::HTTP_OK);
        $headers = array(
            'Content-Disposition' => $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $file->getTitle(), preg_replace('/[^\w._-]/', '', $file->getTitle())),
            'Content-Type' => $file->getType(),
            'Content-Length' => $file->getSize(),
            'Pragma' => 'cache',
            'Content-Transfer-Encoding' => 'binary',
        );
        $response->headers->add($headers);

        return $response;
    }

    /**
     * @Route("/download", name="exercise_download")
     *
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function downloadAction(Request $request)
    {
        $users = array();

        $exercise = $this->getRepo()->find($request->get('id'));

        $form = $this->createForm(SelectType::class, null, ['elements' => $exercise->getSubmissions()]);
        $form->handleRequest($request);

        /** @var ExerciseSubmission $element */
        foreach ($form->getData()['elements'] as $element) {
            if (!in_array($element->getUser()->getUsername(), $users)) {
                $users[] = $element->getUser()->getUsername();
            }
        }
        /** @var Exercise $exercise */
        $response = $this->getHelper()->downloadSubmissions($exercise, $users);

        if (is_string($response)) {
            $this->get('iserv.flash')->error($response);

            return $this->redirect($this->generateUrl('manage_exercise_show', array('id' => $request->get('id'))));
        } else {
            return $response;
        }
    }

    /**
     * @Route("/upload", name="exercise_upload")
     * @Method("POST")
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function uploadAction(Request $request)
    {
        $uploadForm = $this->createForm(UploadType::class);
        $uploadForm->handleRequest($request);
        $formData = $uploadForm->getData();

        if ($uploadForm->isValid()) {
            if (!empty($formData)) {
                $exercise = $this->getRepo()->find($formData['id']);

                /** @var Exercise $exercise */
                $this->getHelper()->handleUpload($exercise, $formData, $this->getUser());

                if ('attachment' === $formData['type']) {
                    return $this->redirect($this->generateUrl('manage_exercise_edit', array('id' => $exercise->getId())) . '#files');
                } else {
                    return $this->redirect($this->generateUrl('exercise_show', array('id' => $exercise->getId())));
                }
            } else {
                $this->get('iserv.flash')->error('There was an error concerning your upload. Maybe the file is too large?');

                return $this->redirect($this->generateUrl('exercise_index'));
            }
        } else {
            $errors = array();
            foreach ($uploadForm->getErrors(true) as $error) {
                $errors[] = $error->getMessage();
            }

            $this->get('iserv.flash')->error(implode("\n", $errors));

            return $this->redirect($this->generateUrl('exercise_show', array('id' => $formData['id'])));
        }
    }

    /**
     * @Route("/{id}/upload/text", name="exercise_upload_text")
     * @Method("POST")
     *
     * @todo move error message to form
     *
     * @param Request $request
     * @param int $id
     *
     * @return RedirectResponse|Response
     */
    public function uploadTextAction(Request $request, $id)
    {
        /** @var Exercise $exercise */
        if (!$exercise = $this->getRepo()->find($id)) {
            throw $this->createNotFoundException('Exercise not found!');
        }

        // Get or create submission object
        $submission = $this->getRepo()->findUserTextSubmission($exercise, $this->getUser());

        $form = $this->createForm(TextFieldTaskType::class, $submission);
        $form->handleRequest($request);

        if (null !== $form->getClickedButton() && $form->getClickedButton()->getName() === 'cancel') {
            return $this->redirectToRoute('exercise_show', array('id' => $id));
        }

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($submission);
            $em->flush();

            $this->addFlash('success', _('Your text was successfully submitted.'));
        } else {
            $errors = array();
            foreach ($form->getErrors(true) as $error) {
                $errors[] = $error->getMessage();
            }

            $this->get('iserv.flash')->error(implode("\n", $errors));
        }

        return $this->redirectToRoute('exercise_show', array('id' => $id));
    }

    /**
     * @Route("/delete", name="exercise_delete")
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function deleteAction(Request $request)
    {
        $exercise = $this->getRepo()->find($request->get('id'));

        /** @var Exercise $exercise */
        $formElements = $request->get('type') == 'submission' ? $this->getRepo()->findUserSubmissions($exercise, $this->getUser()) : $exercise->getAttachments();
        $class = $request->get('type') === 'attachment' ? 'IServExerciseBundle:ExerciseAttachment' : 'IServExerciseBundle:ExerciseSubmission';

        $confirmForm = $this->createForm(SelectType::class, null, ['elements' => $formElements, 'class' => $class]);
        $confirmForm->handleRequest($request);

        if ($confirmForm->isValid()) {
            foreach ($confirmForm->getData()['elements'] as $element) {
                $this->getHelper()->deleteElement($element, $request->get('type'), $exercise, $this->getUser());
            }
        }

        $route = $request->get('type') == 'attachment' ? 'manage_exercise_edit' : 'exercise_show';

        return $this->redirect($this->generateUrl($route, array('id' => $exercise->getId())));
    }

    /**
     * @Route("/attachment/edit", name="exercise_attachment_update", options={"expose" = true})
     * @Method("POST")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateAttachmentAction(Request $request)
    {
        /* @var $attachment \IServ\NachschreibarbeitenBundle\Entity\ExerciseAttachment */
        $attachment = $this->getDoctrine()->getRepository('IServExerciseBundle:ExerciseAttachment')->find($request->request->get('pk'));

        if (!$attachment) {
            throw $this->createNotFoundException('Couldn\'t find the exercise attachment!');
        }
        if ($attachment->getExercise()->getOwner() != $this->getUser()) {
            throw $this->createAccessDeniedException('You are not allowed to edit this attachment');
        }

        // On-the-fly form usage fails due to naming not matching symfony style!
        // TODO: Improve x-editable integration?
//         $data = array('name' => 'description');
//         $form = $this->createFormBuilder($data)
//             ->add('name', 'text')
//             ->add('pk', 'text')
//             ->add('value', 'text')
//             ->getForm()
//         ;
//         $form->handleRequest($request);
//         if ($form->isSubmitted() && $form->isValid()) {

        if ('description' === $request->request->get('name') && null !== $value = $request->get('value')) {

            // Update attachment and persist changes
            $em = $this->getDoctrine()->getManager();
            $attachment->setDescription($value);
            $em->persist($attachment);
            $em->flush();

            return new JsonResponse(
                array(
                    'status' => 'success',
                    'message' => _('Attachment updated.'),
                )
            );
        }

        return new JsonResponse(
            array(
                'status' => 'error',
                'message' => _('Attachment not updated.'),
            )
        );
    }

    /**
     * @Route("/delete/confirm", name="exercise_delete_confirm")
     * @Method("POST")
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function deleteConfirmAction(Request $request)
    {
        $exercise = $this->getRepo()->find($request->get('id'));

        /* @var $exercise \IServ\NachschreibarbeitenBundle\Entity\Exercise */
        $mode = $request->get('type');
        $formElements = 'submission' === $mode
            ? $this->getRepo()->findUserSubmissions($exercise, $this->getUser())
            : $exercise->getAttachments();
        $class = 'attachment' === $mode ? 'IServExerciseBundle:ExerciseAttachment' : 'IServExerciseBundle:ExerciseSubmission';

        $deleteForm = $this->createForm(SelectType::class, null, ['elements' => $formElements, 'class' => $class]);
        $deleteForm->handleRequest($request);

        if ($deleteForm->isValid()) {
            if (count($deleteForm->getData()['elements']) > 0) {
                $confirmForm = $this->createForm(
                    SelectType::class,
                    $deleteForm->getData(),
                    array(
                        'action' => $this->generateUrl(
                            'exercise_delete',
                            array(
                                'id' => $request->get('id'),
                                'type' => $request->get('type'),
                            )
                        ),
                        'elements' => $formElements,
                        'class' => $class,
                        'confirm' => true,
                        'options' => array('buttonLabel' => _('Yes'), 'buttonIcon' => 'ok', 'buttonClass' => 'btn-danger'),
                    )
                );

                // Track path
                if ('attachment' === $mode) {
                    $this->addBreadcrumb(_('Exercises'), $this->generateUrl('manage_exercise_index'));
                    $this->addBreadcrumb($exercise->getTitle(), $this->generateUrl('manage_exercise_edit', array('id' => $exercise->getId())));
                } else {
                    $this->addBreadcrumb(_('Exercises'), $this->generateUrl('exercise_index'));
                    $this->addBreadcrumb($exercise->getTitle(), $this->generateUrl('exercise_show', array('id' => $exercise->getId())));
                }
                $this->addBreadcrumb(_('Confirm action'));

                return $this->render(
                    'IServExerciseBundle:Form:confirmForm.html.twig',
                    array(
                        'confirmForm' => $confirmForm->createView(),
                        'mode' => $mode,
                    )
                );
            } else {
                $this->get('iserv.flash')->alert(_('No items selected.'));

                return $this->redirect($this->generateUrl('exercise_delete', array('id' => $exercise->getId())));
            }
        } else {
            $this->get('iserv.flash')->error(_('There was an error processing your request.'));

            return $this->redirect($this->generateUrl('exercise_delete', array('id' => $exercise->getId())));
        }
    }

    /**
     * @Route("/showText/{id}", name="exercise_showText")
     * @Template()
     *
     * @param integer $id
     * @return array
     */
    public function showTextAction($id)
    {
        /* @var $submission ExerciseSubmission */
        $submission = $this->getDoctrine()->getManager()->getRepository('IServExerciseBundle:ExerciseSubmission')->find($id);

        if ($submission->getExercise()->getOwner() != $this->getUser()) {
            throw $this->createAccessDeniedException('You are not allowed to look at this submission');
        }

        $data['exerciseId'] = $submission->getExercise()->getId();
        $data['pupil'] = $submission->getUser()->getName();
        $data['text'] = $submission->getText();

        return $data;
    }
}
