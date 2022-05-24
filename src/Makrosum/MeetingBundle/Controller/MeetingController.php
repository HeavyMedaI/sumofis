<?php
/**
 * Created by PhpStorm.
 * User: musaatalay
 * Date: 18.03.2016
 * Time: 23:01
 */

namespace Makrosum\MeetingBundle\Controller;


use Makrosum\MeetingBundle\Abstractions\MeetingBaseController;
use Makrosum\MeetingBundle\Entity\MeetingMember;
use Makrosum\MeetingBundle\Entity\Permission;
use Makrosum\MeetingBundle\Entity\Personnel;
use Makrosum\MeetingBundle\Entity\Meeting;
use Makrosum\MeetingBundle\Entity\MeetingCategory;
use Makrosum\MeetingBundle\Entity\MeetingMatterSubject;
use Makrosum\MeetingBundle\Entity\MeetingObject;
use Makrosum\MeetingBundle\Entity\User;
use Makrosum\MeetingBundle\Entity\UserSettings;
use Makrosum\MeetingBundle\Entity\Company;
use Makrosum\MeetingBundle\Entity\MeetingReport;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class MeetingController extends MeetingBaseController
{
    public function meetingsAction()
    {
        $this->renderMeetingBase();

        $Calendar = array();

        $Companies = $this->entity(Company::class)->findAllCompaniesRelatedToMe($this->user);

        foreach($Companies as $Company){

            $UserPermissions = $this->entity(Permission::class)->findOneBy([
                "user" => $this->user->getId(),
                "company" => $Company->getId()
            ]);

            if(is_null($UserPermissions)){
                $UserPermissions = new Permission();
            }

            $Meetings = $this->entity(Meeting::class)->findAllMeetingsRelatedToMe($this->user, $UserPermissions, $Company);

            foreach($Meetings as $Meeting){
                $routeId = "company_meeting_open_meeting";
                if($Meeting->getStatus()==2){
                    $routeId = "company_meeting_view_meeting";
                }
                $Calendar[] = [
                    "id" => $Meeting->getId(),
                    "title" => $Meeting->getHeader(),
                    "start" => $Meeting->getBegin()->format("Y-m-d\TH:i:s"),
                    "end" => $Meeting->getEnd()->format("Y-m-d\TH:i:s"),
                    "detailRoute" => $this->get("router")->generate($routeId, [
                        "subdomain" => $Company->getDomain(),
                        "meetingId" => $Meeting->getId()
                    ])
                ];
            }

        }

        return new JsonResponse($Calendar, 200, []);

        $this->renderIt("MeetingBundle:Meeting:meetings.html.twig");
    }

    public function docAction($meetingId)
    {
        $this->renderMeetingBase($meetingId, [
            "meeting_subjects" => [],
            "meeting_members" => [],
            "meeting_reports" => [],
            "additional_reports" => []
        ]);

        $Category = $this->entity(MeetingCategory::class)->find($this->Meeting->getCategory());

        if(!$this->isGrantedMeeting()){
            throw $this->createAccessDeniedException($this->translator->trans("string.meeting_not_permitted_to_view", [], $this->transDomain, $this->getLocale()));
        }

        $this->data(["meeting_id" => $this->Meeting->getId()]);
        $this->data(["meeting_header" => $this->Meeting->getHeader()]);
        $this->data(["meeting_category" => $Category->getName()]);
        $Begin = $this->Meeting->getBegin()->format("d/m/Y H:i");
        $End = $this->Meeting->getEnd()->format("d/m/Y H:i");
        $this->data(["meeting_time" => "{$Begin} - {$End}"]);

        $MeetingSubjects = $this->entity(MeetingMatterSubject::class)->findBy([
            "meeting" => $meetingId
        ]);

        foreach($MeetingSubjects as $MeetingSubject){
            $belong_to_me = $MeetingSubject->getCreator() == $this->user->getId() ? true : false;
            $report = array(
                "header" => null,
                "content" => null
            );
            $relatedReport = $this->entity(MeetingReport::class)->findOneBy([
                "meeting" => $meetingId,
                "subject" => $MeetingSubject->getId()
            ]);
            if($relatedReport){
                $report = array(
                    "header" => $relatedReport->getName(),
                    "content" => $relatedReport->getContent()
                );
            }
            $this->data([
                "meeting_subjects" => [
                    [
                        "id" => $MeetingSubject->getId(),
                        "header" => $MeetingSubject->getName(),
                        "creator" => $this->entity(User::class)->find($MeetingSubject->getCreator())->getFullname(),
                        "company" => $this->entity(Company::class)->find($MeetingSubject->getCompany())->getName(),
                        "is_mine" => $belong_to_me,
                        "content" => $MeetingSubject->getContent(),
                        "report" => $report
                    ]
                ]
            ]);
        }

        $MeetingMembers = $this->entity(MeetingMember::class)->findBy([
            "meeting" => $meetingId
        ]);

        foreach($MeetingMembers as $MeetingMember){
            $Name = $this->translator->trans("string.member_role_guest", [], $this->transDomain, $this->getLocale());
            $Role = $this->translator->trans("string.member_role_member", [], $this->transDomain, $this->getLocale());
            $RoleId = 1;
            if($MeetingMember->getRole()==2){
                $Role = $this->translator->trans("string.member_role_reporter", [], $this->transDomain, $this->getLocale());
                $RoleId = 2;
            }
            if($MeetingMember->getRole()!=3){
                $Name = $this->entity(User::class)->find($MeetingMember->getUser())->getFullname();
            }else{
                $RoleId = 3;
                $Role = $this->translator->trans("string.member_role_guest", [], $this->transDomain, $this->getLocale());
                $CheckExternalUser = $User = $this->entity(User::class)->findOneBy([
                    "email" => $MeetingMember->getUserEmail()
                ]);
                if($CheckExternalUser){
                    $Name = $User->getFullname();
                    $CheckExternalUseronCompany = $this->entity(Personnel::class)->findOneBy([
                        "company" => $this->company->getId(),
                        "user" => $User->getId()
                    ]);
                    if($CheckExternalUseronCompany){
                        $Role = $this->translator->trans("string.member_role_member", [], $this->transDomain, $this->getLocale());
                    }
                }
            }
            $this->data([
                "meeting_members" => [
                    [
                        "id" => $MeetingMember->getId(),
                        "name" => $Name,
                        "email" => $MeetingMember->getUserEmail(),
                        "role" => $Role,
                        "role_id" => $RoleId
                    ]
                ]
            ]);
        }

        $AdditionalReports = $this->entity(MeetingReport::class)->findBy([
            "meeting" => $meetingId,
            "subject" => 0
        ]);

        foreach($AdditionalReports as $AdditionalReport){
            $Creator = $this->entity(User::class)->find($AdditionalReport->getCreator());
            $this->data([
                "additional_reports" => [
                    [
                        "id" => $AdditionalReport->getId(),
                        "header" => $AdditionalReport->getName(),
                        "content" => $AdditionalReport->getContent(),
                        "creator" => $Creator->getFullname(),
                        "company" => $this->entity(Company::class)->find($AdditionalReport->getCompany())->getName(),
                    ]
                ]
            ]);
        }

        return $this->renderIt("MeetingBundle:Meeting:doc.html.twig");
    }

    public function pdfAction($meetingId)
    {
        $this->renderMeetingBase($meetingId, [
            "meeting_subjects" => [],
            "meeting_members" => [],
            "meeting_reports" => [],
            "additional_reports" => []
        ]);

        $Category = $this->entity(MeetingCategory::class)->find($this->Meeting->getCategory());

        if(!$this->isGrantedMeeting(false)){
            throw $this->createAccessDeniedException($this->translator->trans("string.meeting_not_permitted_to_view", [], $this->transDomain, $this->getLocale()));
        }

        $this->data(["meeting_id" => $this->Meeting->getId()]);
        $this->data(["meeting_header" => $this->Meeting->getHeader()]);
        $this->data(["meeting_category" => $Category->getName()]);
        $Begin = $this->Meeting->getBegin()->format("d/m/Y H:i");
        $End = $this->Meeting->getEnd()->format("d/m/Y H:i");
        $this->data(["meeting_time" => "{$Begin} - {$End}"]);

        $MeetingSubjects = $this->entity(MeetingMatterSubject::class)->findBy([
            "meeting" => $meetingId
        ]);

        foreach($MeetingSubjects as $MeetingSubject){
            $belong_to_me = $MeetingSubject->getCreator() == $this->user->getId() ? true : false;
            $report = array(
                "header" => null,
                "content" => null
            );
            $relatedReport = $this->entity(MeetingReport::class)->findOneBy([
                "meeting" => $meetingId,
                "subject" => $MeetingSubject->getId()
            ]);
            if($relatedReport){
                $report = array(
                    "header" => $relatedReport->getName(),
                    "content" => $relatedReport->getContent()
                );
            }
            $this->data([
                "meeting_subjects" => [
                    [
                        "id" => $MeetingSubject->getId(),
                        "header" => $MeetingSubject->getName(),
                        "creator" => $this->entity(User::class)->find($MeetingSubject->getCreator())->getFullname(),
                        "company" => $this->entity(Company::class)->find($MeetingSubject->getCompany())->getName(),
                        "is_mine" => $belong_to_me,
                        "content" => $MeetingSubject->getContent(),
                        "report" => $report
                    ]
                ]
            ]);
        }

        $MeetingMembers = $this->entity(MeetingMember::class)->findBy([
            "meeting" => $meetingId
        ]);

        $i=1;
        foreach($MeetingMembers as $MeetingMember){
            $Name = $this->translator->trans("string.member_role_guest", [], $this->transDomain, $this->getLocale());
            $Role = $this->translator->trans("string.member_role_member", [], $this->transDomain, $this->getLocale());
            $RoleId = 1;
            if($MeetingMember->getRole()==2){
                $Role = $this->translator->trans("string.member_role_reporter", [], $this->transDomain, $this->getLocale());
                $RoleId = 2;
            }
            if($MeetingMember->getRole()!=3){
                $Name = $this->entity(User::class)->find($MeetingMember->getUser())->getFullname();
            }else{
                $RoleId = 3;
                $Role = $this->translator->trans("string.member_role_guest", [], $this->transDomain, $this->getLocale());
                $CheckExternalUser = false;
                if($MeetingMember->getUser() != null && $MeetingMember->getUser() != 0 && $MeetingMember->getUser() != false){
                    $CheckExternalUser = $User = $this->entity(User::class)->find($MeetingMember->getUser());
                }else{
                    $CheckExternalUser = $User = $this->entity(User::class)->findOneBy([
                        "email" => $MeetingMember->getUserEmail()
                    ]);
                }
                if($CheckExternalUser){
                    $Name = $User->getFullname();
                    $CheckExternalUseronCompany = $this->entity(Personnel::class)->findOneBy([
                        "company" => $this->company->getId(),
                        "user" => $User->getId()
                    ]);
                    if($CheckExternalUseronCompany){
                        $Role = $this->translator->trans("string.member_role_member", [], $this->transDomain, $this->getLocale());
                    }
                }else{
                    $Name = $MeetingMember->getUserEmail();
                }
            }
            $index = $i;
            if($RoleId==2){
                $index = 0;
            }
            $this->data([
                "meeting_members" => [
                    $index => [
                        "id" => $MeetingMember->getId(),
                        "name" => $Name,
                        "email" => $MeetingMember->getUserEmail(),
                        "role" => $Role,
                        "role_id" => $RoleId
                    ]
                ]
            ]);
            $i++;
        }

        arsort($this->renderData["meeting_members"]);

        $AdditionalReports = $this->entity(MeetingReport::class)->findBy([
            "meeting" => $meetingId,
            "subject" => 0
        ]);

        foreach($AdditionalReports as $AdditionalReport){
            $Creator = $this->entity(User::class)->find($AdditionalReport->getCreator());
            $this->data([
                "additional_reports" => [
                    [
                        "id" => $AdditionalReport->getId(),
                        "header" => $AdditionalReport->getName(),
                        "content" => $AdditionalReport->getContent(),
                        "creator" => $Creator->getFullname(),
                        "company" => $this->entity(Company::class)->find($AdditionalReport->getCompany())->getName(),
                    ]
                ]
            ]);
        }

        $ServerName = $this->protocol_scheme."://".$this->companyDomain.".".$this->param("hostname");
        $this->data(["server_name" => $ServerName]);

        $FileName = $this->Meeting->getHeader()." ".$this->Meeting->getBegin()->format("d.m.Y H:i")." - ".$this->Meeting->getEnd()->format("d.m.Y H:i");
        $this->renderData["title"] = $FileName;
        $FileContent = $this->renderView("MeetingBundle:Meeting:doc.html.twig", $this->renderData);

        $PDF = $this->get("knp_snappy.pdf")->getOutputFromHtml($FileContent);

        $response = new Response();
        $response->setContent($PDF);
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set("Content-Type", "application/pdf");
        $response->headers->set("Content-Length", strlen($PDF));
        $response->headers->set("Content-Disposition", "filename=\"{$FileName}.pdf\"");

        $response->send();
    }

    public function viewAction($meetingId)
    {
        $this->renderMeetingBase($meetingId, [], "meeting", false);

        $this->renderData["title"] .= " > ".$this->translator->trans("string.view", [], "meeting");

        $Category = $this->entity(MeetingCategory::class)->find($this->Meeting->getCategory());

        if(!$this->isGrantedMeeting(false)){
            throw $this->createAccessDeniedException($this->translator->trans("string.meeting_not_permitted_to_view", [], $this->transDomain, $this->getLocale()));
        }

        $this->data(["meeting_id" => $this->Meeting->getId()]);
        $this->data(["meeting_header" => $this->Meeting->getHeader()]);
        $this->data(["meeting_category" => $Category->getName()]);
        $Begin = $this->Meeting->getBegin()->format("d/m/Y H:i");
        $End = $this->Meeting->getEnd()->format("d/m/Y H:i");
        $this->data(["meeting_time" => "{$Begin} - {$End}"]);

        return $this->renderIt("MeetingBundle:Meeting:view.html.twig");
    }

    public function openAction($meetingId)
    {
        $this->renderMeetingBase($meetingId, [
            "meeting_subjects" => [],
            "meeting_members" => [],
            "meeting_objects" => [],
            "personnels" => [],
            "meeting_reports" => []
        ]);

        $this->renderData["title"] .= " > ".$this->translator->trans("string.details", [], "meeting");

        $Category = $this->entity(MeetingCategory::class)->find($this->Meeting->getCategory());

        if(!$this->isGrantedMeeting()){

            return $this->redirectToRoute("company_meeting_view_meeting", [
                "subdomain" => $this->companyDomain,
                "meetingId" => $this->Meeting->getId()
            ]);
        }

        if(!$this->isGrantedMeeting()){
            throw $this->createAccessDeniedException($this->translator->trans("string.meeting_not_permitted_to_view", [], $this->transDomain, $this->getLocale()));
        }

        $this->data(["meeting_id" => $this->Meeting->getId()]);
        $this->data(["meeting_header" => $this->Meeting->getHeader()]);
        $this->data(["meeting_category" => $Category->getName()]);
        $Begin = $this->Meeting->getBegin()->format("d/m/Y H:i");
        $End = $this->Meeting->getEnd()->format("d/m/Y H:i");
        $this->data(["meeting_time" => "{$Begin} - {$End}"]);
        $this->data(["meeting_status" => $this->Meeting->getStatus()]);

        $MeetingSubjects = $this->entity(MeetingMatterSubject::class)->findBy([
            "meeting" => $meetingId
        ]);

        foreach($MeetingSubjects as $MeetingSubject){
            $belong_to_me = $MeetingSubject->getCreator() == $this->user->getId() ? true : false;
            $subjectReport = array(
                "id" => null,
                "content" => null
            );
            $Report = $this->entity(MeetingReport::class)->findOneBy([
                "subject" => $MeetingSubject->getId()
            ]);
            if($Report){
                $subjectReport = array(
                    "id" => $Report->getId(),
                    "content" => $Report->getContent()
                );
            }
            $this->data([
                "meeting_subjects" => [
                    [
                        "id" => $MeetingSubject->getId(),
                        "header" => $MeetingSubject->getName(),
                        "creator" => $this->entity(User::class)->find($MeetingSubject->getCreator())->getFullname(),
                        "company" => $this->entity(Company::class)->find($MeetingSubject->getCompany())->getName(),
                        "is_mine" => $belong_to_me,
                        "content" => $MeetingSubject->getContent(),
                        "report" => $subjectReport
                    ]
                ]
            ]);
        }

        $MeetingMembers = $this->entity(MeetingMember::class)->findBy([
            "meeting" => $meetingId
        ]);

        foreach($MeetingMembers as $MeetingMember){
            $Name = $this->translator->trans("string.member_role_guest", [], $this->transDomain, $this->getLocale());
            $Role = $this->translator->trans("string.member_role_member", [], $this->transDomain, $this->getLocale());
            $RoleId = 1;
            if($MeetingMember->getRole()==2){
                $Role = $this->translator->trans("string.member_role_reporter", [], $this->transDomain, $this->getLocale());
                $RoleId = 2;
            }
            if($MeetingMember->getRole()!=3){
                $Name = $this->entity(User::class)->find($MeetingMember->getUser())->getFullname();
            }else{
                $RoleId = 3;
                $Role = $this->translator->trans("string.member_role_guest", [], $this->transDomain, $this->getLocale());
                $CheckExternalUser = $User = $this->entity(User::class)->findOneBy([
                    "email" => $MeetingMember->getUserEmail()
                ]);
                if($CheckExternalUser){
                    $Name = $User->getFullname();
                    $CheckExternalUseronCompany = $this->entity(Personnel::class)->findOneBy([
                        "company" => $this->company->getId(),
                        "user" => $User->getId()
                    ]);
                    if($CheckExternalUseronCompany){
                        $Role = $this->translator->trans("string.member_role_member", [], $this->transDomain, $this->getLocale());
                    }
                }
            }
            $this->data([
                "meeting_members" => [
                    [
                        "id" => $MeetingMember->getId(),
                        "name" => $Name,
                        "email" => $MeetingMember->getUserEmail(),
                        "role" => $Role,
                        "role_id" => $RoleId
                    ]
                ]
            ]);
        }

        $MeetingObjects = $this->entity(MeetingObject::class)->findBy([
            "meeting" => $meetingId
        ]);

        $defaultPreview = "/bundles/Makrosum/Meeting/_assets/img/document.png";

        foreach($MeetingObjects as $MeetingObject){
            $objectPreview = $defaultPreview;
            $parseResource = explode(".", $MeetingObject->getResource());
            if(
                $parseResource[count($parseResource)-1]=="jpg"
                ||$parseResource[count($parseResource)-1]=="png"
                ||$parseResource[count($parseResource)-1]=="gif"
            ){
                $objectPreview = "/bundles/Makrosum/Meeting/Objects/".$MeetingObject->getResource();
            }
            $this->data([
                "meeting_objects" => [
                    [
                        "id" => $MeetingObject->getId(),
                        "object_link" => $MeetingObject->getResource(),
                        "object_preview" => $objectPreview
                    ]
                ]
            ]);
        }

        $Personnels = $this->entity(Personnel::class)->findBy([
            "company" => $this->company->getId()
        ]);

        foreach($Personnels as $Personnel){
            $checkRelationToThisMeeting = $this->entity(MeetingMember::class)->findOneBy([
                "meeting" => $meetingId,
                "user" => $Personnel->getUser()
            ]);
            if($checkRelationToThisMeeting){
                continue;
            }
            $User = $this->entity(User::class)->find($Personnel->getUser());
            $this->data([
                "personnels" => [
                    [
                        "id" => $User->getId(),
                        "name" => $User->getFullname()
                    ]
                ]
            ]);
        }

        $MeetingReports = $this->entity(MeetingReport::class)->findBy([
            "company" => $this->company->getId(),
            "meeting" => $meetingId,
            "subject" => 0
        ]);

        foreach($MeetingReports as $MeetingReport){
            $Creator = $this->entity(User::class)->find($MeetingReport->getCreator());
            $this->data([
                "meeting_reports" => [
                    [
                        "id" => $MeetingReport->getId(),
                        "header" => $MeetingReport->getName(),
                        "content" => $MeetingReport->getContent(),
                        "creator" => $Creator->getFullname(),
                        "company" => $this->entity(Company::class)->find($MeetingReport->getCompany())->getName(),
                    ]
                ]
            ]);
        }

        return $this->renderIt("MeetingBundle:Meeting:details.html.twig");
    }

    public function createAction()
    {
        $this->renderMeetingBase();

        $MeetingStatus = ($this->request->get("status")) ? $this->request->get("status") : 1;

        $Meeting = new Meeting();
        $Meeting->setCreator($this->user)
            ->setCompany($this->company)
            ->setCategory($this->entity(MeetingCategory::class)->find($this->request->get("category")))
            ->setHeader($this->request->get("header"))
            ->setBegin(new \DateTime($this->request->get("begin")))
            ->setEnd($this->request->get("end"))
            ->setStatus($MeetingStatus);

        $this->em()->em_persist($Meeting)->em_flush();

        $status = true;
        $message = null;

        if(!$Meeting){
            $status = false;
            $message = $this->translator->trans("string.meeting_could_not_create", [], $this->transDomain, $this->getLocale());
        }else{
            $route = $this->get("router")->generate("company_meeting_open_meeting", [
                "subdomain" => $this->companyDomain,
                "meetingId" => $Meeting->getId()
            ]);
            $this->data(["route" => $route]);
        }

        return $this->renderJson([
            "status" => $status,
            "message" => $message
        ]);
    }

    public function editAction($meetingId)
    {
        $this->renderMeetingBase($meetingId, [], "meeting");

        if(!$this->isGrantedMeeting()){
            throw $this->createAccessDeniedException($this->translator->trans("string.meeting_not_permitted_to_view", [], $this->transDomain, $this->getLocale()));
        }

        $Meeting = $this->entity(Meeting::class)->find($meetingId);

        $Meeting->setHeader($this->request->get("header"));
        $Meeting->setCategory($this->entity(MeetingCategory::class)->find($this->request->get("category")));
        $Meeting->setBegin(new \DateTime($this->request->get("begin")));
        $Meeting->setEnd(new \DateTime($this->request->get("end")));

        $this->em_flush();

        $status = true;
        $message = null;

        return $this->renderJson([
            "status" => $status,
            "message" => $message
        ], 200);
    }

    public function cancelAction($meetingId)
    {
        $this->renderMeetingBase($meetingId);

        if(!$this->isGrantedMeeting()){
            throw $this->createAccessDeniedException($this->translator->trans("string.meeting_not_permitted_to_view", [], $this->transDomain, $this->getLocale()));
        }

        $Meeting = $this->entity(Meeting::class)->find($meetingId);

        $Meeting->setStatus(3);

        $this->em_flush();

        return $this->renderJson();
    }

    public function re_setAction($meetingId)
    {
        $this->renderMeetingBase($meetingId);

        if(!$this->isGrantedMeeting()){
            throw $this->createAccessDeniedException($this->translator->trans("string.meeting_not_permitted_to_view", [], $this->transDomain, $this->getLocale()));
        }

        $Meeting = $this->entity(Meeting::class)->find($meetingId);

        $Meeting->setStatus(1);

        $this->em_flush();

        return $this->renderJson();
    }

    public function approveAction($meetingId)
    {
        $this->renderMeetingBase($meetingId);

        if(!$this->isGrantedMeeting()){
            throw $this->createAccessDeniedException($this->translator->trans("string.meeting_not_permitted_to_view", [], $this->transDomain, $this->getLocale()));
        }

        $Meeting = $this->entity(Meeting::class)->find($meetingId);

        $Meeting->setStatus(1);

        $this->em_flush();

        return $this->renderJson();
    }

    public function completeAction($meetingId)
    {
        $this->renderMeetingBase($meetingId);

        if(!$this->isGrantedMeeting()){
            throw $this->createAccessDeniedException($this->translator->trans("string.meeting_not_permitted_to_view", [], $this->transDomain, $this->getLocale()));
        }

        $Meeting = $this->entity(Meeting::class)->find($meetingId);

        $Meeting->setStatus(2);

        $this->em_flush();

        $MeetingMembers = $this->entity(MeetingMember::class)->findBy([
            "meeting" => $meetingId
        ]);

        foreach($MeetingMembers as $MeetingMember){
            $User = $this->entity(User::class)->findOneBy([
                "email" => $MeetingMember->getUserEmail()
            ]);

            $UserSettings = false;
            if($User){
                $UserSettings = $this->entity(UserSettings::class)->findOneBy([
                    "user_id" => $User->getId()
                ]);
            }

            $MeetingUrl = $this->get("router")->generate("company_meeting_view_meeting", [
                "subdomain" => $this->companyDomain,
                "meetingId" => $this->Meeting->getId()
            ]);

            if($UserSettings){
                $SmsNotification = null;
                if($UserSettings->getSmsNotification()){
                    $SmsNotification = $this->translator->trans("string.meeting_complete_published", [
                        "%meeting%" => $Meeting->getHeader(),
                    ], "notification", $UserSettings->getLanguage());
                    $SmsStream = $this->get("MakrosumNotificationStream.SMS");
                    //$SmsStream->sendToGroup($SmsNotification, [substr($User->getPhone(), -10)]);
                }

                $MailNotification = null;
                if($UserSettings->getEmailNotification()){
                    $MailNotification = \Swift_Message::newInstance()
                        ->setSubject($this->translator->trans("string.notification_title", [], "notification", $UserSettings->getLanguage()))
                        ->setFrom(array($this->param("mailer_sender") => $this->param("global.project_name")))
                        ->setTo($User->getEmail())
                        ->setBody(
                            $this->renderView(
                                "MeetingBundle:Emails:meeting_completed.html.twig",
                                [
                                    "trans_l" => $this->getLocale(),
                                    "base_hostname" => $this->baseHostname,
                                    "language" => $UserSettings->getLanguage(),
                                    "meeting" => $Meeting->getHeader(),
                                    "begin" => $Meeting->getBegin()->format("d.m.Y H:i"),
                                    "end" => $Meeting->getEnd()->format("d.m.Y H:i"),
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
        }

        return $this->renderJson();
    }

    public function webcalAction($meetingId)
    {
        $this-$this->renderMeetingBase($meetingId);



        $Company = $this->entity(Company::class)->find($this->Meeting->getCompany());

        $MeetingMembers = $this->entity(MeetingMember::class)->findBy([
            "meeting" => $meetingId
        ]);

        function dateToCal($timestamp) {
            return date('Ymd\THis', $timestamp);
        }

        function escapeString($string) {
            return preg_replace('/([\,;])/','\\\$1', $string);
        }

        $Url = $this->get("router")->generate("company_meeting_open_meeting", [
            "subdomain" => $Company->getDomain(),
            "meetingId" => $this->Meeting->getId()
        ]);

        $eol = "\r\n";
        $Calendar = "BEGIN:VCALENDAR" . $eol .
            "VERSION:2.0" . $eol .
            "PRODID:-//" . $Company->getDomain() . "/meeting//NONSGML v1.0//EN" . $eol .
            "CALSCALE:GREGORIAN" . $eol .
            "BEGIN:VEVENT" . $eol .
            "DTSTART:" . $this->Meeting->getBegin()->format("Ymd\THis") . $eol .
            "UID:" . $this->Meeting->getId() . $eol .
            "DTSTAMP:" . dateToCal(time()) . $eol .
            "DESCRIPTION:" . htmlspecialchars($this->Meeting->getHeader()) . $eol .
            #"URL;VALUE=URI:" . $this->protocol_scheme . "://" . $Company->getDomain() . "." . $this->param("hostname") . htmlspecialchars($Url) . $eol .
            "URL;VALUE=URI:" . $this->protocol_scheme . ":" . htmlspecialchars($Url) . $eol .
            "SUMMARY:" . htmlspecialchars($this->Meeting->getHeader()) . $eol .
            "DTEND:" . $this->Meeting->getEnd()->format("Ymd\THis") . $eol;
        if($MeetingMembers){
            foreach($MeetingMembers as $MeetingMember){
                if($MeetingMember->getRole()!=3){
                    $User = $this->entity(User::class)->find($MeetingMember->getUser());
                    $Calendar .= "ATTENDEE;ROLE=REQ-PARTICIPANT;CN=" . $User->getFullname() . " :MAILTO:" . $User->getEmail() . $eol;
                }else{
                    $User = $this->entity(User::class)->findOneBy([
                        "email" => $MeetingMember->getUserEmail()
                    ]);
                    if($User){
                        $Calendar .= "ATTENDEE;ROLE=REQ-PARTICIPANT;CN=" . $User->getFullname() . " :MAILTO:" . $User->getEmail() . $eol;
                    }else{
                        $Calendar .= "ATTENDEE;ROLE=REQ-PARTICIPANT;CN=" . $MeetingMember->getUserEmail() . " :MAILTO:" . $MeetingMember->getUserEmail() . $eol;
                    }
                }
            }
        }
        $Calendar .= "END:VEVENT" . $eol .
            "END:VCALENDAR";

        $filename = $this->Meeting->getHeader().".ics";
        // Set the headers
        header('Content-type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        exit($Calendar);

        return $this->renderJson();
    }
}