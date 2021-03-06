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

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

use NlpTools\Tokenizers\WhitespaceAndPunctuationTokenizer;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {        
        $activities = $this->getDoctrine()
                           ->getRepository('AppBundle:Activity')
                           ->findAll();
        
        $users = $this->getDoctrine()
                      ->getRepository('AppBundle:User')
                      ->findBy([], ['points' => 'DESC']);
                
        return $this->render('default/index.html.twig', array(
            'activities' => $activities,
            'users' => $users,
        ));
    }
    
    /**
     * @Route("/activity/create", name="create_activity")
     */
    public function createActivityAction(Request $request) {
        $activity = new \AppBundle\Entity\Activity();
        $activity->setDate(new \DateTime("now"));
        
        return $this->processActivity($activity, $request);
    }
    
    /**
     * @Route("/activity/edit/{id}", name="edit_activity")
     */
    public function editActivityAction(Request $request, $id) {
        $activity = $this->getDoctrine()
                         ->getRepository('AppBundle:Activity')
                         ->find($id);
        
        if($activity) {
            return $this->processActivity($activity, $request);
        }
    }
    
    /**
     * @Route("/activity/analyse/{id_activity}", name="analyse_activity")
     */
    public function analyseActivityAction($id_activity) {
        $activity = $this->getDoctrine()
                         ->getRepository('AppBundle:Activity')
                         ->find($id_activity);
        
        $s = "";
        
        $statistics = array();
        
        foreach($activity->getFeedbacks() as $feedback) {
            $analysis = json_decode($feedback->getJsonAnalysis(), true)["document_tone"]["tone_categories"];
            foreach($analysis as $tones) {
                foreach($tones["tones"] as $tone) {
                    if(! array_key_exists($tone["tone_name"], $statistics)) {
                        $statistics[$tone["tone_name"]] = array();
                    }
                    
                    $statistics[$tone["tone_name"]][$feedback->getId()] = $tone["score"];
                }
            }
        }
        
        foreach($statistics as &$stats) {
            asort($stats);
        }
                
        return $this->render('analysis/summary.html.twig', array(
                'statistics' => $statistics,
                'activity' => $activity,
                'sentiment' => array($activity->getSentimentScores(1), $activity->getSentimentScores(-1)),
                'emotions' => $this->getEmotionCounts($activity),
        ));
    }
    
    /**
     * @Route("/activity/tone/{id_activity}/{tone_name}")
     */
    public function analyseToneForActivityAction($id_activity, $tone_name) {
        $activity = $this->getDoctrine()
                         ->getRepository('AppBundle:Activity')
                         ->find($id_activity);
        
        $s = "";
        
        $statistics = array();
        
        foreach($activity->getFeedbacks() as $feedback) {
            $analysis = json_decode($feedback->getJsonAnalysis(), true)["document_tone"]["tone_categories"];
            foreach($analysis as $tones) {
                foreach($tones["tones"] as $tone) {
                    if($tone_name !== $tone["tone_name"]) {
                        continue;
                    }
                    
                    if(! array_key_exists($tone["tone_name"], $statistics)) {
                        $statistics[$tone["tone_name"]] = array(0, 0, 2, -1);
                    }
                    
                    $statistics[$tone["tone_name"]][0] += $tone["score"];
                    $statistics[$tone["tone_name"]][1]++;
                    $statistics[$tone["tone_name"]][2] = min(array(
                        $tone["score"], $statistics[$tone["tone_name"]][2]
                    ));
                    
                    $statistics[$tone["tone_name"]][3] = max(array(
                        $tone["score"], $statistics[$tone["tone_name"]][3]
                    ));                                        
                }
            }
        }
                
        return $this->render('analysis/tone_graph.html.twig', array(
                'statistics' => $statistics,
        ));
    }
    
    /**
     * @Route("/analysis/frequency_list/{id_activity}")
     */
    public function getFrequencyListAction($id_activity) {
        $activity = $this->getDoctrine()
                         ->getRepository('AppBundle:Activity')
                         ->find($id_activity);
               // produce frequency list
        $freq_list = $this->produceFrequencyList($activity);
        
        return $this->render('analysis/word_cloud.html.twig', array(                
                'freq_list' => $freq_list,
                'id_activity' => $id_activity,
        ));
    }
    
    /**
     * @Route("/analysis/compare/{ids_activities}")
     */
    public function compareActivitiesAction($ids_activities) {
        $summary = array();
        $tones = array();
        
        $activities = explode("-", $ids_activities);
        foreach($activities as $id_activity) {
            $a_activity = array();
            
            $activity = $this->getDoctrine()
                             ->getRepository('AppBundle:Activity')
                             ->find($id_activity);
            $a_activity[] = $activity;
            $a_activity[] = $this->produceFrequencyList($activity);
            $a_activity[] = $activity->getSentimentScores(1);
            $a_activity[] = $activity->getSentimentScores(-1);
            foreach($this->getTones($activity) as $tone => $value) {
                $tones[$tone][] = $value;
            }
            $a_activity[] = $this->getEmotionCounts($activity);
            
            $summary[] = $a_activity;
        }
        
        return $this->render('analysis/compare.html.twig', array(
            'summary' => $summary,
            'tones' => $tones,
        ));
    }

    /**
     * @Route("/feedback/add/{id_activity}")
     */
    public function addFeedbackAction(Request $request, $id_activity) {
        $activity = $this->getDoctrine()
                         ->getRepository('AppBundle:Activity')
                         ->find($id_activity);
        
        $feedback = new \AppBundle\Entity\Feedback();
        
        if($activity) {
            return $this->editFeedback($activity, $feedback, $request);
        }
    }

    /**
     * @Route("/feedback/display/{id_feedback}", name="display_feedback")
     */
    public function displayFeedbackAction($id_feedback) {
        $feedback = $this->getDoctrine()
                         ->getRepository('AppBundle:Feedback')
                         ->find($id_feedback);
        
        $tones = array();
        
        $analysis = json_decode($feedback->getJsonAnalysis(), true)["document_tone"]["tone_categories"];
            foreach($analysis as $values) {
                foreach($values["tones"] as $tone) {                    
                    $tones[$tone["tone_name"]] = $tone["score"];
                }
            }
        
        if($feedback) {
            return $this->render('analysis/display_feedback.html.twig', array(
                'feedback' => $feedback,
                'tones' => $tones,
            ));
        }
    }
    
    /**
     * @Route("/analysis/keyword/{id_activity}/{keyword}", name="display_keyword")
     */
    public function displayKeywordAction($id_activity, $keyword) {
        $tokenizer = new WhitespaceAndPunctuationTokenizer();
        $activity = $this->getDoctrine()
                         ->getRepository('AppBundle:Activity')
                         ->find($id_activity);
        $concordances = array();
        $users = array();
        $max_len = 50;
        
        if($activity) {
            foreach($activity->getFeedbacks() as $feedback) {
                $a_text = $tokenizer->tokenize($feedback->getText());
                
                for($i = 0; $i < count($a_text); $i++) {
                    if(strtolower($a_text[$i]) === strtolower($keyword)) {
                        $pos = $i - 1;
                        $prev = "";
                        while($pos >= 0 && strlen($prev . " " . $a_text[$pos]) < $max_len) {
                            $prev = $a_text[$pos] . " " . $prev;
                            $pos--;
                        }
                        
                        $pos = $i + 1;
                        $next = "";
                        while($pos < count($a_text) && strlen($next . " " . $a_text[$pos]) < $max_len) {
                            $next .= (" " . $a_text[$pos]);
                            $pos++;
                        }
                        
                        $concordance = $prev . " <strong>" . $a_text[$i] . "</strong> " . $next;
                        $concordances[] = array($concordance, $feedback->getId());
                        
                        if(! in_array($feedback->getUser(), $users)) {
                            $users[] = $feedback->getUser();
                        }
                    }
                }                
            }
            
            // compute statistics
            $stats = array();
            $stats["gender"] = array();
            foreach($users as $user) {
                if(array_key_exists($user->getGender(), $stats["gender"])) {
                    $stats["gender"][$user->getGender()]++;
                } else {
                    $stats["gender"][$user->getGender()] = 1;
                }
            }
            
            $stats["ethnicity"] = array();
            foreach($users as $user) {
                if(array_key_exists($user->getEthnicOrigin(), $stats["ethnicity"])) {
                    $stats["ethnicity"][$user->getEthnicOrigin()]++;
                } else {
                    $stats["ethnicity"][$user->getEthnicOrigin()] = 1;
                }
            }
            
            return $this->render('analysis/display_keyword.html.twig', array(
                'activity' => $activity,
                'concordances' => $concordances,
                'stats' => $stats,
            ));
        }
    }
    
    /**
     * @Route("/analysis/user/{id}", name="user_details")
     */
    public function getUserDetailsAction($id) {
        $user = $this->getDoctrine()
                     ->getRepository('AppBundle:User')
                     ->find($id);
        
        if(! $user) {
            return $this->redirectToRoute("homepage");
        }
        
        return $this->render('analysis/display_user_details.html.twig', array(
            'user' => $user,
        ));        
    }


    /************************************************************************
     * 
     * Private methods
     * 
     ************************************************************************/
    
    private function getEmotionCounts($activity) {
        $freq_list = $this->produceFrequencyList($activity);
        $emotion_words = array("anger" => array(), "anticipation" => array(),
            "disgust" => array(), "fear" => array(), "joy" => array(),
            "negative" => array(), "positive" => array(), "sadness" => array(),
            "surprise" => array(), "trust" => array());
        
        // read emotions
        $kernel = $this->get('kernel');
        $path = $kernel->locateResource('@AppBundle/Controller/NRC-emotion-lexicon-wordlevel-alphabetized-v0.92.txt');
        $handle = fopen($path, "r");
        if($handle) {
            $in_header = 1;
            while (($line = fgets($handle)) !== false) {
                $pieces = explode("\t", $line);
                
                if(count($pieces) != 3) {
                    continue;
                }
                
                if($pieces && $pieces[0] == "aback") {
                    $in_header = 0;
                }
                
                if($in_header) {
                    continue;
                }
                
                if($pieces[2] == 0) {
                    continue;
                }                
                
                if(array_key_exists($pieces[0], $freq_list) && $freq_list[$pieces[0]] > 1) {
                    $emotion_words[$pieces[1]][] = array($freq_list[$pieces[0]], $pieces[0]);
                }
            }
            fclose($handle);
        }
        
        foreach($emotion_words as &$emotion) {
            arsort($emotion);
        }
        
        return $emotion_words;
    }
    
    private function getTones($activity) {
        $statistics = array();
        
        foreach($activity->getFeedbacks() as $feedback) {
            $analysis = json_decode($feedback->getJsonAnalysis(), true)["document_tone"]["tone_categories"];
            foreach($analysis as $tones) {
                foreach($tones["tones"] as $tone) {                    
                    if(! array_key_exists($tone["tone_name"], $statistics)) {
                        $statistics[$tone["tone_name"]] = array(0, 0, 2, -1);
                    }
                    
                    $statistics[$tone["tone_name"]][0] += $tone["score"];
                    $statistics[$tone["tone_name"]][1]++;
                    $statistics[$tone["tone_name"]][2] = min(array(
                        $tone["score"], $statistics[$tone["tone_name"]][2]
                    ));
                    
                    $statistics[$tone["tone_name"]][3] = max(array(
                        $tone["score"], $statistics[$tone["tone_name"]][3]
                    ));                                        
                }
            }
        }
        
        return $statistics;
    }

    private function processActivity($activity, $request) {
        $form = $this->createFormBuilder($activity)
                     ->add('name', TextType::class)
                     ->add('description', TextareaType::class, array(
                            'attr' => array('row' => 6)))
                     ->add('date', DateType::class)
                     ->add('save', SubmitType::class)
                     ->add('reset', SubmitType::class, array('label' => 'Cancel'))
                     ->getForm();
        
        $form->handleRequest($request);
        
        if($form->isValid()) {
            if($form->get('save')->isClicked()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($activity);
                $em->flush();
            }
            
            return $this->redirectToRoute("homepage");
        } 
        
        return $this->render('admin/activity.html.twig', array(
                'form' => $form->createView(),
        ));
    }
    
    private function editFeedback($activity, $feedback, $request) {
        $form = $this->createFormBuilder($feedback)
                     ->add('text', TextareaType::class, array(
                            'attr' => array('row' => 6)))
                     ->add('date', DateType::class)
                     ->add('save', SubmitType::class)
                     ->add('reset', SubmitType::class, array('label' => 'Cancel'))
                     ->getForm();
        
        $form->handleRequest($request);
        
        if($form->isValid()) {
            if($form->get('save')->isClicked()) {
                $em = $this->getDoctrine()->getManager();
                
                if(! $feedback->getActivity()) {
                    $feedback->setActivity($activity);
                    $activity->addFeedback($feedback);
                }
                //$feedback->setJsonAnalysis($this->container);
                
                $em->persist($feedback);
                $em->persist($activity);
                $em->flush();
            }
            
            return $this->redirectToRoute("fos_user_security_logout");
        }
        
        return $this->render('admin/feedback.html.twig', array(
                'form' => $form->createView(),
                'desc_activity' => $activity->getDescription(),
        ));
    }
    
    private function produceFrequencyList($activity) {
        $tokenizer = new WhitespaceAndPunctuationTokenizer();
        $freq_list = array();
        $stopwords = $this->getStopList();
        
        foreach($activity->getFeedbacks() as $feedback) {
            $a_text = $tokenizer->tokenize($feedback->getText());
            foreach ($a_text as $word) {
                if(array_key_exists(strtolower($word), $stopwords) ||
                   stristr(",.?!'’:“”-_–…()/\\", $word)) {
                    continue;
                }
                
                if(! array_key_exists(strtolower($word), $freq_list)) {
                    $freq_list[strtolower($word)] = 0;
                }
                $freq_list[strtolower($word)]++;
            }
        }
        
        arsort($freq_list, SORT_NUMERIC);
        
        return $freq_list;
    }
    
    private function getStopList() {
        $stopwords = array();
        $kernel = $this->get('kernel');
        $path = $kernel->locateResource('@AppBundle/Controller/english');
        
        $handle = fopen($path, "r");
        if($handle) {
            while (($line = fgets($handle)) !== false) {
                $stopwords[trim($line)] = 1;
            }
        }
        
        return $stopwords;
    }
}
