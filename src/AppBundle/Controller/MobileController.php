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
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Description of MobileController
 *
 * @author dinel
 */
class MobileController extends Controller {
    /**
     * @Route("/user/auth/{email}/{pass}", name="user_auth")
     */
    public function loginAction(Request $request, $email, $pass)            
    {
        if(is_null($email) || is_null($pass)) {
            return new JsonResponse(array(
                "success" => "No",
                "message" => "Invalid email or password",
            ));
        }
        
        $user_manager = $this->get('fos_user.user_manager');
        $factory = $this->get('security.encoder_factory');

        $user = $user_manager->findUserByEmail($email);
        
        if($user) {
            $encoder = $factory->getEncoder($user);
            $salt = $user->getSalt();

            if($encoder->isPasswordValid($user->getPassword(), $pass, $salt)) {
                $user->setMobileToken();
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
                
                return new JsonResponse(array(
                    "success" => "YES",
                    "message" => 'Welcome '. $user->getUsername(),
                    "points" => $user->getPoints(),
                    "token" => $user->getMobileToken(),
                    "user_id" => $user->getId(),
                ));            
            } 
        }
        
        return new JsonResponse(array(
            "success" => "No",
            "message" => "Invalid email or password",
        ));                
    }
    
    /**
     * @Route("/user/submit_feedback/{id_user}/{token}/{id_activity}/{text}")
     */
    public function submitFeedbackAction(Request $request, $id_user, $token, $id_activity, $text) {
        $user = $this->getDoctrine()
                     ->getRepository('Application\Sonata\UserBundle\Entity\User')
                     ->find($id_user);
        
        if($user && $user->getMobileToken() === $token) {
            $activity = $this->getDoctrine()
                         ->getRepository('AppBundle:Activity')
                         ->find($id_activity);
        
            $feedback = new \AppBundle\Entity\Feedback();
            $feedback->setText($text);
            $feedback->setActivity($activity);
            $feedback->setDate(new \DateTime("now"));
            $feedback->setUser($user);
            $activity->addFeedback($feedback);
            $user->setPoints($user->getPoints() + 1);
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($feedback);
            $em->persist($activity);
            $em->persist($user);
            $em->flush();
            
            return new JsonResponse(array(
                'success' => 'yes',
            ));
        }
        
        return new JsonResponse(array(
            'success' => 'no',
        ));
    }
    
    /**
     * @Route("/user/retrieve/{id_user}/{token}")
     */
    public function retrieveActivityDetailsAction(Request $request, $id_user, $token) {
        $activity = $this->getDoctrine()
                         ->getRepository('AppBundle:Activity')
                         ->findAll()[0];
        
        // TODO: check the token
        
        return new JsonResponse(array(
            'title' => $activity->getDescription(),
            'id' => $activity->getId(),
        ));
        
    }
}
