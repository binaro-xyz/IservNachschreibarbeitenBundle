<?php
// src/IServ/ExerciseBundle/Entity/ExerciseSubmission.php
namespace IServ\NachschreibarbeitenBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use IServ\CoreBundle\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * IServExerciseBundle:ExerciseSubmission
 *
 * @ORM\Entity
 * @ORM\Table(name="exercise_submissions")
 * @ORM\HasLifecycleCallbacks
 */
class ExerciseSubmission
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
     * @ORM\ManyToOne(targetEntity="\IServ\ExerciseBundle\Entity\Exercise", inversedBy="submissions")
     * @ORM\JoinColumn(name="exerciseid", referencedColumnName="id", onDelete="CASCADE")
     * @Assert\NotNull
     *
     * @var \IServ\NachschreibarbeitenBundle\Entity\Exercise
     */
    private $exercise;

    /**
     * @ORM\ManyToOne(targetEntity="\IServ\ExerciseBundle\Entity\ExerciseFile", inversedBy="submissions")
     * @ORM\JoinColumn(name="fileid", referencedColumnName="id")
     *
     * @var \IServ\NachschreibarbeitenBundle\Entity\ExerciseFile $file
     */
    private $file;

    /**
     * @ORM\ManyToOne(targetEntity="\IServ\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="actuser", referencedColumnName="act", onDelete="CASCADE")
     * @Assert\NotNull
     *
     * @var \IServ\CoreBundle\Entity\User $user
     */
    private $user;

    /**
     * @ORM\Column(name="time", type="datetime")
     *
     * @var \DateTime $time
     */
    private $time;

    /**
     * @ORM\Column
     *
     * @var $string
     */
    private $text;

    /**
     * ExerciseSubmission constructor.
     *
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        if ($data)
        {
            foreach ($data as $field => $value) {
                $this->$field = $value;
            }
        }
    }

    /**
     * Magic call to get the entity name
     */
    public function __toString()
    {
        return $this->getFile() !== null ? $this->getFile()->getTitle() : $this->getText();
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
     * Get the ID of the submission
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the ID of the submission
     *
     * @param integer $id
     *
     * @return \IServ\NachschreibarbeitenBundle\Entity\ExerciseSubmission
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get the exercise associated with the submission
     *
     * @return \IServ\NachschreibarbeitenBundle\Entity\Exercise
     */
    public function getExercise()
    {
        return $this->exercise;
    }

    /**
     * Set the exercise associated with the submission
     *
     * @param Exercise $exercise
     *
     * @return \IServ\NachschreibarbeitenBundle\Entity\ExerciseSubmission
     */
    public function setExercise(Exercise $exercise)
    {
        $this->exercise = $exercise;
        return $this;
    }

    /**
     * Get the file associated with the submission
     *
     * @return \IServ\NachschreibarbeitenBundle\Entity\ExerciseFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set the file associated with the submission
     *
     * @param ExerciseFile $file
     *
     * @return \IServ\NachschreibarbeitenBundle\Entity\ExerciseSubmission
     */
    public function setFile(ExerciseFile $file)
    {
        $this->file = $file;
        return $this;
    }

    /**
     * Get the user that submitted the file
     *
     * @return \IServ\CoreBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the user that submitted the file
     *
     * @param User $user
     *
     * @return \IServ\NachschreibarbeitenBundle\Entity\ExerciseSubmission
     */
    public function setUser(User $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get the time of the submission
     *
     * @return \DateTime
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Set the time of the submission
     *
     * @param \DateTime $time
     *
     * @return \IServ\NachschreibarbeitenBundle\Entity\ExerciseSubmission
     */
    public function setTime($time)
    {
        $this->time = $time;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param mixed $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }
}