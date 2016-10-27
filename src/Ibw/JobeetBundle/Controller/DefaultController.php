<?php

namespace Ibw\JobeetBundle\Controller;

use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookSession;
use Ibw\JobeetBundle\Entity\Client;
use Ibw\JobeetBundle\Entity\User;
use Ibw\JobeetBundle\Services\SmsMailer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Swift_Message;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Facebook\FacebookRequest;
use Facebook\GraphUser;
use Facebook\FacebookRequestException;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('IbwJobeetBundle:Default:index.html.twig', array('name' => $name));
    }

    public function loginAction()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $session = $request->getSession();

        // get the login error if there is one
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }

        return $this->render('IbwJobeetBundle:Default:login.html.twig', array(
            // last username entered by the user
            'last_username' => $session->get(SecurityContext::LAST_USERNAME),
            'error'         => $error,
        ));
    }

    public function sendMailAction(){

        $a = 1 ;
        try{
            $message = Swift_Message::newInstance()
                ->setSubject('Contact enquiry from symblog')
                ->setFrom('vasiltia.chiril@gmail.com')
                ->setTo('everest0corp@gmail.com')
                ->setBody($this->renderView('IbwJobeetBundle:Default:mail.txt.twig' ));

            $this->get('mailer')->send($message);
        } catch(Exception $e){
            print_r($e->getMessage());
        }

        //$this->get('session')->setFlash('blogger-notice', 'Your contact enquiry was successfully sent. Thank you mf!');

        // Redirect - This is important to prevent users re-posting
        // the form if they refresh the page
        return $this->redirect($this->generateUrl('ibw_job'));
    }

    public function changeLanguageAction()
    {
        $language = $this->container->get('request_stack')->getCurrentRequest()->get('language');
        return $this->redirect($this->generateUrl('ibw_job', array('_locale' => $language)));
    }

    public function facebookLoginAction()
    {
        $appId      = $this->container->getParameter('facebookID');
        $appSecret  = $this->container->getParameter('facebookSecret');

        FacebookSession::setDefaultApplication($appId ,$appSecret);

        $route = $this->get("router")->generate("facebook_login", [], true);
        $helper = new FacebookRedirectLoginHelper($route);

        $session = null;

        try {
            $session = $helper->getSessionFromRedirect();
            $accessTokenExpDate = $session->getAccessToken()->extend()->getExpiresAt()->format('d-m-y H:m:s');

            $token = $session->getToken();
        } catch(FacebookRequestException $ex) {
            // When Facebook returns an error
            echo 'fb ex : '.$ex->getMessage();
        } catch(\Exception $ex) {
            // When validation fails or other local issues
            echo 'ex : '.$ex->getMessage();
        }
        $buff = array();

        if($session){
            try{
                $user_profile = (new FacebookRequest(
                    $session, 'GET', '/me'
                ))->execute()->getGraphObject(GraphUser::className());

                $buff['userName'] =  $user_profile->getFirstName();
                $buff['userSurname'] = $user_profile->getLastName();
                $buff['userId'] =  $user_profile->getId();

                $request = new FacebookRequest(
                    $session,
                    'GET',
                    '/me/picture'
                );
                $response = $request->execute();
                $graphObject = $response->getGraphObject(GraphUser::className());



            } catch(Exception $e){
                print_r($e->getMessage());
            }
        }
        var_dump($buff, $graphObject);


        return $this->render('IbwJobeetBundle:Default:index.html.twig');
    }

    public function facebookRedirectAction(){

        $appId      = $this->container->getParameter('facebookID');
        $appSecret  = $this->container->getParameter('facebookSecret');

        FacebookSession::setDefaultApplication($appId ,$appSecret);

        $route = $this->get('router')->generate('facebook_login', [], true);
        $helper = new FacebookRedirectLoginHelper($route, $appId = null, $appSecret = null);
        $newURL = $helper->getLoginUrl();

        return $this->redirect($newURL,302);
    }

    public function smsAction(){

        $message = Swift_Message::newInstance()
            ->setSubject('Contact enquiry from symblog')
            ->setFrom('.@gmail.com')
            ->setTo('@gmail.com')
            ->setBody($this->renderView('IbwJobeetBundle:Default:mail.txt.twig' ));

        $user = new Client();
        $user->setUsername("test");
        $user->setEmail(".@gmail.com");
        $user->setPassword("test");
        $user->setPhoneNumber("+");

        try{
            $smsMailer = new SmsMailer($user, $message, false, "+37368024060", ".@gmail.com" );
            $smsMailer->sendSMS($user, "test message", "StarSoft");
        } catch(Exception $e){
            print_r($e->getMessage());
        }

        return $this->render("IbwBlogBundle:Default:index.html.twig");
    }
}
