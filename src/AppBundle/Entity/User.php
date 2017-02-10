<?php

/*
 * Copyright (C) 2016 dinel
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/* src/AppBundle/Entity/User.php */

namespace AppBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * Description of User
 *
 * @author dinel
 */

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string", length=1024, nullable=true)
     */
    protected $mobile_token;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $points;
    
    /**
     * @ORM\OneToMany(targetEntity="Feedback", mappedBy="user")
     */
    protected $feedbacks;
    
    /**
     * This is used to identify the user. It can be the student number, 
     * full name, or some kind of unique string used 
     * @ORM\Column(type="string", length=1024, nullable=true)
     */
    protected $identifier;
    
    /**
     * @ORM\Column(type="string", length=1024, nullable=true)
     */
    protected $name_of_school;
    
    /**
     * @ORM\Column(type="string", length=1024, nullable=true)
     */
    protected $level_of_study;
    
    /**
     * @ORM\Column(type="string", length=1024, nullable=true)
     */
    protected $title_of_course;
    
    /**
     * @ORM\Column(type="string", length=1024, nullable=true)
     */
    protected $gender;
    
    /**
     * @ORM\Column(type="string", length=1024, nullable=true)
     */
    protected $age;
    
    /**
     * @ORM\Column(type="string", length=1024, nullable=true)
     */
    protected $ethnic_origin;
    
    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     */
    protected $disability;
    
    /**
     * Only the first part of the postcode
     * @ORM\Column(type="string", length=1024, nullable=true)
     */
    protected $postcode;        
    

    public function __construct()
    {
        parent::__construct();
        $this->feedbacks = new \Doctrine\Common\Collections\ArrayCollection();
        $this->points = 0;
    }

    /**
     * Set mobile_token
     *
     * @param string $mobileToken
     * @return User
     */
    public function setMobileToken()
    {
        $this->mobile_token = md5(str_shuffle(date("Y-m-d h:i:sa")  . $this->getUsername()));        

        return $this;
    }

    /**
     * Get mobile_token
     *
     * @return string 
     */
    public function getMobileToken()
    {
        return $this->mobile_token;
    }

    /**
     * Add feedbacks
     *
     * @param \AppBundle\Entity\Feedback $feedbacks
     * @return User
     */
    public function addFeedback(\AppBundle\Entity\Feedback $feedbacks)
    {
        $this->feedbacks[] = $feedbacks;

        return $this;
    }

    /**
     * Remove feedbacks
     *
     * @param \AppBundle\Entity\Feedback $feedbacks
     */
    public function removeFeedback(\AppBundle\Entity\Feedback $feedbacks)
    {
        $this->feedbacks->removeElement($feedbacks);
    }

    /**
     * Get feedbacks
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFeedbacks()
    {
        return $this->feedbacks;
    }

    /**
     * Set points
     *
     * @param integer $points
     * @return User
     */
    public function setPoints($points)
    {
        $this->points = $points;

        return $this;
    }

    /**
     * Get points
     *
     * @return integer 
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * Set identifier
     *
     * @param string $identifier
     *
     * @return User
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Set nameOfSchool
     *
     * @param string $nameOfSchool
     *
     * @return User
     */
    public function setNameOfSchool($nameOfSchool)
    {
        $this->name_of_school = $nameOfSchool;

        return $this;
    }

    /**
     * Get nameOfSchool
     *
     * @return string
     */
    public function getNameOfSchool()
    {
        return $this->name_of_school;
    }

    /**
     * Set levelOfStudy
     *
     * @param string $levelOfStudy
     *
     * @return User
     */
    public function setLevelOfStudy($levelOfStudy)
    {
        $this->level_of_study = $levelOfStudy;

        return $this;
    }

    /**
     * Get levelOfStudy
     *
     * @return string
     */
    public function getLevelOfStudy()
    {
        return $this->level_of_study;
    }

    /**
     * Set titleOfCourse
     *
     * @param string $titleOfCourse
     *
     * @return User
     */
    public function setTitleOfCourse($titleOfCourse)
    {
        $this->title_of_course = $titleOfCourse;

        return $this;
    }

    /**
     * Get titleOfCourse
     *
     * @return string
     */
    public function getTitleOfCourse()
    {
        return $this->title_of_course;
    }

    /**
     * Set gender
     *
     * @param string $gender
     *
     * @return User
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get gender
     *
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set age
     *
     * @param string $age
     *
     * @return User
     */
    public function setAge($age)
    {
        $this->age = $age;

        return $this;
    }

    /**
     * Get age
     *
     * @return string
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * Set ethnicOrigin
     *
     * @param string $ethnicOrigin
     *
     * @return User
     */
    public function setEthnicOrigin($ethnicOrigin)
    {
        $this->ethnic_origin = $ethnicOrigin;

        return $this;
    }

    /**
     * Get ethnicOrigin
     *
     * @return string
     */
    public function getEthnicOrigin()
    {
        return $this->ethnic_origin;
    }

    /**
     * Set disability
     *
     * @param string $disability
     *
     * @return User
     */
    public function setDisability($disability)
    {
        $this->disability = $disability;

        return $this;
    }

    /**
     * Get disability
     *
     * @return string
     */
    public function getDisability()
    {
        return $this->disability;
    }

    /**
     * Set postcode
     *
     * @param string $postcode
     *
     * @return User
     */
    public function setPostcode($postcode)
    {
        $this->postcode = $postcode;

        return $this;
    }

    /**
     * Get postcode
     *
     * @return string
     */
    public function getPostcode()
    {
        return $this->postcode;
    }
}
