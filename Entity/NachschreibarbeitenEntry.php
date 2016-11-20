<?php
namespace IServ\NachschreibarbeitenBundle\Entity;
use IServ\CrudBundle\Entity\CrudInterface;
use Doctrine\ORM\Mapping as ORM;


/**
 * NachschreibarbeitenBundle:NachschreibarbeitenEntry
 *
 * @ORM\Entity(repositoryClass="IServ\NachschreibarbeitenBundle\Entity")
 * @ORM\Table(name="mod_nachschreibarbeiten_entries")
 * @ORM\HasLifecycleCallbacks()
 */
class NachschreibarbeitenEntry implements CrudInterface {
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
     * @ORM\JoinColumn(name="created_by_act", referencedColumnName="act", onDelete="CASCADE")
     *
     * @var \IServ\CoreBundle\Entity\User
     */
    private $owner;

    /**
     * @ORM\ManyToOne(targetEntity="\IServ\NachschreibarbeitenBundle\Entity\NachschreibarbeitenDate")
     * @ORM\JoinColumn(name="date_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @var \IServ\NachschreibarbeitenBundle\Entity\NachschreibarbeitenDate
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="\IServ\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="teacher_act", referencedColumnName="act", onDelete="CASCADE")
     *
     * @var User
     */
    private $teacher;

    /**
     * @ORM\ManyToOne(targetEntity="\IServ\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="student_act", referencedColumnName="act", onDelete="CASCADE")
     *
     * @var User
     */
    private $student;

    /**
     * @ORM\Column(name="class", type="text")
     *
     * @var string
     */
    private $class;

    /**
     * @ORM\Column(name="subject", type="text")
     *
     * @var string
     */
    private $subject;

    /**
     * @ORM\Column(name="additional_material", type="text")
     *
     * @var string
     */
    private $additional_material;

    /**
     * @ORM\Column(name="duration", type="integer")
     *
     * @var int
     */
    private $duration;

    /**
     * Gets a string representation of the object.
     *
     * @return string
     */
    public function __toString() {
        return $this->student . ' am ' . $this->date;
    }

    /**
     * Gets a unique ID of the object which can be used to reference the entity in a URI.
     *
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return \IServ\CoreBundle\Security\User
     */
    public function getOwner() {
        return $this->owner;
    }

    /**
     * @param \IServ\NachschreibarbeitenBundle\Entity\User $owner
     */
    public function setOwner($owner) {
        $this->owner = $owner;
    }

    /**
     * @return \IServ\NachschreibarbeitenBundle\Entity\Date
     */
    public function getDate() {
        return $this->date;
    }

    /**
     * @param \IServ\NachschreibarbeitenBundle\Entity\Date $date
     */
    public function setDate($date) {
        $this->date = $date;
    }

    /**
     * @return \IServ\NachschreibarbeitenBundle\Entity\User
     */
    public function getTeacher() {
        return $this->teacher;
    }

    /**
     * @param \IServ\NachschreibarbeitenBundle\Entity\User $teacher
     */
    public function setTeacher($teacher) {
        $this->teacher = $teacher;
    }

    /**
     * @return \IServ\NachschreibarbeitenBundle\Entity\User
     */
    public function getStudent() {
        return $this->student;
    }

    /**
     * @param \IServ\NachschreibarbeitenBundle\Entity\User $student
     */
    public function setStudent($student) {
        $this->student = $student;
    }

    /**
     * @return string
     */
    public function getClass() {
        return $this->class;
    }

    /**
     * @param string $class
     */
    public function setClass($class) {
        $this->class = $class;
    }

    /**
     * @return string
     */
    public function getSubject() {
        return $this->subject;
    }

    /**
     * @param string $subject
     */
    public function setSubject($subject) {
        $this->subject = $subject;
    }

    /**
     * @return string
     */
    public function getAdditionalMaterial() {
        return $this->additional_material;
    }

    /**
     * @param string $additional_material
     */
    public function setAdditionalMaterial($additional_material) {
        $this->additional_material = $additional_material;
    }

    /**
     * @return int
     */
    public function getDuration() {
        return $this->duration;
    }

    /**
     * @param int $duration
     */
    public function setDuration($duration) {
        $this->duration = $duration;
    }
}