<?php

/**
 * Created by PhpStorm.
 * User: user
 * Date: 24.02.2016
 * Time: 16:32
 */

namespace Makrosum\MeetingBundle\Abstractions;

use Makrosum\MeetingBundle\Entity\Activation;
use Makrosum\MeetingBundle\MeetingBundle;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

abstract class BaseController extends Controller
{
    protected $entityManager;
    protected $baseHostname;
    //protected $companyDomain;
    protected $transDomain;
    protected $renderData = array();
    protected $request;
    protected $translator;
    protected $user;
    protected $user_settings;
    protected $related_domains = array();
    protected $protocol_scheme;
    protected $defaultLocale = "tr";

    protected function entity($Entity, $Bundle = "MeetingBundle")
    {
        $explodeEntity = explode(":", $Entity);
        $Entity = $explodeEntity[count($explodeEntity)-1];
        $explodeEntity = explode("\\", $Entity);
        $Entity = $explodeEntity[count($explodeEntity)-1];
        //return $this->getDoctrine()->getRepository("{$Bundle}:{$Entity}");
        $this->em();
        return $this->entityManager->getRepository("{$Bundle}:{$Entity}");
    }

    protected function em()
    {
        $this->entityManager = $this->getDoctrine()->getManager();
        return $this;
    }

    protected function em_persist($Entity)
    {
        $this->entityManager->persist($Entity);
        return $this;
    }

    protected function em_remove(MakrosumEntity $Entity)
    {
        $this->entityManager->remove($Entity);
        return $this;
    }

    protected function em_flush()
    {
        $this->entityManager->flush();
    }

    protected function em_clear()
    {
        $this->entityManager->clear();
    }

    protected function isRole($ROLE)
    {
        return $this->get("security.authorization_checker")->isGranted($ROLE);
    }

    protected function param($parameter, $value = null){
        if($value==null){
            if(strpos($parameter, ".")!==false){
                $returnParameter = null;
                $parseParameter = explode(".", $parameter);
                $stackParameter = $this->getParameter(array_shift($parseParameter));
                foreach($parseParameter as $currentParameter){
                    $stackParameter = $stackParameter[$currentParameter];
                }
                return $stackParameter;
            }else{
                return $this->getParameter($parameter);
            }
        }
    }

    protected function getRequest(){
        return $this->container->get('request_stack')->getCurrentRequest();
    }

    protected function getTranslator(){
        return $this->get('translator');
    }

    protected function transDomain($domain){
        $this->transDomain = $domain;
        $this->data(["trans_d" => $domain]);
    }

    protected function getLocale(){
        return $this->translator->getLocale();
    }

    protected function setLocale($locale, $fallbackLocales = array()){
        if(count($fallbackLocales)>=1){
            $this->translator->setFallbackLocales($fallbackLocales);
        }
        $this->request->setLocale($locale);
        $this->translator->setLocale($locale);
        $this->data(["trans_l" => $locale]);
    }

    protected function renderBase($checkActivation = true){
        //exit(var_dump($this->get('security.authorization_checker').isGranted('ROLE_USER')));
        $GlobalParams = $this->param("global");
        $this->data(["title" => $GlobalParams["project_name"]]);
        $this->defaultLocale = $this->param("default_locale");
        $this->request = $this->getRequest();
        $this->baseHostname = $this->param("hostname");
        $this->companyDomain = $this->request->get('subdomain');
        $this->data([
            "base_hostname" => $this->baseHostname,
            //"subdomain" => $this->companyDomain,
            "companies" => array()
        ]);
        $this->translator = $this->getTranslator();
        $l = $this->defaultLocale;
        if($this->isRole(MeetingBundle::ROLE_USER)){
            $this->user = $this->getUser();
            if($checkActivation&&$this->user->getIsConfirmedEmail()==false){
                $render = $this->redirectToRoute("check_your_email");
                $render->send();
                exit;
            }
            if($checkActivation&&!$this->user->getIsConfirmedPhone()){
                $token = $this->entity(Activation::class)->findOneBy([
                    "user" => $this->user->getId()
                ]);
                if($token){
                    $tokenKey = $token->getToken();
                    $render = $this->redirectToRoute("add_your_phone", ["tokenKey" => $tokenKey]);
                    $render->send();
                    exit;
                }
            }
            $this->data(["user_fullname" => $this->user->getFullname()]);
            $EntityManager = $this->getDoctrine()->getManager();
            $this->user_settings = $EntityManager->getRepository("MeetingBundle:UserSettings")->findOneBy(array("user_id" => $this->user->getId()));
            $l = $this->user_settings->getLanguage();
            $Companies = $this->getDoctrine()->getRepository("MeetingBundle:Company")->findAllCompaniesRelatedToMe($this->user);
            foreach($Companies as $domain => $company){
                $this->related_domains[] = $domain;
                $this->data([
                    "companies" => [
                        $domain => $company->getName()
                    ]
                ]);
            }
        }
        $this->setLocale($l, [
            $l, $this->defaultLocale,
            "en", "tr_TR", "en_US"
        ]);
        $this->protocol_scheme = $this->request->getScheme();
        $this->data(["protocol_scheme" => $this->protocol_scheme]);
        $this->data(array(
            "_current_".$this->request->get("subdomain") => "current"
        ));
        if(strpos($this->request->get("_route"), "company")!==false&&$this->request->get("_route")!="company_add_new"){
            $this->data(array(
                "_current_companies" => "current",
                "_current_company_".$this->request->get("subdomain") => "current"
            ));
        }
        if($this->request->get("prompt")){
            $this->renderData["promt_error"] = [
                "messageKey" => $this->request->get("prompt"),
                "messageData" => []
            ];
        }
        $this->renderData["protocol_scheme"] = $this->protocol_scheme;
        //$this->renderData["server_name"] = $this->protocol_scheme."://".$this->baseHostname;
    }

    protected function data(Array $data){
        $this->renderData = array_merge_recursive($this->renderData, $data);
        //$this->renderData = MeetingBundle::array_merger($this->renderData, $data);
    }

    protected function renderIt($twig){
        return $this->render($twig, $this->renderData);
    }

    protected function renderJson(Array $data = [], $status = 200, Array $headers = array()){
        $this->renderData = array_merge_recursive($this->renderData, $data);
        //$this->renderData = MeetingBundle::array_merger($this->renderData, $data);
        return new JsonResponse($this->renderData, $status, $headers);
    }
}