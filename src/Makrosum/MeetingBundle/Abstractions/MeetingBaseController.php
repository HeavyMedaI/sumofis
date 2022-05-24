<?php
/**
 * Created by PhpStorm.
 * User: musaatalay
 * Date: 22.03.2016
 * Time: 02:23
 */

namespace Makrosum\MeetingBundle\Abstractions;

use Makrosum\MeetingBundle\MeetingBundle;
use Makrosum\MeetingBundle\Entity\Meeting;
use Makrosum\MeetingBundle\Entity\MeetingMember;

class MeetingBaseController extends CompanyBaseController
{

    protected $Meeting;

    public function renderMeetingBase($meetingId = false, Array $base_data = array(), $trans_domain = "meeting", $checkDeeply = true)
    {
        $this->renderCompanyBase($base_data, $trans_domain);
        $this->transDomain($trans_domain);
        $this->renderData["IS_GRANTED_SUPER_MEETING"] = false;
        $this->data([
            "IS_GRANTED_CREATE_MEETING" => false,
            "IS_GRANTED_EDIT_MEETING" => false,
            "IS_GRANTED_STATUS_MEETING"  => false,
            "IS_GRANTED_DELETE_MEETING"  => false,
            "IS_GRANTED_REQUEST_MEETING" => false,
            "IS_GRANTED_MATTER_MEETING"  => false,
            "IS_GRANTED_REPORT_MEETING"  => false
        ]);
        if($meetingId){
            $this->Meeting = $this->entity(Meeting::class)->find($meetingId);
            if(!$this->Meeting){
                throw $this->createNotFoundException($this->translator->trans("string.meeting_could_not_found", [], $trans_domain, $this->getLocale()));
            }
            $this->renderData["title"] .= " > ".$this->Meeting->getHeader();
        }
        if($this->check_relations["meeting"]){
            if(!$this->isGrantedMeeting()){
                $render = $this->redirectToRoute("company_homepage", ["subdomain" => $this->company->getDomain()]);
                $render->send();
            }
        }
        if($this->user_permissions){
            if($this->user_permissions->isGranted(MeetingBundle::GRANTED_CREATE_MEETING)){
                $this->renderData["IS_GRANTED_CREATE_MEETING"] = true;
                $this->renderData["IS_GRANTED_REQUEST_MEETING"] = true;
                $this->renderData["IS_GRANTED_MATTER_MEETING"] = true;
                $this->renderData["IS_GRANTED_REPORT_MEETING"] = true;
            }
            if($this->user_permissions->isGranted(MeetingBundle::GRANTED_EDIT_MEETING)){
                $this->renderData["IS_GRANTED_EDIT_MEETING"] = true;
                $this->renderData["IS_GRANTED_REQUEST_MEETING"] = true;
                $this->renderData["IS_GRANTED_MATTER_MEETING"] = true;
            }
            if($this->user_permissions->isGranted(MeetingBundle::GRANTED_STATUS_MEETING)){
                $this->renderData["IS_GRANTED_STATUS_MEETING"] = true;
                $this->renderData["IS_GRANTED_EDIT_MEETING"] = true;
                $this->renderData["IS_GRANTED_REQUEST_MEETING"] = true;
                $this->renderData["IS_GRANTED_MATTER_MEETING"] = true;
            }
            if($this->user_permissions->isGranted(MeetingBundle::GRANTED_REQUEST_MEETING)){
                $this->renderData["IS_GRANTED_REQUEST_MEETING"] = true;
                $this->renderData["IS_GRANTED_MATTER_MEETING"] = true;
            }
            if($this->user_permissions->isGranted(MeetingBundle::GRANTED_MATTER_MEETING)){
                $this->renderData["IS_GRANTED_MATTER_MEETING"] = true;
            }
            if($this->isGrantedReporter()){
                $this->renderData["IS_GRANTED_REPORT_MEETING"] = true;
            }
        }
    }

    public function isGrantedMeeting($checkDeeply = true)
    {
        $MeetingMemberWithId = $this->entity(MeetingMember::class)->findOneBy([
            "meeting" => $this->Meeting->getId(),
            "user" => $this->user->getId()
        ]);

        $MeetingMemberWithEmail = $this->entity(MeetingMember::class)->findOneBy([
            "meeting" => $this->Meeting->getId(),
            "user_email" => $this->user->getEmail()
        ]);

        if(
            $this->company->isOwner($this->user)
            ||$this->Meeting->isCreator($this->user)
            ||$MeetingMemberWithId
            ||$MeetingMemberWithEmail
        ){
            if($this->Meeting->getStatus()==2){
                if($checkDeeply){
                    if(
                        $this->company->isOwner($this->user)
                        ||(
                            $this->isRelated()
                            &&(
                                $this->user_permissions->isGranted(MeetingBundle::GRANTED_SUPER_MEETING)
                                ||$this->user_permissions->isGranted(MeetingBundle::GRANTED_CREATE_MEETING)
                            )
                        )
                    ){
                        return true;
                    }
                }else{
                    return true;
                }
            }else{
                return true;
            }
        }
        return false;
    }

    public function isGrantedReporter(){
        if($this->Meeting){
            $MeetingMemberWithId = $this->entity(MeetingMember::class)->findOneBy([
                "user" => $this->user->getId(),
                "meeting" => $this->Meeting->getId()
            ]);
            if($MeetingMemberWithId&&$MeetingMemberWithId->getRole()==2){
                return true;
            }
        }
        return false;
    }
}