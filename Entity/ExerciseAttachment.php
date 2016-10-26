<?php
// src/IServ/ExerciseBundle/Entity/ExerciseAttachment.php
namespace IServ\NachschreibarbeitenBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * IServExerciseBundle:ExerciseAttachment
 *
 * @ORM\Entity
 * @ORM\Table(name="exercise_data")
 */
class ExerciseAttachment
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\IServ\ExerciseBundle\Entity\Exercise", inversedBy="attachments")
     * @ORM\JoinColumn(name="exerciseid", referencedColumnName="id", onDelete="CASCADE")
     * @Assert\NotNull
     *
     * @var \IServ\NachschreibarbeitenBundle\Entity\Exercise
     */
    private $exercise;

    /**
     * @ORM\ManyToOne(targetEntity="\IServ\ExerciseBundle\Entity\ExerciseFile", inversedBy="attachments")
     * @ORM\JoinColumn(name="fileid", referencedColumnName="id", onDelete="CASCADE")
     * @Assert\NotNull
     *
     * @var \IServ\NachschreibarbeitenBundle\Entity\ExerciseFile
     */
    private $file;

    /**
     * @ORM\Column(name="title", type="text")
     *
     * @var string
     */
    private $description;

    /* METHODS */

    /**
     * Magic call to get the attachment name or the filename of the attachment
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Magic call to clone an exercise and reset basic variables
     */
    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
            $this->exercise = null;
        }
    }

    /**
     * Get a string representation of the attachment
     *
     * @return string
     */
    public function getName()
    {
        return $this->file->getTitle();
    }

    /**
     * Get the ID of the attachment
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the ID of the attachment
     *
     * @param integer $id
     *
     * @return \IServ\NachschreibarbeitenBundle\Entity\ExerciseAttachment
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the exercise associated with the attachment
     *
     * @return \IServ\NachschreibarbeitenBundle\Entity\Exercise
     */
    public function getExercise()
    {
        return $this->exercise;
    }

    /**
     * Set the exercise associated with the attachment
     *
     * @param Exercise $exercise
     *
     * @return \IServ\NachschreibarbeitenBundle\Entity\ExerciseAttachment
     */
    public function setExercise(Exercise $exercise)
    {
        $this->exercise = $exercise;

        return $this;
    }

    /**
     * Get the file associated with the attachment
     *
     * @return \IServ\NachschreibarbeitenBundle\Entity\ExerciseFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set the file associated with the attachment
     *
     * @param ExerciseFile $file
     *
     * @return \IServ\NachschreibarbeitenBundle\Entity\ExerciseAttachment
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Get the description of the attachment
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the description of the attachment
     *
     * @param string $description
     *
     * @return \IServ\NachschreibarbeitenBundle\Entity\ExerciseAttachment
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

}