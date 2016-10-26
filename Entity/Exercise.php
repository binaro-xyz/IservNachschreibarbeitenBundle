<?php
// src/IServ/ExerciseBundle/Entity/Exercise.php
namespace IServ\NachschreibarbeitenBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use IServ\CoreBundle\Entity\Group;
use IServ\CoreBundle\Entity\User;
use IServ\CrudBundle\Entity\CrudInterface;
use IServ\NachschreibarbeitenBundle\Validator\Constraints as ExerciseAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ExerciseBundle:Exercise
 *
 * @ORM\Entity(repositoryClass="IServ\ExerciseBundle\Entity\ExerciseRepository")
 * @ORM\Table(name="exercises")
 * @ORM\HasLifecycleCallbacks()
 * @ExerciseAssert\DateRange
 */
class Exercise implements CrudInterface
{
    const TYPE_FILES = 'files';
    const TYPE_TEXT = 'text';

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\IServ\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="owner", referencedColumnName="act", onDelete="CASCADE")
     *
     * @var User
     */
    private $owner;

    /**
     * @ORM\Column(name="title", type="text")
     * @Assert\NotBlank
     *
     * @var string
     */
    private $title;

    /**
     * @ORM\Column(name="text", type="text")
     * @Assert\NotBlank
     *
     * @var string
     */
    private $text;

    /**
     * @ORM\Column(name="startDate", type="datetime")
     *
     * @var \DateTime
     */
    private $startDate;

    /**
     * @ORM\Column(name="endDate", type="datetime")
     *
     * @var \DateTime
     */
    private $endDate;

    /**
     * @ORM\Column(name="tolerance", type="integer")
     *
     * @var int
     */
    private $tolerance;

    /**
     * @ORM\Column(name="dateAdded", type="datetime")
     *
     * @var \DateTime
     */
    private $dateAdded;

    /**
     * @ORM\ManyToMany(targetEntity="\IServ\CoreBundle\Entity\Group")
     * @ORM\JoinTable(name="exercise_participants",
     *      joinColumns={@ORM\JoinColumn(name="exerciseid", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="actgrp", referencedColumnName="act")}
     * )
     *
     * @var ArrayCollection|Group[]
     */
    private $participants;

    /**
     * @ORM\OneToMany(targetEntity="\IServ\ExerciseBundle\Entity\ExerciseAttachment", mappedBy="exercise")
     *
     * @var ArrayCollection|ExerciseAttachment[]
     */
    private $attachments;

    /**
     * @ORM\OneToMany(targetEntity="\IServ\ExerciseBundle\Entity\ExerciseSubmission", mappedBy="exercise")
     *
     * @var ArrayCollection|ExerciseSubmission[]
     */
    private $submissions;

    /**
     * @ORM\Column(name="type", type="text")
     *
     * @var string
     */
    private $type = self::TYPE_FILES;

    /* METHODS */

    /**
     * The constructor
     * Initializes the ArrayCollections
     */
    public function __construct()
    {
        $this->participants = new ArrayCollection();
        $this->attachments = new ArrayCollection();
        $this->submissions = new ArrayCollection();
    }

    /**
     * Magic call to get the title of the exercise
     *
     * @return string
     */
    public function __toString()
    {
        return $this->title;
    }

    /**
     * Magic call to clone an exercise and reset basic variables
     */
    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
            $this->startDate = null;
            $this->endDate = null;
            $this->dateAdded = new \DateTime();
            $this->participants = new ArrayCollection();
            $this->submissions = new ArrayCollection();
        }
    }

    /**
     * Checks if we're in files mode
     *
     * @return bool
     */
    public function isFilesType()
    {
        return $this->type === self::TYPE_FILES;
    }

    /**
     * Checks if we're in text mode
     *
     * @return bool
     */
    public function isTextType()
    {
        return $this->type === self::TYPE_TEXT;
    }

    /**
     * Gets submissions as an array sorted by user and file names
     *
     * @return array
     */
    public function getSubmissionsSorted()
    {
        $subs = $this->submissions->toArray();

        uasort(
            $subs,
            function ($a, $b) {
                /* @var ExerciseSubmission $a */
                /* @var ExerciseSubmission $b */
                $nat = strnatcmp(strtolower($a->getUser()), strtolower($b->getUser()));

                if (0 === $nat) {
                    return strnatcmp(strtolower($a->getFile()), strtolower($b->getFile()));
                } else {
                    return $nat;
                }
            }
        );

        return $subs;
    }

    /**
     * Returns true if object is valid, i.e. start and end date are set. false otherwise
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->getStartDate() !== null
        && $this->getEndDate() !== null;
    }

    /* LIFECYCLE CALLBACKS */

    /**
     * Lifecycle callback to set the creation date
     *
     * @ORM\PrePersist
     */
    public function setCreatedValue()
    {
        $this->dateAdded = new \DateTime();
        $this->tolerance = 3;
    }

    /* GETTER AND SETTERS */

    /**
     * Get the ID of the exercise
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the ID of the exercise
     *
     * @param integer $id
     *
     * @return Exercise
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the owner of the exercise
     *
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set the owner of the exercise
     *
     * @param User $owner
     *
     * @return Exercise
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get the title of the exercise
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the title of the exercise
     *
     * @param string $title
     *
     * @return Exercise
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the text (description) of the exercise
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set the text (description) of the exercise
     *
     * @param string $text
     *
     * @return Exercise
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get the start date of the exercise
     *
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set the start date of the exercise
     *
     * @param \DateTime $startDate
     *
     * @return Exercise
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get the end date of the exercise
     *
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set the end date of the exercise
     *
     * @param \DateTime $endDate
     *
     * @return Exercise
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get the tolerance the exercise still accepts submissions
     *
     * @return integer
     */
    public function getTolerance()
    {
        return !empty($this->tolerance) ? $this->tolerance : 3;
    }

    /**
     * Set the tolerance the exercise still accepts submissions
     *
     * @param integer $tolerance
     *
     * @return Exercise
     */
    public function setTolerance($tolerance)
    {
        $this->tolerance = $tolerance;

        return $this;
    }

    /**
     * Get the creation date of the exercise
     *
     * @return \DateTime
     */
    public function getDateAdded()
    {
        return $this->dateAdded;
    }

    /**
     * Set the creation date of the exercise
     *
     * @param \DateTime $dateAdded
     *
     * @return Exercise
     */
    public function setDateAdded($dateAdded)
    {
        $this->dateAdded = $dateAdded;

        return $this;
    }

    /**
     * Get the participants of the exercise
     *
     * @return ArrayCollection
     */
    public function getParticipants()
    {
        return $this->participants;
    }

    /**
     * Check if the a group is a participant
     *
     * @param Group $group
     *
     * @return boolean
     */
    public function hasParticipant(Group $group)
    {
        foreach ($this->participants as $participant) {
            if ($participant == $group) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add a participant to the exercise
     *
     * @param Group $group
     *
     * @return Exercise
     */
    public function addParticipant(Group $group)
    {
        $this->participants->add($group);

        return $this;
    }

    /**
     * Remove a participant from the exercise
     *
     * @param Group $group
     *
     * @return Exercise
     */
    public function removeParticipant(Group $group)
    {
        $this->participants->removeElement($group);

        return $this;
    }

    /**
     * Get the attachment of the exercise
     *
     * @return ArrayCollection|ExerciseAttachment[]
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * Add a attachment to the exercise
     *
     * @param ExerciseAttachment $attachment
     *
     * @return Exercise
     */
    public function addAttachment(ExerciseAttachment $attachment)
    {
        $attachment->setExercise($this);
        $this->attachments->add($attachment);

        return $this;
    }

    /**
     * Remove a attachment from the exercise
     *
     * @param ExerciseAttachment $attachment
     *
     * @return Exercise
     */
    public function removeAttachment(ExerciseAttachment $attachment)
    {
        $this->attachments->removeElement($attachment);

        return $this;
    }

    /**
     * Get the submissions of the Exercise
     *
     * @return ArrayCollection|ExerciseSubmission[]
     */
    public function getSubmissions()
    {
        return $this->submissions;
    }

    /**
     * Check if the user has submissions
     *
     * @return bool
     */
    public function hasSubmissions()
    {
        return (!$this->submissions->isEmpty());
    }

    /**
     * Add a submission to the exercise
     *
     * @param ExerciseSubmission $submission
     *
     * @return Exercise
     */
    public function addSubmission(ExerciseSubmission $submission)
    {
        $submission->setExercise($this);
        $this->submissions->add($submission);

        return $this;
    }

    /**
     * Remove a submission from the exercise
     *
     * @param ExerciseSubmission $submission
     *
     * @return Exercise
     */
    public function removeSubmission(ExerciseSubmission $submission)
    {
        $this->submissions->removeElement($submission);

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        if ($type !== 'files' && $type !== 'text') {
            throw new \InvalidArgumentException('Unsupported type!');
        }

        $this->type = $type;
    }

}
