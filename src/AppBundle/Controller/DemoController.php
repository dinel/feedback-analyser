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

/**
 * Description of DemoController
 *
 * @author dinel
 */
class DemoController extends Controller
{
    /**
     * @Route("/demo/create_users")
     */
    public function createDemoUsersAction() {
        $em = $this->getDoctrine()->getManager();
        for($i = 0; $i < 50; $i++) {
            $user = new \AppBundle\Entity\User();
            $user->setUserName("user" . ($i + 1));
            $user->setPassword("1234567");  
            $user->setEmail("user" . ($i + 1) . "@u.com");
            $em->persist($user);
            $em->flush();
        }
        
        return $this->redirectToRoute("homepage");
    }
    
    /**
     * @Route("/demo/import_texts/{id_activity}")
     */
    public function importTextAction($id_activity) {
        set_time_limit(0);
        
        $activity = $this->getDoctrine()
                         ->getRepository('AppBundle:Activity')
                         ->find($id_activity);
        
        $handle = fopen("/home/dinel/Projects/feedback_analyser/text.txt", "r");
        if ($handle) {
            $counter = 0;
            $user_ids = range(2,51);
            shuffle($user_ids);
            $em = $this->getDoctrine()->getManager();
            while (($line = fgets($handle)) !== false) {
                $feedback = new \AppBundle\Entity\Feedback();
                
                $user = $this->getDoctrine()
                     ->getRepository('AppBundle:User')
                     ->find($user_ids[$counter++]);
                $feedback->setText($line);
                $feedback->setUser($user);                
                $feedback->setActivity($activity);
                $feedback->setDate(new \DateTime("now"));
                $feedback->setJsonAnalysis($this->container);
                $activity->addFeedback($feedback);
                
                $em->persist($feedback);
                $em->persist($activity);
                $em->flush();                
            }

            fclose($handle);
        }
        
        return $this->redirectToRoute("homepage");        
    }
}
