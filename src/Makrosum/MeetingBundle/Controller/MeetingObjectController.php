<?php
/**
 * Created by PhpStorm.
 * User: musaatalay
 * Date: 20.03.2016
 * Time: 13:23
 */

namespace Makrosum\MeetingBundle\Controller;


use Makrosum\MeetingBundle\Abstractions\CompanyBaseController;
use Makrosum\MeetingBundle\Entity\Meeting;
use Makrosum\MeetingBundle\Entity\MeetingObject;

class MeetingObjectController extends CompanyBaseController
{
    public function upload_streamAction($meetingId)
    {
        $this->renderCompanyBase();

        $this->request->files->get("meeting_object")->move(
            $this->get("kernel")->getMeetingObjectDir(),
            $this->request->files->get("meeting_object")->getClientOriginalName()
        );

        $MeetingObject = new MeetingObject();
        $MeetingObject->setCompany($this->company);
        $MeetingObject->setCreator($this->user);
        $MeetingObject->setMeeting($this->entity(Meeting::class)->find($meetingId));
        $MeetingObject->setResource($this->request->files->get("meeting_object")->getClientOriginalName());

        $this->em()->em_persist($MeetingObject)->em_flush();

        $status = true;
        $message = null;
        $objectId = null;
        $objectUrl = null;

        if(!$MeetingObject){
            $status = false;
            $message = "Hata";
        }else{
            $objectId = $MeetingObject->getId();
            $objectUrl = $MeetingObject->getResource();
        }

        return $this->renderJson([
            "status" => $status,
            "objectId" => $objectId,
            "objectUrl" => $objectUrl,
            "message" => $message
        ], 200);
    }
    public function remove_streamAction($objectId)
    {
        $this->renderCompanyBase();

        $MeetingObject = $this->entity(MeetingObject::class)->find($objectId);

        $this->em_remove($MeetingObject)->em_flush();

        return $this->renderJson();
    }
}