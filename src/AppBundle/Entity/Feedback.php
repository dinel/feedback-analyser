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

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Feedback
 *
 * @author dinel
 */

/**
 * @ORM\Entity
 * @ORM\Table(name="feedback")
 */
class Feedback {
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="text")
     */
    protected $text;  
        
    /**
     * @ORM\Column(type="date")
     */
    protected $date;
    
    /**
     * @ORM\ManyToOne(targetEntity="Activity", inversedBy="feedbacks")
     * @ORM\JoinColumn(name="activity_id", referencedColumnName="id")
     */
    protected $activity;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="feedbacks")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;
    
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $jsonAnalysis;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $positive_score;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $negative_score;
        
    private $header = array("Content-type: application/json", "X-Watson-Learning-Opt-Out: 1");
    private $url = "https://gateway.watsonplatform.net/tone-analyzer/api/v3/tone?version=2016-05-19&sentences=false";

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set text
     *
     * @param string $text
     * @return Feedback
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string 
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Feedback
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set activity
     *
     * @param \AppBundle\Entity\Activity $activity
     * @return Feedback
     */
    public function setActivity(\AppBundle\Entity\Activity $activity = null)
    {
        $this->activity = $activity;

        return $this;
    }

    /**
     * Get activity
     *
     * @return \AppBundle\Entity\Activity 
     */
    public function getActivity()
    {
        return $this->activity;
    }
    
    /**
     * Set user
     */
    public function setUser(\AppBundle\Entity\User $user = null) {
        $this->user = $user;
        
        return $this;
    }

    /**
     * Set jsonAnalysis
     *
     * @return Feedback
     */
    public function setJsonAnalysis($container)
    {
        $conn = curl_init();
        
        curl_setopt($conn, CURLOPT_HTTPHEADER, $this->header); 
        $user_pass = $container->getParameter('watson_user') . ":"
                . $container->getParameter('watson_passwd');
        curl_setopt($conn, CURLOPT_USERPWD, $user_pass);
        curl_setopt($conn, CURLOPT_POST, 1);
        curl_setopt($conn, CURLOPT_URL, $this->url);
        curl_setopt($conn, CURLOPT_RETURNTRANSFER, 1);
        
        $body = json_encode(array("text" => $this->getText()));        
        curl_setopt($conn, CURLOPT_POSTFIELDS, $body);
        
        $this->jsonAnalysis = curl_exec($conn);
        curl_close($conn);

        return $this;
    }

    /**
     * Get jsonAnalysis
     *
     * @return string 
     */
    public function getJsonAnalysis()
    {
        return $this->jsonAnalysis;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set positive_score
     *
     * @param integer $positiveScore
     * @return Feedback
     */
    public function setPositiveScore()
    {
        $tmp_file_name = tempnam("/tmp", "FB");
        $handle = fopen($tmp_file_name, "w");
        if($handle) {
            fwrite($handle, $this->getText());
            fclose($handle);

            $output = popen("cat " . $tmp_file_name 
                    .  " | java -jar /home/dinel/Projects/feedback_analyser/extra/SentiStrengthCom.jar " 
                    . "sentidata /home/dinel/Projects/feedback_analyser/extra/SentStrength_Data/ stdin " 
                    , "r");
            $read = fread($output, 1024);
            $values = explode("\t", $read);
            $this->positive_score = $values[0];
            $this->negative_score = $values[1];
        }
    }

    /**
     * Get positive_score
     *
     * @return integer 
     */
    public function getPositiveScore()
    {
        if(($this->positive_score === NULL) || ($this->negative_score === NULL)) {
            $this->setPositiveScore();
        }
        return $this->positive_score;
    }

    /**
     * Set negative_score
     *
     * @param integer $negativeScore
     * @return Feedback
     */
    public function setNegativeScore()
    {
        $this->setPositiveScore();

        return $this;
    }

    /**
     * Get negative_score
     *
     * @return integer 
     */
    public function getNegativeScore()
    {
        if(($this->positive_score === NULL) || ($this->negative_score === NULL)) {
            $this->setPositiveScore();
        }
        
        return abs($this->negative_score);
    }
}
