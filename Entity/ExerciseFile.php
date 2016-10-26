<?php
// src/IServ/ExerciseBundle/Entity/ExerciseFile.php
namespace IServ\NachschreibarbeitenBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * IServExerciseBundle:ExerciseFile
 *
 * @ORM\Entity
 * @ORM\Table(name="exercise_files")
 * @ORM\HasLifecycleCallbacks
 */
class ExerciseFile
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var integer
     */
    private $id;

    /**
     * @ORM\Column(name="md5", type="text")
     *
     * @var string $md5
     */
    private $hash;

    /**
     * @ORM\Column(name="file", type="text")
     *
     * @var string $file
     */
    private $title;

    /**
     * @ORM\Column(name="type", type="text")
     *
     * @var string $type
     */
    private $type;

    /**
     * @ORM\Column(name="size", type="integer")
     *
     * @var integer $size
     */
    private $size;

    /**
     * @ORM\Column(name="time", type="datetime")
     *
     * @var \DateTime $time
     */
    private $time;

    /**
     * @ORM\OneToMany(targetEntity="\IServ\ExerciseBundle\Entity\ExerciseSubmission", mappedBy="file")
     *
     * @var ArrayCollection $submissions
     */
    private $submissions;

    /**
     * @ORM\OneToMany(targetEntity="\IServ\ExerciseBundle\Entity\ExerciseAttachment", mappedBy="file")
     *
     * @var ArrayCollection $attachments
     */
    private $attachments;

    /**
     * The constructor
     * Initializes the ArrayCollections
     */
    public function __construct()
    {
        $this->submissions = new ArrayCollection();
        $this->attachments = new ArrayCollection();
    }

    /**
     * Magic call to get the filename
     *
     * @return string
     */
    public function __toString()
    {
        return $this->title;
    }

    /* LIFECYCLE CALLBACKS */

    /**
     * Lifecycle callback to set the upload time
     *
     * @ORM\PrePersist
     */
    public function setCreatedValue()
    {
        $this->time = new \DateTime();
    }

    /**
     * Get the ID of the file
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the ID of the file
     *
     * @param integer $id
     *
     * @return \IServ\NachschreibarbeitenBundle\Entity\ExerciseFile
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get the hash of the file
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Set the hash of the file
     *
     * @param string $hash
     *
     * @return \IServ\NachschreibarbeitenBundle\Entity\ExerciseFile
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
        return $this;
    }

    /**
     * Get the filename
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the filename
     *
     * @param string $title
     *
     * @return \IServ\NachschreibarbeitenBundle\Entity\ExerciseFile
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Get the mimetype of the file
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the mimetype of the file
     *
     * @param string $type
     *
     * @return \IServ\NachschreibarbeitenBundle\Entity\ExerciseFile
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get the size of the file
     *
     * @return integer
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set the size of the file
     *
     * @param integer $size
     *
     * @return \IServ\NachschreibarbeitenBundle\Entity\ExerciseFile
     */
    public function setSize($size)
    {
        $this->size = $size;
        return $this;
    }

    /**
     * Get the time the file was uploaded
     *
     * @return \DateTime
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Set the time the file was uploaded
     *
     * @param \DateTime $time
     *
     * @return \IServ\NachschreibarbeitenBundle\Entity\ExerciseFile
     */
    public function setTime($time)
    {
        $this->time = $time;
        return $this;
    }

    /**
     * Get the submissions associated with the file
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getSubmissions()
    {
        return $this->submissions;
    }

    /**
     * Set the submissions associated with the file
     *
     * @param ArrayCollection $submissions
     *
     * @return \IServ\NachschreibarbeitenBundle\Entity\ExerciseFile
     */
    public function setSubmissions(ArrayCollection $submissions)
    {
        $this->submissions = $submissions;
        return $this;
    }

    /**
     * Add a submission to the file
     *
     * @param ExerciseSubmission $submission
     *
     * @return \IServ\NachschreibarbeitenBundle\Entity\ExerciseFile
     */
    public function addSubmission(ExerciseSubmission $submission)
    {
        $this->submissions->add($submission);
        return $this;
    }

    /**
     * Remove a submission from the file
     *
     * @param ExerciseSubmission $submission
     *
     * @return \IServ\NachschreibarbeitenBundle\Entity\ExerciseFile
     */
    public function removeSubmission(ExerciseSubmission $submission)
    {
        $this->submissions->removeElement($submission);
        return $this;
    }

    /**
     * Get the attachments associated with the file
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * Set the attachments of the file
     *
     * @param ArrayCollection $attachments
     *
     * @return \IServ\NachschreibarbeitenBundle\Entity\ExerciseFile
     */
    public function setAttachments(ArrayCollection $attachments)
    {
        $this->attachments = $attachments;
        return $this;
    }

    /**
     * Add a attachment to the file
     *
     * @param ExerciseAttachment $attachment
     *
     * @return \IServ\NachschreibarbeitenBundle\Entity\ExerciseFile
     */
    public function addAttachment(ExerciseAttachment $attachment)
    {
        $this->attachments->add($attachment);
        return $this;
    }

    /**
     * Remove a attachment from the file
     *
     * @param ExerciseAttachment $attachment
     *
     * @return \IServ\NachschreibarbeitenBundle\Entity\ExerciseFile
     */
    public function removeAttachment(ExerciseAttachment $attachment)
    {
        $this->attachments->removeElement($attachment);
        return $this;
    }
}