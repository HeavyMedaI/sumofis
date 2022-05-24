<?php
/**
 * Created by PhpStorm.
 * User: musaatalay
 * Date: 22.03.2016
 * Time: 01:58
 */

namespace Makrosum\MeetingBundle\Controller;


use Makrosum\MeetingBundle\Abstractions\CompanyBaseController;
use Makrosum\MeetingBundle\Abstractions\MeetingBaseController;
use Makrosum\MeetingBundle\Entity\Meeting;
use Makrosum\MeetingBundle\Entity\User;
use Makrosum\MeetingBundle\Entity\UserSettings;
use Makrosum\MeetingBundle\Entity\MeetingMember;

class MeetingMemberController extends MeetingBaseController
{
    public function insertAction($meetingId)
    {
        $this->renderMeetingBase($meetingId, []);

        $Meeting = $this->entity(Meeting::class)->find($meetingId);

        $MeetingMember = $this->request->get("member");

        $User = $this->entity(User::class)->find($MeetingMember);
        $Member = new MeetingMember();
        $Member->setCompany($this->company)
            ->setMeeting($Meeting)
            ->setUser($User)
            ->setUserEmail($User)
            ->setRole(1);
        $this->em()->em_persist($Member)->em_flush();
        $this->em_clear();

        $WebCalPath = $this->get("router")->generate("company_meeting_webcal_protocol_ics", [
            "subdomain" => $this->company->getDomain(),
            "meetingId" => $this->Meeting->getId()
        ]);

        $MeetingUrl = $this->get("router")->generate("company_meeting_view_meeting", [
            "subdomain" => $this->companyDomain,
            "meetingId" => $this->Meeting->getId()
        ]);

        $UserSettings = $this->entity(UserSettings::class)->findOneBy([
            "user_id" => $User->getId()
        ]);

        if($UserSettings){
            $SmsNotification = null;
            if($UserSettings->getSmsNotification()){
                $SmsNotification = $this->translator->trans("string.there_shall_be_a_meeting", [
                    "%meeting%" => $Meeting->getHeader(),
                    "%begin%" => $Meeting->getBegin()->format("d.m.Y H:i"),
                    "%end%" => $Meeting->getEnd()->format("d.m.Y H:i"),
                    "%company%" => $this->company->getName()
                ], "notification", $UserSettings->getLanguage());
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
                            "MeetingBundle:Emails:you_have_been_added_to_a_meeting.html.twig",
                            [
                                "trans_l" => $this->getLocale(),
                                "base_hostname" => $this->baseHostname,
                                "language" => $UserSettings->getLanguage(),
                                "meeting" => $Meeting->getHeader(),
                                "begin" => $Meeting->getBegin()->format("d.m.Y H:i"),
                                "end" => $Meeting->getEnd()->format("d.m.Y H:i"),
                                "webcal" => $WebCalPath,
                                "meeting_url" => $this->protocol_scheme."://".$this->companyDomain.".".$this->param("hostname").$MeetingUrl,
                                "base_server_url" => $this->protocol_scheme."://".$this->baseHostname,
                                "company" => $this->company->getName(),
                                "company_url" => $this->protocol_scheme."://".$this->companyDomain.".".$this->param("hostname"),
                                "current_company_logo" => $this->company->getLogo()
                            ]
                        ), "text/html"
                    );

                $this->get("mailer")->send($MailNotification);
            }

            $this->data(["sms_notification" => $SmsNotification]);
            $this->data(["mail_notification" => $MailNotification]);
        }

        return $this->renderJson([
            "status" => true,
            "message" => "Hata"
        ]);
    }

    public function insertMembersAction($meetingId)
    {
        $this->renderCompanyBase($meetingId, []);

        $MeetingMembers = $this->request->get("members");

        foreach($MeetingMembers as $MeetingMember){
            $User = $this->entity(User::class)->find($MeetingMember);
            $Member = new MeetingMember();
            $Member->setCompany($this->company)
                ->setMeeting($this->entity(Meeting::class)->find($meetingId))
                ->setUser($User)
                ->setUserEmail($User)
                ->setRole(1);
            $this->em()->em_persist($Member)->em_flush();
            $this->em_clear();
        }

        return $this->renderJson([
            "status" => true,
            "message" => "Hata"
        ]);
    }

    public function insertExternalAction($meetingId)
    {
        $this->renderMeetingBase($meetingId, [], "meeting");

        $MemberEmail = $this->request->get("email");

        $checkRelationToThisMeeting = $this->entity(MeetingMember::class)->findOneBy([
            "meeting" => $meetingId,
            "user_email" => $MemberEmail
        ]);
        if($checkRelationToThisMeeting){
            return $this->renderJson([
                "status" => false,
                "message" => $this->translator->trans("string.member_already_exist", [], $this->transDomain, $this->getLocale())
            ]);
        }

        $isUser = false;

        $checkUserWithEmail = $this->entity(User::class)->findOneBy([
            "email" => $MemberEmail
        ]);

        if($checkUserWithEmail){
            $isUser = true;
            $User = $checkUserWithEmail;
        }else{
            $User = new User();
            $User->setEmail($MemberEmail);
        }

        $MeetingMember = new MeetingMember();
        $MeetingMember->setCompany($this->company)
            ->setMeeting($this->entity(Meeting::class)->find($meetingId))
            ->setUser($User)
            ->setUserEmail($User)
            ->setRole(3);
        $this->em()->em_persist($MeetingMember)->em_flush();
        $this->em_clear();

        $status = true;
        $message = null;

        if(!$MeetingMember){
            $status = false;
            $message = "Hata";
        }

        $WebCalPath = $this->get("router")->generate("company_meeting_webcal_protocol_ics", [
            "subdomain" => $this->company->getDomain(),
            "meetingId" => $this->Meeting->getId()
        ]);

        $MeetingUrl = $this->get("router")->generate("company_meeting_view_meeting", [
            "subdomain" => $this->companyDomain,
            "meetingId" => $this->Meeting->getId()
        ]);

        if($isUser){
            $UserSettings = $this->entity(UserSettings::class)->findOneBy([
                "user_id" => $User->getId()
            ]);

            if($UserSettings){
                $SmsNotification = null;
                if($UserSettings->getSmsNotification()){
                    $SmsNotification = $this->translator->trans("string.there_shall_be_a_meeting", [
                        "%meeting%" => $this->Meeting->getHeader(),
                        "%begin%" => $this->Meeting->getBegin()->format("d.m.Y H:i"),
                        "%end%" => $this->Meeting->getEnd()->format("d.m.Y H:i"),
                        "%company%" => $this->company->getName()
                    ], "notification", $UserSettings->getLanguage());
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
                                "MeetingBundle:Emails:you_have_been_added_to_a_meeting.html.twig",
                                [
                                    "trans_l" => $this->getLocale(),
                                    "base_hostname" => $this->baseHostname,
                                    "language" => $UserSettings->getLanguage(),
                                    "meeting" => $this->Meeting->getHeader(),
                                    "begin" => $this->Meeting->getBegin()->format("d.m.Y H:i"),
                                    "end" => $this->Meeting->getEnd()->format("d.m.Y H:i"),
                                    "webcal" => $WebCalPath,
                                    "meeting_url" => $this->protocol_scheme."://".$this->companyDomain.".".$this->param("hostname").$MeetingUrl,
                                    "base_server_url" => $this->protocol_scheme."://".$this->baseHostname,
                                    "current_company_name" => $this->company->getName(),
                                    "company_url" => $this->protocol_scheme."://".$this->companyDomain.".".$this->param("hostname"),
                                    "current_company_logo" => $this->company->getLogo()
                                ]
                            ), "text/html"
                        );

                    $this->get("mailer")->send($MailNotification);
                }

                $this->data(["sms_notification" => $SmsNotification]);
                $this->data(["mail_notification" => $MailNotification]);
            }
        }else{
            $MailNotification = null;
            $MailNotification = \Swift_Message::newInstance()
                ->setSubject("Sumofis.com Hareket Bildirimi")
                ->setFrom(array($this->param("mailer_sender") => $this->param("global.project_name")))
                ->setTo($User->getEmail())
                ->setBody(
                    $this->renderView(
                        "MeetingBundle:Emails:you_have_been_added_to_a_meeting.html.twig",
                        [
                            "trans_l" => $this->getLocale(),
                            "base_hostname" => $this->baseHostname,
                            "language" => $this->defaultLocale,
                            "meeting" => $this->Meeting->getHeader(),
                            "begin" => $this->Meeting->getBegin()->format("d.m.Y H:i"),
                            "end" => $this->Meeting->getEnd()->format("d.m.Y H:i"),
                            "webcal" => $WebCalPath,
                            "meeting_url" => $this->protocol_scheme."://".$this->companyDomain.".".$this->param("hostname").$MeetingUrl,
                            "base_server_url" => $this->protocol_scheme."://".$this->baseHostname,
                            "company" => $this->company->getName(),
                            "company_url" => $this->protocol_scheme."://".$this->companyDomain.".".$this->param("hostname"),
                            "current_company_logo" => $this->company->getLogo()
                        ]
                    ), "text/html"
                );

            $this->get("mailer")->send($MailNotification);

            $this->data(["sms_notification" => false]);
            $this->data(["mail_notification" => $MailNotification]);
        }

        return $this->renderJson([
            "status" => $status,
            "message" => $message
        ]);
    }

    public function removeAction($memberId)
    {
        $this->renderCompanyBase([], "meeting");

        $MeetingMember = $this->entity(MeetingMember::class)->find($memberId);

        $this->em_remove($MeetingMember)->em_flush();

        return $this->renderJson([
            "status" => true,
            "message" => "Hata"
        ]);
    }

    public function setMemberAction($meetingId, $memberId)
    {
        $this->renderMeetingBase($meetingId, []);

        $Role = $this->request->get("role");

        if($Role==2){
            $PreviousReporter = $this->entity(MeetingMember::class)->findOneBy([
                "role" => 2
            ]);
            if($PreviousReporter){
                $PreviousReporter->setRole(1);
            }
        }

        $MeetingMember = $this->entity(MeetingMember::class)->find($memberId);
        $MeetingMember->setRole($Role);

        $this->em_flush();

        $status = true;
        $message = null;

        if(!$MeetingMember){
            $status = false;
            $message = "Hata";
        }

        return $this->renderJson([
            "status" => $status,
            "message" => $message
        ]);

    }

}