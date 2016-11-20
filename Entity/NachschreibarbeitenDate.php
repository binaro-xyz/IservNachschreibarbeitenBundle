<?php

namespace IServ\NachschreibarbeitenBundle\Entity;


use IServ\CrudBundle\Entity\CrudInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * NachschreibarbeitenBundle:NachschreibarbeitenDate
 *
 * @ORM\Entity(repositoryClass="IServ\NachschreibarbeitenBundle\Entity")
 * @ORM\Table(name="mod_nachschreibarbeiten_dates")
 * @ORM\HasLifecycleCallbacks()
 */
class NachschreibarbeitenDate implements CrudInterface {
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
   * @var User
   */
  private $owner;

  /**
   * @ORM\Column(name="room", type="text")
   *
   * @var string
   */
  private $room;

  /**
   * @ORM\Column(name="date", type="date")
   *
   * @var \DateTime
   */
  private $date;

  /**
   * @ORM\Column(name="time", type="time")
   *
   * @var \DateTime
   */
  private $time;

  /**
   * @ORM\ManyToOne(targetEntity="\IServ\CoreBundle\Entity\User")
   * @ORM\JoinColumn(name="teacher_act", referencedColumnName="act", onDelete="CASCADE")
   *
   * @var User
   */
  private $teacher;

  /**
   * Gets a string representation of the object.
   *
   * @return string
   */
  public function __toString() {
    return $this->date->format('Y-m-d') . ' ' . $this->time->format('H:i');
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
   * @return \IServ\NachschreibarbeitenBundle\Entity\User
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
   * @return string
   */
  public function getRoom() {
    return $this->room;
  }

  /**
   * @param string $room
   */
  public function setRoom($room) {
    $this->room = $room;
  }

  /**
   * @return \DateTime
   */
  public function getDate() {
    return $this->date;
  }

  /**
   * @param \DateTime $date
   */
  public function setDate($date) {
    $this->date = $date;
  }

  /**
   * @return \DateTime
   */
  public function getTime() {
    return $this->time;
  }

  /**
   * @param \DateTime $time
   */
  public function setTime($time) {
    $this->time = $time;
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
}