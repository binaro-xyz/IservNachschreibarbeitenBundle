<?php
namespace IServ\NachschreibarbeitenBundle\Service;

use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;
use Doctrine\Common\Collections\ArrayCollection;
use IServ\CoreBundle\Entity\User;
use IServ\CoreBundle\Util\Format;
use IServ\NachschreibarbeitenBundle\Entity\Exercise;
use IServ\NachschreibarbeitenBundle\Entity\ExerciseAttachment;
use IServ\NachschreibarbeitenBundle\Entity\ExerciseFile;
use IServ\NachschreibarbeitenBundle\Entity\ExerciseSubmission;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;

class ExerciseHelper
{
    /**
     * @var string
     */
    private $exercisePath = '/var/lib/iserv/exercise';

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Session
     */
    private $session;

    /**
     * The constructor
     *
     * @param Doctrine $em
     * @param Filesystem $filesystem
     * @param Session $session
     */
    public function __construct(Doctrine $em, Filesystem $filesystem, Session $session)
    {
        $this->em = $em->getManager();
        $this->filesystem = $filesystem;
        $this->session = $session;
    }

    /**
     * Check if the user is allowed to access the exercise
     * Owner are always allowed to access
     *
     * @param Exercise $exercise
     * @param User $user
     * @return boolean
     */
    public function isAllowedToAccess(Exercise $exercise, User $user)
    {
        $allowed = ($exercise->getOwner() == $user);

        foreach ($user->getGroups() as $group) {
            $allowed = !$allowed ? $exercise->hasParticipant($group) : $allowed;
        }

        return $allowed;
    }

    /**
     * Check if the user is allowed to acces the given file
     * Owner of the exercise also have access to the files
     *
     * @param ExerciseFile $file
     * @param User $user
     *
     * @return boolean
     */
    public function isAllowedToAccessFile(ExerciseFile $file, User $user)
    {
        $allowed = false;
        if ($file->getSubmissions()->count() > 0) {
            foreach ($file->getSubmissions() as $submission) {
                $allowed = ($submission->getUser() == $user) || ($submission->getExercise()->getOwner() == $user);
            }
        }
        if ($file->getAttachments()->count() > 0) {
            foreach ($file->getAttachments() as $attachment) {
                foreach ($attachment->getExercise()->getParticipants() as $participant) {
                    $allowed = ($user->getGroups()->contains($participant)) || ($attachment->getExercise()->getOwner() == $user);
                }
            }
        }

        return $allowed;
    }

    /**
     * Get file path
     *
     * @param ExerciseFile $file
     *
     * @return string
     */
    public function getFilePath(ExerciseFile $file)
    {
        return $this->exercisePath . '/' . $this->getBucket($file->getHash()) . '/' . $file->getHash() . '.gz';
    }

    /**
     * Download submission as zip archive
     *
     * @param Exercise $exercise
     * @param array $users An array of user ids
     *
     * @return Response|string
     */
    public function downloadSubmissions(Exercise $exercise, array $users)
    {
        $tempfile = tempnam($this->exercisePath, 'zip');
        $zip = new \ZipArchive();
        $res = $zip->open($tempfile, \ZipArchive::OVERWRITE);

        if ($res === true) {
            $count = 0;
            /** @var User $user */
            foreach ($users as $user) {
                // Get user object
                $user = $this->em->getRepository('IServCoreBundle:User')->find($user);

                // Create user directory
                $dir = $user->getName();
                // Convert encoding to ensure Windows can handle it
                $dir = iconv("UTF-8", "CP437//TRANSLIT", $dir);

                // Fetch user submissions
                $submissions = $this->em->getRepository('IServExerciseBundle:Exercise')->findUserSubmissions($exercise, $user);

                // Iterate over the user submissions and add them to the zip archive
                foreach ($submissions as $submission) {
                    $fn = $this->getFilePath($submission->getFile());
                    $data = '';
                    if (file_exists($fn)) {
                        $handle = gzopen($fn, 'r');

                        while (!gzeof($handle)) {
                            $data .= gzgets($handle, 4096);
                        }
                        gzclose($handle);

                        if ($data) {
                            $fn = $submission->getFile()->getTitle();

                            // Convert encoding to ensure Windows can handle it
                            $fn = iconv("UTF-8", "CP437//TRANSLIT", $fn);

                            // Strip invalid characters from file name
                            $fn = preg_replace('#[*<>\]\[="\/:\?\|]#', '_', $fn);

                            // Add file to zip
                            $zip->addFromString($dir . '/' . $fn, $data);
                        }
                        $count++;
                    } else {
                        // Add text file with an alternate text if the file could not be found on the filesystem
                        $zip->addFromString($dir . '/' . $fn . '.txt', _("Sorry! This file could not be found!"));
                    }
                }
            }
            $zip->setArchiveComment("IServ submission archive");
            $zip->close();

            if ($count > 0) {
                $zipname = $exercise->getTitle() . ' - ' . Format::date(new \DateTime(), _("%#m/%#d/%Y %#I:%M %p")) . '.zip';

                $zipHandle = fopen($tempfile, 'r');

                $res = new Response(fread($zipHandle, filesize($tempfile)), Response::HTTP_OK);
                $headers = array(
                    'Content-Disposition' => $res->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $zipname, preg_replace('/[^\w._-]/', '', $zipname)),
                    'Content-Type' => 'application/zip',
                    'Content-Length' => filesize($tempfile),
                    'Pragma' => 'cache',
                    'Content-Transfer-Encoding' => 'binary',
                );
                $res->headers->add($headers);

                fclose($zipHandle);
            } else {
                $res = _("No files could be added to the ZIP archive!");
            }
        } else {
            $zip->close(); // Just to be sure
            $res = _("System error: Couldn't create ZIP archive!");
        }

        @unlink($tempfile);
        return $res;
    }

    /**
     * Handle the upload of a new file
     *
     * @param Exercise $exercise
     * @param array $data
     * @param User $user
     */
    public function handleUpload(Exercise $exercise, array $data, User $user)
    {

        if ($this->isAllowedToUpload($exercise, $user, $data['type'])) {
            foreach ($data['file'] as $file) {
                if (null === $file) {
                    $this->session->getFlashBag()->add('warning', _('Please choose a file to upload.'));
                }
                elseif ($file->getError() == UPLOAD_ERR_OK) {
                    $element = $data['type'] == 'attachment' ? new ExerciseAttachment() : new ExerciseSubmission(array('user' => $user));
                    $element->setFile($this->newUploadedFile($file));
                    $this->em->persist($element);

                    if ($data['type'] == 'attachment') {
                        $exercise->addAttachment($element);
                        $message = _('Your file "%s" was successfully attached.');
                    } else {
                        $exercise->addSubmission($element);
                        $message = _('Your file "%s" was successfully submitted.');
                    }

                    $this->session->getFlashBag()->add('success', sprintf($message, $element));
                } else {
                    $this->session->getFlashBag()->add('error', $file->getErrorMessage());
                }
            }
            $this->em->persist($exercise);
            $this->em->flush();
        } else {
            throw new AccessDeniedException('You are not allowed to upload files to this exercise.');
        }
    }

    /**
     * Check if the user is allowed to upload a file
     *
     * @param Exercise $exercise
     * @param User $user
     * @param string $type
     * @return boolean
     */
    private function isAllowedToUpload(Exercise $exercise, User $user, $type)
    {
        if ($type == 'submission') {
            if ($this->isAllowedToAccess($exercise, $user)) {
                if ($exercise->getEndDate()->modify('+' . $exercise->getTolerance() . ' days') > new \DateTime()) {
                    return true;
                }
            }
        } else {
            if ($exercise->getOwner() == $user) {
                return true;
            }
        }

        return false;
    }

    /**
     * Handle new uploaded file
     *
     * @todo write syslog
     *
     * @param UploadedFile $uploadedFile
     * @return boolean|\IServ\NachschreibarbeitenBundle\Entity\ExerciseFile
     */
    public function newUploadedFile(UploadedFile $uploadedFile)
    {
        $hash = md5_file($uploadedFile);
        $lookup = $this->em->getRepository('IServExerciseBundle:ExerciseFile')->findBy(array('hash' => $hash));

        if (empty($lookup)) {
            // Get bucket name
            $bucket = $this->getBucket($hash);

            // Create bucket directory
            if (!$this->filesystem->exists($this->exercisePath . '/' . $bucket)) {
                $this->filesystem->mkdir($this->exercisePath . '/' . $bucket);
            }

            $file = new ExerciseFile();

            // Set ExerciseFile data
            $file->setHash($hash);
            $file->setTitle($uploadedFile->getClientOriginalName());
            $file->setType($uploadedFile->getMimeType());
            $file->setSize($uploadedFile->getClientSize());

            // Move uploaded file and create new ExerciseFile object
            if ($uploadedFile->move($this->exercisePath . '/' . $bucket . '/', $hash)) {
                // Gzip file
                exec('gzip ' . $this->exercisePath . '/' . $bucket . '/' . $hash);

                // Syslog up insert!

                $this->em->persist($file);
                $this->em->flush();
            } else {
                return false;
            }
        } else {
            $file = $lookup[0];
            // Syslog cp insert
        }

        return $file;
    }

    /**
     * Handle deletion of a attachment or a submission by a user
     *
     * @param integer $elementId
     * @param string $type submission or attachment
     * @param Exercise $exercise
     * @param User $user
     */
    public function deleteElement($elementId, $type, Exercise $exercise, User $user)
    {
        $allowed = false;

        $repo = preg_match('/(attachment|submission)/i', $type)
            ? $this->em->getRepository('IServExerciseBundle:Exercise' . ucfirst($type))
            : null;

        if (isset($repo)) {
            /** @var ExerciseSubmission|ExerciseAttachment $element */
            $element = $repo->find($elementId);

            if ($element->getExercise() == $exercise) {
                $allowed = ($type == 'submission')
                    ? ($element->getUser() == $user)
                    : ($element->getExercise()->getOwner() == $user);
            }

            // Prepare messages
            $messages = array(
                'success' => array(
                    'attachment' => _('Your attachment "%s" has successfully been deleted.'),
                    'submission' => _('Your submission "%s" has successfully been deleted.'),
                ),
                'error' => array(
                    'attachment' => _('You are not allowed to delete this attachment.'),
                    'submission' => _('You are not allowed to delete this submission.'),
                )
            );

            if ($allowed) {
                $this->deleteFromExercise($element, $type);
                $this->session->getFlashBag()->add('success', sprintf($messages['success'][$type], $element));
            } else {
                $this->session->getFlashBag()->add('error', $messages['error'][$type]);
            }

            // Apply changes to the database
            $this->em->flush();
        } else {
            throw new InvalidArgumentException();
        }
    }

    /**
     * Delete a submission or attachment from the exercise
     *
     * @param ExerciseAttachment|ExerciseSubmission $object
     * @param string $type
     */
    public function deleteFromExercise($object, $type)
    {
        if (preg_match('/(attachment|submission)/i', $type)) {
            // Get the ExerciseFile object
            $file = $object->getFile();

            if ($file !== null) {
                // Build function to call
                $call = 'remove' . ucfirst($type);

                // Remove submission from file to get accurate counts
                $file->$call($object);

                // Check the file
                $this->checkFile($file);
            }

            // Remove attachment/submission from database
            $this->em->remove($object);
        }
    }

    /**
     * Check if a file is still used as attachment or submission else delete it from the filesystem and the database
     *
     * @param ExerciseFile $file
     */
    private function checkFile(ExerciseFile $file)
    {
        if ($file->getSubmissions()->count() == 0 && $file->getAttachments()->count() == 0) {
            $this->deleteFile($file->getHash());
            $this->em->remove($file);
        }
    }

    /**
     * Remove a file from the filesystem
     *
     * @param string $hash
     */
    private function deleteFile($hash)
    {
        $path = $this->exercisePath . '/' . $this->getBucket($hash) . '/' . $hash . '.gz';
        if ($this->filesystem->exists($path)) {
            $this->filesystem->remove($path);
        }
    }

    /**
     * Just return first 2 characters of the md5 string. This is the same as 256 buckets
     * @param string $hash
     * @return string
     */
    private function getBucket($hash)
    {
        return $hash[0] . $hash[1];
    }

    /**
     * Copy the exercise and set a new title
     *
     * @param ArrayCollection $entites
     * @param User $user
     */
    public function copy(ArrayCollection $entites, User $user)
    {
        foreach ($entites as $entity) {
            /**
             * @var Exercise
             */
            $ex = clone $entity;

            // Generate new title by first adding "Copy of" and if that is already done add an incrementing number
            $title = '';
            $i = 0;
            do {
                $title = $title ? sprintf(__('Copy of %s', $entity->getTitle()) . ' %d', ++$i) : __('Copy of %s', $entity->getTitle());
                $qb = $this->em->getRepository('IServExerciseBundle:Exercise')->createQueryBuilder('e');
                $qb->select('COUNT(e)')
                    ->where('e.title = :title')
                    ->andWhere('e.owner = :owner')
                    ->setParameter('title', $title)
                    ->setParameter('owner', $user->getId());

                $found = $qb->getQuery()->getSingleScalarResult();
            } while ($found);

            // Set the new title and persist the new exercise
            $ex->setTitle($title);
            $this->em->persist($ex);

            // Add attachments
            foreach ($entity->getAttachments() as $attachment) {
                $a = clone $attachment;
                $ex->addAttachment($a);
                $this->em->persist($a);
            }

            // Update the exercise and flush all changes to the database
            $this->em->persist($ex);
            $this->em->flush();
        }
    }
}