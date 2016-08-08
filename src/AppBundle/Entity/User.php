<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

// src/AppBundle/Entity/User.php

namespace AppBundle\Entity;

use FOS\UserBundle\Entity\User as BaseUser;
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
}
