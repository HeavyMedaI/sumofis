<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 26.02.2016
 * Time: 15:54
 */

namespace Makrosum\MeetingBundle\Controller;

use Makrosum\MeetingBundle\Abstractions\BaseController;
use Makrosum\MeetingBundle\Entity\UserSettings;

class AccountController extends BaseController
{

    protected $transDomain = "account";

    public function profileAction(){
        $this->renderBase();
        $this->transDomain($this->transDomain);
        $this->data([
            "email" => $this->user->getEmail(),
            "fullname" => $this->user->getFullname(),
            "phone" => $this->user->getPhone(),
            "countries" => [],
            "provinces" => [],
            "cities" => []
        ]);

        $countries = $this->getDoctrine()->getRepository("MeetingBundle:Country")->findAll();
        foreach($countries as $country){
            $selected = false;
            if($this->user->getCountry()==$country->getCode()){
                $selected = true;
            }
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

        $provinces = $this->getDoctrine()->getRepository("MeetingBundle:Province")->findBy([
            "country_code" => $this->user->getCountry()
        ]);
        foreach($provinces as $province){
            $selected = false;
            if($this->user->getProvince()==$province->getArea()){
                $selected = true;
            }
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

        $cities = $this->getDoctrine()->getRepository("MeetingBundle:City")->findBy([
            "country_code" => $this->user->getCountry(), "province_code" => $this->user->getProvince()
        ]);
        foreach($cities as $city){
            $selected = false;
            if($this->user->getCity()==$city->getArea()){
                $selected = true;
            }
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

        return $this->renderIt("MeetingBundle:Account:profile.html.twig");
    }

    public function profileUpdateAction(){

        $this->renderBase();
        $this->transDomain($this->transDomain);

        $em = $this->getDoctrine()->getManager();
        $Profile = $em->getRepository("MeetingBundle:User")->find($this->user->getId());

        $Profile->setFullname($this->getRequest()->get("fullname"));
        $Profile->setEmail($this->getRequest()->get("email"));
        $Profile->setPhone($this->getRequest()->get("phone"));
        $Profile->setCountry($this->getRequest()->get("country"));
        $Profile->setProvince($this->getRequest()->get("province"));
        $Profile->setCity($this->getRequest()->get("city"));

        if($this->getRequest()->get("password") != null){
            $Profile->setPlainPassword($this->getRequest()->get("password"));
            $newPassword = $this->get("security.password_encoder")->encodePassword($Profile, $Profile->getPlainPassword());
            $Profile->setPassword($newPassword);
        }

        $em->flush();

        return $this->redirectToRoute("profile");

    }

    public function generalSettingsAction(){
        $this->renderBase();
        $this->transDomain("account");
        $this->data(["settings" => []]);

        $languages = $this->getDoctrine()->getRepository("MeetingBundle:Language")->findAll();

        foreach($languages as $language){
            $selected = false;
            if($this->getLocale()==$language->getIso()){
                $selected = true;
            }
            $this->data([
                "settings" => [
                    "languages" => [
                        $language->getIso() => [
                            "name" => $language->getName(),
                            "selected" => $selected
                        ]
                    ]
                ]
            ]);
        }

        return $this->renderIt("MeetingBundle:Account:general_settings.html.twig");
    }

    public function generalSettingsUpdateAction(){
        $this->renderBase();
        $this->transDomain("account");

        $em = $this->getDoctrine()->getManager();
        $UserSettings = $em->getRepository("MeetingBundle:UserSettings")->findOneBy([
            "user_id" => $this->user->getId()
        ]);

        $UserSettings->setLanguage($this->getRequest()->get("language"));
        $em->flush();

        return $this->redirectToRoute("account_settings_general");
    }

    public function notificationSettingsAction(){
        $this->renderBase();
        $this->transDomain("account");

        $this->data([
            "settings" => [
                "notification" => [
                    "email" => $this->user_settings->getEmailNotification(),
                    "sms" => $this->user_settings->getSmsNotification()
                ]
            ]
        ]);

        return $this->renderIt("MeetingBundle:Account:notification_settings.html.twig");
    }

    public function notificationSettingsUpdateAction(){
        $this->renderBase();
        $this->transDomain("account");

        $em = $this->getDoctrine()->getManager();
        $UserSettings = $em->getRepository("MeetingBundle:UserSettings")->findOneBy([
            "user_id" => $this->user->getId()
        ]);

        if($this->getRequest()->get("email") != null){
            $UserSettings->setEmailNotification($this->getRequest()->get("email"));
        }
        if($this->getRequest()->get("sms") != null){
            $UserSettings->setSmsNotification($this->getRequest()->get("sms"));
        }

        $em->flush();

        return $this->redirectToRoute("account_settings_notification");
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



    public function settingsRegisterAction(){

        $this->renderBase();

        $settings = new UserSettings();
        $settings->setUserId($this->user->getId());
        $settings->setLanguage("en");

        $eM = $this->getDoctrine()->getManager();
        $eM->persist($settings);
        $eM->flush();

        exit("1");

    }

    public function settingsUpdateAction(){

        $this->renderBase();

        $eM = $this->getDoctrine()->getManager();

        $settings = $eM->getRepository("MeetingBundle:UserSettings")->findOneBy(array("user_id" => $this->user->getId()));

        var_dump($settings->getLanguage());

        $settings->setLanguage("en");

        $eM->persist($settings);
        $eM->flush();

        var_dump($settings->getLanguage());

        //var_dump($this->container->get('security.context')->getToken()->getUser());
        var_dump( $this->get('security.authorization_checker')->isGranted('ROLE_USER') );
        exit;
    }

    public function testLocaleAction(){
        $this->renderBase();
        var_dump( $this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_ANONYMOUSLY') );
        var_dump($this->getLocale());
        exit;
    }

}