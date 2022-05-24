<?php
/**
 * Created by PhpStorm.
 * User: musaatalay
 * Date: 24.03.2016
 * Time: 12:11
 */

namespace Makrosum\MeetingBundle\Controller;


use Makrosum\MeetingBundle\Abstractions\MeetingBaseController;
use Makrosum\MeetingBundle\Entity\MeetingReport;
use Makrosum\MeetingBundle\Entity\MeetingMember;
use Makrosum\MeetingBundle\Entity\User;
use Makrosum\MeetingBundle\Entity\UserSettings;

class MeetingReportController extends MeetingBaseController
{
    public function create_reportAction($meetingId)
    {
        $this->renderMeetingBase($meetingId, []);

        $Meeting = $this->entity(Meeting::class)->find($meetingId);

        $MeetingReport = new MeetingReport();
        $MeetingReport->setCreator($this->user);
        $MeetingReport->setCompany($this->company);
        $MeetingReport->setMeeting($Meeting);
        $MeetingReport->setName($this->request->get("name"));

        if($this->request->get("content")){
            $MeetingReport->setContent($this->request->get("content"));
        }

        $this->em()->em_persist($MeetingReport)->em_flush();

        $status = true;
        $message = null;

        if(!$MeetingReport){
            $status = false;
            $message = "Hata";
        }

        return $this->renderJson([
            "status" => $status,
            "message" => $message
        ]);
    }

    public function edit_reportAction($meetingId, $reportId)
    {
        $this->renderMeetingBase($meetingId, []);

        $MeetingReport = $this->entity(MeetingReport::class)->find($reportId);

        $MeetingReport->setName($this->request->get("name"));

        if($this->request->get("content")){
            $MeetingReport->setContent($this->request->get("content"));
        }

        $this->em_flush();

        $status = true;
        $message = null;

        if(!$MeetingReport){
            $status = false;
            $message = "Hata";
        }

        return $this->renderJson([
            "status" => $status,
            "message" => $message
        ]);
    }

    public function remove_reportAction($meetingId, $reportId)
    {
        $this->renderMeetingBase($meetingId, []);

        $MeetingReport = $this->entity(MeetingReport::class)->find($reportId);

        $this->em_remove($MeetingReport)->em_flush();

        $status = true;
        $message = null;

        return $this->renderJson([
            "status" => $status,
            "message" => $message
        ]);
    }

    public function download_reportAction($meetingId, $reportId)
    {
        $this->renderMeetingBase($meetingId, []);

        $MeetingReport = $this->entity(MeetingReport::class)->find($reportId);

        $status = true;
        $message = null;

        return $this->renderJson([
            "status" => $status,
            "message" => $message
        ]);
    }
}