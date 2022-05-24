<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 22.02.2016
 * Time: 18:46
 */

namespace Makrosum\MeetingBundle\Controller;

use Makrosum\MeetingBundle\Abstractions\BaseController;
use Makrosum\MeetingBundle\Entity\Activation;
use Makrosum\MeetingBundle\Entity\User;
use Makrosum\MeetingBundle\Entity\UserSettings;
use Makrosum\MeetingBundle\Entity\Guest;
use Makrosum\MeetingBundle\MeetingBundle;

class AuthenticationController extends BaseController
{

    public function forgotPasswordAction()
    {
        $this->renderBase();

        return $this->renderIt("MeetingBundle:Authentication:forgot_password.html.twig");
    }

    public function resetPasswordAction()
    {
        $this->renderBase();

        if(!$this->request->get("email")){
            return $this->redirectToRoute("forgot_password", ["prompt" => "string.type_an_email"]);
        }

        $User = $this->entity(User::class)->findOneBy([
            "email" => $this->request->get("email")
        ]);

        if(!$User){
            return $this->redirectToRoute("forgot_password", ["prompt" => "string.no_user_found"]);
        }

        $UserSettings = $this->entity(UserSettings::class)->findOneBy([
            "user_id" => $User->getId()
        ]);

        $TemporaryPasswordPlain = rand(1000000000, 9999999999);

        $User->setPlainPassword($TemporaryPasswordPlain);
        $newPassword = $this->get("security.password_encoder")->encodePassword($User, $User->getPlainPassword());
        $User->setPassword($newPassword);

        $this->em_flush();

        if($User){

            $SmsNotification = $this->translator->trans("string.reset_password_sms", [
                "%password%" => $TemporaryPasswordPlain
            ], "security", $UserSettings->getLanguage());

            $SmsStream = $this->get("MakrosumNotificationStream.SMS");
            $SmsStream->sendToGroup($SmsNotification, [substr($User->getPhone(), -10)]);

            return $this->redirectToRoute("login_www", ["prompt" => "string.check_your_sms_reset_password"]);

        }

        return $this->redirectToRoute("forgot_password", ["prompt" => "string.system_error"]);
    }

    public function loginAction(){

        $this->renderBase();

        $authenticationUtils = $this->get("security.authentication_utils");

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $this->data([
            "lastUsername" => $lastUsername,
            "promt_error" => $error
        ]);

        return $this->renderIt("MeetingBundle:Authentication:login.html.twig");
    }

    public function registerAction()
    {
        $this->renderBase();

        $this->data([
            "email" => null,
            "fullname" => null,
            "selected_country" => "*",
            "selected_province" => "*",
            "selected_city" => "*",
            "countries" => [],
            "provinces" => [],
            "cities" => [],
            "reg" => "register"
        ]);

        $countries = $this->getDoctrine()->getRepository("MeetingBundle:Country")->findAll();
        foreach($countries as $country){
            $selected = false;
            $this->data([
                "countries" => [
                    $country->getLocale() => [
                        "code" => $country->getCode(),
                        "name" => $country->getName(),
                        "selected" => $selected
                    ]
                ]
            ]);
        }

        if($this->request->get("country")&&$this->request->get("country")!="*"){
            $provinces = $this->getDoctrine()->getRepository("MeetingBundle:Province")->findBy([
                "country_code" => $this->request->get("country")
            ]);
            foreach($provinces as $province){
                $selected = false;
                $this->data([
                    "provinces" => [
                        $province->getId() => [
                            "area" => $province->getArea(),
                            "name" => $province->getName(),
                            "selected" => $selected
                        ]
                    ]
                ]);
            }
        }

        if($this->request->get("province")&&$this->request->get("province")!="*"){
            $cities = $this->getDoctrine()->getRepository("MeetingBundle:City")->findBy([
                "country_code" => $this->request->get("country"), "province_code" => $this->request->get("province")
            ]);
            foreach($cities as $city){
                $selected = false;
                $this->data([
                    "cities" => [
                        $city->getId() => [
                            "area" => $city->getArea(),
                            "name" => $city->getName(),
                            "selected" => $selected
                        ]
                    ]
                ]);
            }
        }

        if($this->request->get("register")){

            $process = true;
            $process_message = null;

            $checkUserExistWithEmail = $this->entity(User::class)->findOneBy([
                "email" => $this->request->get("email")
            ]);

            if($checkUserExistWithEmail){
                $process = false;
                $process_message = "string.email_already_taken";
            }else{
                $newUser = new User();
                $newUser->setRole(MeetingBundle::ROLE_USER);
                $newUser->setFullname($this->request->get("fullname"));
                $newUser->setEmail($this->request->get("email"));
                $newUser->setCountry($this->request->get("country"));
                $newUser->setProvince($this->request->get("province"));
                $newUser->setCity($this->request->get("city"));
                $newUser->setPlainPassword($this->getRequest()->get("password"));
                $newPassword = $this->get("security.password_encoder")->encodePassword($newUser, $newUser->getPlainPassword());
                $newUser->setPassword($newPassword);

                $newUser->setIsConfirmedEmail(false);
                $newUser->setIsConfirmedPhone(false);
                $newUser->setIsActive(false);

                $this->em()->em_persist($newUser)->em_flush();
                $this->em_clear();

                if(!$newUser){
                    $process = false;
                    $process_message = "string.not_registered";
                }else{

                    $newUserSettings = new UserSettings();
                    $newUserSettings->setUserId($newUser->getId());
                    $newUserSettings->setLanguage("tr");
                    $newUserSettings->setEmailNotification(true);
                    $newUserSettings->setSmsNotification(true);
                    $this->em()->em_persist($newUserSettings)->em_flush();
                    $this->em_clear();

                    $newActivation = new Activation();
                    $newActivation->setUser($newUser);
                    $newActivation->setToken(md5($newUser->getId().$newUser->getEmail().$newUser->getId().$newUser->getPassword()));

                    $this->em()->em_persist($newActivation)->em_flush();
                    $this->em_clear();

                    $message = \Swift_Message::newInstance()
                        ->setSubject($this->translator->trans("string.activation_title", [], "notification", $this->param("default_locale")))
                        ->setFrom(array($this->param("mailer_sender") => $this->param("global.project_name")))
                        ->setTo($newUser->getEmail())
                        ->setBody(
                            $this->renderView(
                                "MeetingBundle:Emails:activation.html.twig",
                                [
                                    "trans_l" => $this->getLocale(),
                                    "base_hostname" => $this->baseHostname,
                                    "tokenKey" => $newActivation->getToken(),
                                    "fullname" => $this->request->get("fullname"),
                                    "email" => $this->request->get("email"),
                                    "path" => "/global/activation/ap/",
                                    "hostname" => $this->param("hostname"),
                                    "language" => $this->param("default_locale"),
                                    "activation" => $this->translator->trans("string.activation", [], "notification", $this->param("default_locale")),
                                    "base_server_url" => $this->protocol_scheme."://".$this->baseHostname,
                                    "project_name"=> $this->param("global.project_name"),
                                    "social" => [
                                        "facebook" => $this->param("global.social_facebook"),
                                        "twitter" => $this->param("global.social_twitter"),
                                        "instagram" => $this->param("global.social_instagram"),
                                        "google" => $this->param("global.social_google"),
                                        "youtube" => $this->param("global.social_youtube")
                                    ]
                                ]
                            ), "text/html"
                        );

                    $this->get("mailer")->send($message);

                    if($this->request->get("guest_token")&&strlen($this->request->get("guest_token"))>10){
                        $Guest = $this->entity(Guest::class)->findOneBy([
                            "token_key" => $this->request->get("guest_token")
                        ]);
                        if($Guest){
                            $Guest->setStatus(1);
                            $this->em_flush();
                        }
                    }
                }
            }

            if(!$process){
                $this->renderData["fullname"] = $this->request->get("fullname");
                $this->renderData["email"] = $this->request->get("email");
                $this->renderData["selected_country"] = $this->request->get("country");
                $this->renderData["selected_province"] = $this->request->get("province");
                $this->renderData["selected_city"] = $this->request->get("city");
                $this->renderData["promt_error"] = [
                    "messageKey" => $process_message,
                    "messageData" => []
                ];
                $this->data([
                    "subdomain" => "",
                    "status" => $process,
                    "message" => $process_message,
                ]);
                return $this->renderIt("MeetingBundle:Authentication:register.html.twig");
            }

            $this->renderData["email"] = $this->request->get("email");

            return $this->renderIt("MeetingBundle:Authentication:check_your_mailbox.html.twig");

        }

        if($this->request->get("guest_token")){

            $Guest = $this->entity(Guest::class)->findOneBy([
                "token_key" => $this->request->get("guest_token"),
                "status" => 0
            ]);

            if($Guest){

                $this->renderData["guest_token"] = $this->request->get("guest_token");
                $this->renderData["email"] = $Guest->getEmail();

            }

        }

        return $this->renderIt("MeetingBundle:Authentication:register.html.twig");
    }

    public function confirmYourEmailAction()
    {
        $this->renderBase(false);
        $this->transDomain("security");
        $this->data(["email" => $this->user->getEmail(), "reg" => "register"]);
        return $this->renderIt("MeetingBundle:Authentication:check_your_mailbox.html.twig");
    }

    public function addYourPhoneAction($tokenKey)
    {
        $this->renderBase(false);
        $this->transDomain("security");
        $this->data(["activation_token" => $tokenKey]);

        $token = $this->entity(Activation::class)->findOneBy([
            "token_key" => $tokenKey
        ]);

        $User = $this->entity(User::class)->find($token->getUser());
        $User->setIsConfirmedEmail(true);
        $this->em_flush();

        $process = true;
        $process_message = null;

        if(!$process){
            return $this->redirectToRoute("homepage_www");
        }

        return $this->renderIt("MeetingBundle:Authentication:add_your_phone.html.twig");
    }

    public function setPhoneNumberAction($tokenKey)
    {
        $this->renderBase(false);
        $this->transDomain("security");
        $this->data(["activation_token" => $tokenKey]);

        $process = true;
        $process_message = null;

        $tokenA = $this->entity(Activation::class)->findOneBy([
            "token_key" => $tokenKey
        ]);
        $checkPhoneAlreadyExist = $this->entity(User::class)->findOneBy([
            "phone" => $this->request->get("phone")
        ]);
        if($tokenA&&$checkPhoneAlreadyExist&&$checkPhoneAlreadyExist->getId()!=$tokenA->getUser()){
            $process = false;
            $process_message = "string.phone_already_taken";
        }else{
            $this->em_clear();
            $token = $this->entity(Activation::class)->findOneBy([
                "token_key" => $tokenKey
            ]);
            if(!$token){
                $process = false;
                $process_message = "string.token_could_not_found";
            }else{
                $token->setSmsKey(rand(100000, 999999));
                $this->em_flush();
                $this->em_clear();
                $SmsStream = $this->get("MakrosumNotificationStream.SMS");
                $SmsStream->sendToGroup($token->getSmsKey(), [substr($this->request->get("phone"), -10)]);
                $User = $this->entity(User::class)->find($token->getUser());
                $User->setPhone($this->request->get("phone"));
                $this->em_flush();
                $this->em_clear();
            }
        }

        if(!$process){
            return $this->redirectToRoute("add_your_phone", ["tokenKey" => $tokenKey, "prompt" => $process_message]);
        }

        return $this->renderIt("MeetingBundle:Authentication:confirm_your_phone.html.twig");
    }

    public function confirmPhoneNumberAction($tokenKey)
    {
        $this->renderBase(false);
        $this->transDomain("security");

        $process = true;
        $process_message = null;

        $token = $this->entity(Activation::class)->findOneBy([
            "token_key" => $tokenKey
        ]);

        if(!$token){
            $process = false;
            $process_message = "string.token_could_not_found";
        }else{
            if($this->request->get("sms_token")==$token->getSmsKey()){
                $User = $this->entity(User::class)->find($token->getUser());
                $User->setIsActive(true);
                $User->setIsConfirmedPhone(true);
                $this->em_flush();
            }else{
                $process = false;
                $process_message = "string.wrong_sms_key";
            }
        }

        if(!$process){
            return $this->redirectToRoute("add_your_phone", ["tokenKey" => $tokenKey, "prompt" => $process_message]);
        }

        return $this->redirectToRoute("homepage_www");

    }

    public function loadProvincesAction(){

        $this->renderBase();
        $this->transDomain("account");
        $this->data(["provinces" => [
            "*" => [
                "area" => "*",
                "name" => $this->translator->trans("string.please_select", [], $this->transDomain, $this->getLocale())
            ]
        ]]);

        $provinces = $this->getDoctrine()->getRepository("MeetingBundle:Province")->findBy([
            "country_code" => $this->getRequest()->get("country")
        ]);

        foreach($provinces as $province){
            $this->data([
                "provinces" => [
                    $province->getId() => [
                        "area" => $province->getArea(),
                        "name" => $province->getName()
                    ]
                ]
            ]);
        }

        return $this->renderIt("MeetingBundle:Global:load_provinces.html.twig");

    }

    public function loadCitiesAction(){

        $this->renderBase();
        $this->transDomain("account");
        $this->data(["cities" => [
            "*" => [
                "area" => "*",
                "name" => $this->translator->trans("string.please_select", [], $this->transDomain, $this->getLocale())
            ]
        ]]);

        $cities = $this->getDoctrine()->getRepository("MeetingBundle:City")->findBy([
            "country_code" => $this->getRequest()->get("country"), "province_code" => $this->getRequest()->get("province")
        ]);

        foreach($cities as $city){
            $this->data([
                "cities" => [
                    $city->getId() => [
                        "area" => $city->getArea(),
                        "name" => $city->getName()
                    ]
                ]
            ]);
        }

        return $this->renderIt("MeetingBundle:Global:load_cities.html.twig");

    }

    public function loginCheckAction(){



    }

    public function testEmailAction()
    {
        $this->renderBase(false);

        function HTTPPoster($prmPostAddress,$prmSendData){
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$prmPostAddress);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,TRUE);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: application/json"));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $prmSendData);
            $result = curl_exec($ch);
            return $result;
        }

        $loginData='{"Username": "makrosum", "Password": "3344", "Origin": "MAKROSUM"}';
        $loginPath = "http://smsservice.artimesaj.com/api/login/";
        $aToken = HTTPPoster($loginPath, $loginData);

        $smsData = json_encode([
            "MessageText" => "Bu bir deneme mesajdır",
            "Orginator" => "MAKROSUM",
            "IsTurkish" => false,
            "Date" => null,
            "Numbers" => ["5447434252"]
        ]);
        $smsPath = "http://smsservice.artimesaj.com/api/singletextmessage?authToken=".rtrim(ltrim($aToken, "\""), "\"");
        $gonder = HTTPPoster($smsPath, $smsData);

        var_dump($gonder);

        /*$message = \Swift_Message::newInstance()
            ->setSubject("Test Email")
            ->setFrom(array($this->param("mailer_sender") => $this->param("global.project_name")))
            ->setTo("byhackro@gmail.com")
            ->setBody("Deneme <b>Musa ATALAY</b>");

        $this->get("mailer")->send($message);*/

        //return $this->renderIt("MeetingBundle:Global:message.html.twig");

        return $this->renderJson(["message" => $gonder]);
    }
}