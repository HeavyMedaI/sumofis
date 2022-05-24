<?php
/**
 * Created by PhpStorm.
 * User: Musa ATALAY
 * Date: 11.03.2016
 * Time: 21:04
 */

namespace Makrosum\MeetingBundle\Controller;


use Makrosum\MeetingBundle\Abstractions\CompanyBaseController;
use Makrosum\MeetingBundle\Entity\Guest;
use Makrosum\MeetingBundle\Entity\Permission;
use Makrosum\MeetingBundle\Entity\Personnel;
use Makrosum\MeetingBundle\Entity\User;
use Makrosum\MeetingBundle\Entity\UserSettings;
use Makrosum\MeetingBundle\MeetingBundle;

class PersonnelController extends CompanyBaseController
{

    private $grants = [
        "personnel" => ["super" => false],
        "meeting" => [
            "super" => false,
            "create" => false,
            "edit" => false,
            "status" => false,
            "delete" => false,
            "request" => false,
            "matter" => false
        ]
    ];

    public function checkPersonnelAction()
    {
        $this->renderCompanyBase();

        if(!$this->user_permissions->isGranted(MeetingBundle::GRANTED_SUPER_PERSONNEL)){
            throw $this->createAccessDeniedException($this->translator->trans("string.module_not_permitted", [], "security", $this->getLocale()));
        }

        $User = $this->entity(User::class)->findOneBy([
            "email" => $this->request->get("email")
        ]);

        $status = true;
        $message = null;

        if(!$User){
            $status = false;
            $message = $this->translator->trans("string.user_could_not_found_with_email", [], $this->transDomain, $this->getLocale());
        }else{
            $Personnel = $this->entity(Personnel::class)->findOneBy([
                "company" => $this->company->getId(),
                "user" => $User->getId()
            ]);
            if($Personnel){
                $status = false;
                $message = $this->translator->trans("string.user_already_in_your_company", [], $this->transDomain, $this->getLocale());
            }else{
                $message = ["user_id" => $User->getId(), "user_fullname" => $User->getFullname()];
            }
        }

        return $this->renderJson([
            "status" => $status,
            "message" => $message,
            "email" => $this->request->get("email")
        ], 200);

        exit(self::class."::checkPersonnel({$personnelEmail})");
    }

    public function newPersonnelAction()
    {
        $this->renderCompanyBase();

        if(!$this->user_permissions->isGranted(MeetingBundle::GRANTED_SUPER_PERSONNEL)){
            throw $this->createAccessDeniedException($this->translator->trans("string.module_not_permitted", [], "security", $this->getLocale()));
        }

        $Personnel = new Personnel();
        $Personnel->setCompany($this->company->getId())
            ->setUser($this->request->get("user"))
            ->setDepartment($this->request->get("department"))
            ->setPosition($this->request->get("position"));

        $this->em()->em_persist($Personnel)->em_flush();

        $status = true;
        $message = null;

        if(!$Personnel){
            $status = false;
            $message = $this->translator->trans("string.personnel_could_not_created", [], $this->transDomain, $this->getLocale());
        }else{

            $User = $this->entity(User::class)->find($this->request->get("user"));
            $UserSettings = $this->entity(UserSettings::class)->findOneBy([
                "user_id" => $User->getId()
            ]);

            if($UserSettings){
                $SmsNotification = null;
                if($UserSettings->getSmsNotification()){
                    $SmsNotification = $this->translator->trans("string.you_have_been_added_to_a_company", ["%company%" => $this->company->getName()], "notification", $UserSettings->getLanguage());
                    $SmsStream = $this->get("MakrosumNotificationStream.SMS");
                    $SmsStream->sendToGroup($SmsNotification, [substr($User->getPhone(), -10)]);
                }

                $MailNotification = null;
                if($UserSettings->getEmailNotification()){
                    $MailNotification = \Swift_Message::newInstance()
                        ->setSubject($this->translator->trans("string.notification_title", [], "notification", $UserSettings->getLanguage()))
                        ->setFrom(array($this->param("mailer_sender") => $this->param("global.project_name")))
                        ->setTo($User->getEmail())
                        ->setBody(
                            $this->renderView(
                                "MeetingBundle:Emails:you_have_been_added_to_a_company.html.twig",
                                [
                                    "trans_l" => $this->getLocale(),
                                    "base_hostname" => $this->baseHostname,
                                    "base_server_url" => $this->protocol_scheme."://".$this->baseHostname,
                                    "language" => $UserSettings->getLanguage(),
                                    "company" => $this->company->getName(),
                                    "company_name" => $this->company->getName(),
                                    "company_email" => $this->company->getEmail(),
                                    "company_logo" => $this->company->getLogo(),
                                    "company_url" => $this->protocol_scheme."://".$this->companyDomain.".".$this->param("hostname")
                                ]
                            ), "text/html"
                        );

                    $this->get("mailer")->send($MailNotification);
                }

                $this->data(["sms_notification" => $SmsNotification]);
                $this->data(["mail_notification" => $MailNotification]);
            }

            $permissions = $this->request->get("permission");

            if($permissions["personnel"]["super"]){
                $this->grants["personnel"]["super"] = $permissions["personnel"]["super"];
            }

            if($permissions["meeting"]){
                if($permissions["meeting"]["super"]){
                    $this->grants["meeting"] = [
                        "super" => true,
                        "create" => true,
                        "edit" => true,
                        "status" => true,
                        "delete" => false,
                        "request" => true,
                        "matter" => true
                    ];
                }else{
                    foreach($permissions["meeting"] as $permission => $grant){
                        $this->grants["meeting"][$permission] = $grant;
                    }
                    if($permissions["meeting"]["create"]){
                        $this->grants["meeting"]["request"] = true;
                        $this->grants["meeting"]["matter"] = true;
                    }
                    if($permissions["meeting"]["edit"]){
                        $this->grants["meeting"]["request"] = true;
                        $this->grants["meeting"]["matter"] = true;
                    }
                    if($permissions["meeting"]["status"]){
                        $this->grants["meeting"]["edit"] = true;
                        $this->grants["meeting"]["request"] = true;
                        $this->grants["meeting"]["matter"] = true;
                    }
                    if($permissions["meeting"]["request"]){
                        $this->grants["meeting"]["matter"] = true;
                    }
                }
            }

            $Permission = new Permission();
            $Permission->setUser($this->request->get("user"))
                ->setCompany($this->company);

            $Permission->setGrant(MeetingBundle::GRANTED_SUPER_PERSONNEL, $this->grants["personnel"]["super"]);
            $Permission->setGrant(MeetingBundle::GRANTED_SUPER_MEETING, $this->grants["meeting"]["super"]);
            $Permission->setGrant(MeetingBundle::GRANTED_CREATE_MEETING, $this->grants["meeting"]["create"]);
            $Permission->setGrant(MeetingBundle::GRANTED_EDIT_MEETING, $this->grants["meeting"]["edit"]);
            $Permission->setGrant(MeetingBundle::GRANTED_STATUS_MEETING, $this->grants["meeting"]["status"]);
            $Permission->setGrant(MeetingBundle::GRANTED_DELETE_MEETING, $this->grants["meeting"]["delete"]);
            $Permission->setGrant(MeetingBundle::GRANTED_REQUEST_MEETING, $this->grants["meeting"]["request"]);
            $Permission->setGrant(MeetingBundle::GRANTED_MATTER_MEETING, $this->grants["meeting"]["matter"]);

            $this->em()->em_persist($Permission)->em_flush();

            $route = $this->get("router")->generate("company_company_personnels_edit", [
                "subdomain" => $this->companyDomain,
                "personnelId" => $Personnel->getId()
            ]);

            $this->data([
                "route" => $this->companyDomain.".".$this->param("hostname").$route
            ]);
        }

        return $this->renderJson([
            "status" => $status,
            "message" => $message
        ]);

        exit(self::class."::newPersonnel()");
    }

    public function invitePeopleAction()
    {
        $this->renderCompanyBase();

        if(!$this->user_permissions->isGranted(MeetingBundle::GRANTED_SUPER_PERSONNEL)){
            throw $this->createAccessDeniedException($this->translator->trans("string.module_not_permitted", [], "security", $this->getLocale()));
        }

        $GuestToken = md5($this->user->getId().$this->request->get("email").$this->user->getId().time().rand(3333, 9999));

        $Guest = new Guest();
        $Guest->setToken($GuestToken)
            ->setEmail($this->request->get("email"))
            ->setPersonnel()
            ->setRef($this->user->getId())
            ->setStatus(0);

        $this->em()->em_persist($Guest)->em_flush();

        $status = true;
        $message = $this->translator->trans("string.user_invited_successfully", [], $this->transDomain, $this->getLocale());

        if(!$Guest){
            $status = false;
            $message = $this->translator->trans("string.user_could_not_invited", [], $this->transDomain, $this->getLocale());
        }else{
            $MailNotification = \Swift_Message::newInstance()
                ->setSubject($this->translator->trans("string.notification_title", [], "notification", $this->getLocale()))
                ->setFrom(array($this->param("mailer_sender") => $this->param("global.project_name")))
                ->setTo($this->request->get("email"))
                ->setBody(
                    $this->renderView(
                        "MeetingBundle:Emails:invite.html.twig",
                        [
                            "trans_l" => $this->getLocale(),
                            "base_hostname" => $this->baseHostname,
                            "base_server_url" => $this->protocol_scheme."://".$this->baseHostname,
                            "language" => $this->getLocale(),
                            "company" => $this->company->getName(),
                            "company_name" => $this->company->getName(),
                            "company_email" => $this->company->getEmail(),
                            "company_logo" => $this->company->getLogo(),
                            "company_url" => $this->protocol_scheme."://".$this->companyDomain.".".$this->param("hostname"),
                            "redirect_url" => $this->get("router")->generate("register")."?guest_token=".$GuestToken
                        ]
                    ), "text/html"
                );

            $this->get("mailer")->send($MailNotification);

            $this->data(["mail_notification" => $MailNotification]);
        }


        return $this->renderJson([
            "status" => $status,
            "message" => $message
        ]);

        exit(self::class."::invitePeopleAction()");
    }

    public function editPersonnelAction($personnelId)
    {
        $this->renderCompanyBase();

        if(!$this->user_permissions->isGranted(MeetingBundle::GRANTED_SUPER_PERSONNEL)){
            throw $this->createAccessDeniedException($this->translator->trans("string.module_not_permitted", [], "security", $this->getLocale()));
        }

        $route = $this->get("router")->generate("company_company_personnels_edit", [
            "subdomain" => $this->companyDomain,
            "personnelId" => $personnelId
        ]);

        $this->data([
            "route" => $this->companyDomain.".".$this->param("hostname").$route
        ]);

        $Personnel = $this->entity(Personnel::class)->find($personnelId);
        $Personnel->setDepartment($this->request->get("department"));
        $Personnel->setPosition($this->request->get("position"));

        $this->em_flush();

        $permissions = $this->request->get("permission");

        if($permissions["personnel"]["super"]){
            $this->grants["personnel"]["super"] = $permissions["personnel"]["super"];
        }

        if($permissions["meeting"]){
            if($permissions["meeting"]["super"]){
                $this->grants["meeting"] = [
                    "super" => true,
                    "create" => true,
                    "edit" => true,
                    "status" => true,
                    "delete" => false,
                    "request" => true,
                    "matter" => true
                ];
            }else{
                foreach($permissions["meeting"] as $permission => $grant){
                    $this->grants["meeting"][$permission] = $grant;
                    }
                if($permissions["meeting"]["create"]){
                    $this->grants["meeting"]["request"] = true;
                    $this->grants["meeting"]["matter"] = true;
                }
                if($permissions["meeting"]["edit"]){
                    $this->grants["meeting"]["request"] = true;
                    $this->grants["meeting"]["matter"] = true;
                }
                if($permissions["meeting"]["status"]){
                    $this->grants["meeting"]["edit"] = true;
                    $this->grants["meeting"]["request"] = true;
                    $this->grants["meeting"]["matter"] = true;
                }
                if($permissions["meeting"]["request"]){
                    $this->grants["meeting"]["matter"] = true;
                }
            }
        }

        $Permission = $this->entity(Permission::class)->findOneBy([
            "user" => $this->request->get("user"),
            "company" => $this->company->getId()
        ]);

        $Permission->setGrant(MeetingBundle::GRANTED_SUPER_PERSONNEL, $this->grants["personnel"]["super"]);
        $Permission->setGrant(MeetingBundle::GRANTED_SUPER_MEETING, $this->grants["meeting"]["super"]);
        $Permission->setGrant(MeetingBundle::GRANTED_CREATE_MEETING, $this->grants["meeting"]["create"]);
        $Permission->setGrant(MeetingBundle::GRANTED_EDIT_MEETING, $this->grants["meeting"]["edit"]);
        $Permission->setGrant(MeetingBundle::GRANTED_STATUS_MEETING, $this->grants["meeting"]["status"]);
        $Permission->setGrant(MeetingBundle::GRANTED_DELETE_MEETING, $this->grants["meeting"]["delete"]);
        $Permission->setGrant(MeetingBundle::GRANTED_REQUEST_MEETING, $this->grants["meeting"]["request"]);
        $Permission->setGrant(MeetingBundle::GRANTED_MATTER_MEETING, $this->grants["meeting"]["matter"]);

        $this->em()->em_persist($Permission)->em_flush();

        return $this->renderJson([
            "status" => true,
            "message" => null
        ]);

        exit(self::class."::editPersonnel({$personnelId})");
    }

    public function removePersonnelAction($personnelId)
    {
        $this->renderCompanyBase();

        if(!$this->user_permissions->isGranted(MeetingBundle::GRANTED_SUPER_PERSONNEL)){
            throw $this->createAccessDeniedException($this->translator->trans("string.module_not_permitted", [], "security", $this->getLocale()));
        }

        $Personnel = $this->entity(Personnel::class)->find($personnelId);

        $User = $this->entity(User::class)->find($Personnel->getUser());

        $Permissions = $this->entity(Permission::class)->findOneBy([
            "user" => $User->getId(),
            "company" => $this->company->getId()
        ]);
        if($Permissions){
            $this->em_remove($Permissions)->em_flush();
        }

        $this->em_remove($Personnel)->em_flush();
        $this->em_clear();

        return $this->renderJson(["status" => true]);
    }
}