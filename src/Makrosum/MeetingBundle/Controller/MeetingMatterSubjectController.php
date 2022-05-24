<?php
/**
 * Created by PhpStorm.
 * User: musaatalay
 * Date: 21.03.2016
 * Time: 04:49
 */

namespace Makrosum\MeetingBundle\Controller;


use Makrosum\MeetingBundle\Abstractions\CompanyBaseController;
use Makrosum\MeetingBundle\Abstractions\MeetingBaseController;
use Makrosum\MeetingBundle\Entity\Meeting;
use Makrosum\MeetingBundle\Entity\MeetingMatterSubject;
use Makrosum\MeetingBundle\Entity\MeetingReport;

class MeetingMatterSubjectController extends MeetingBaseController
{
    public function create_subjectAction($meetingId)
    {
        $this->renderMeetingBase($meetingId);

        $MeetingSubject = new MeetingMatterSubject();
        $MeetingSubject->setCreator($this->user);
        $MeetingSubject->setCompany($this->company);
        $MeetingSubject->setMeeting($this->entity(Meeting::class)->find($meetingId));
        $MeetingSubject->setName($this->request->get("name"));

        if($this->request->get("content")){
            $MeetingSubject->setContent($this->request->get("content"));
        }

        $this->em()->em_persist($MeetingSubject)->em_flush();
        $this->em_clear();

        $status = true;
        $message = null;

        if(!$MeetingSubject){
            $status = false;
            $message = "Hata";
        }

        $MeetingReport = new MeetingReport();
        $MeetingReport->setCreator($this->user);
        $MeetingReport->setCompany($this->company);
        $MeetingReport->setMeeting($this->Meeting);
        $MeetingReport->setSubject($MeetingSubject);
        $MeetingReport->setName($this->request->get("name"));

        $this->em()->em_persist($MeetingReport)->em_flush();
        $this->em_clear();

        return $this->renderJson([
            "status" => $status,
            "message" => $message
        ]);
    }
    public function edit_subjectAction($matterSubjectId)
    {
        $this->renderCompanyBase([], "meeting");

        $MeetingSubject = $this->entity(MeetingMatterSubject::class)->find($matterSubjectId);

        $MeetingSubject->setName($this->request->get("name"));

        if($this->request->get("content")){
            $MeetingSubject->setContent($this->request->get("content"));
        }

        $this->em_flush();

        $status = true;
        $message = null;

        if(!$MeetingSubject){
            $status = false;
            $message = "Hata";
        }

        return $this->renderJson([
            "status" => $status,
            "message" => $message
        ]);
    }
    public function remove_subjectAction($matterSubjectId)
    {
        $this->renderCompanyBase([], "meeting");

        $MeetingSubject = $this->entity(MeetingMatterSubject::class)->find($matterSubjectId);

        $this->em_remove($MeetingSubject)->em_flush();

        $status = true;
        $message = null;

        return $this->renderJson([
            "status" => $status,
            "message" => $message
        ]);
    }
}