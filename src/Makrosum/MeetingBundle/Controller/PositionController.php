<?php
/**
 * Created by PhpStorm.
 * User: Musa ATALAY
 * Date: 11.03.2016
 * Time: 01:01
 */

namespace Makrosum\MeetingBundle\Controller;


use Makrosum\MeetingBundle\Abstractions\CompanyBaseController;
use Makrosum\MeetingBundle\Entity\Position;
use Makrosum\MeetingBundle\MeetingBundle;

class PositionController extends CompanyBaseController
{

    public function getAllJsonAction()
    {
        $this->renderCompanyBase(["positions" => []]);

        if(!$this->user_permissions->isGranted(MeetingBundle::GRANTED_SUPER_PERSONNEL)){
            throw $this->createAccessDeniedException($this->translator->trans("string.module_not_permitted", [], "security", $this->getLocale()));
        }

        $this->data(["positions" => [
            "*" => [
                "id" => "*",
                "name" => $this->translator->trans("string.please_select", [], $this->transDomain, $this->getLocale())
            ]
        ]]);

        $positions = $this->entity(Position::class)->findBy([
            "company" => $this->company->getId(),
            "department" => $this->request->get("departmentId")
        ]);


        foreach($positions as $position){
            $this->data([
                "positions" => [
                    $position->getId() => [
                        "id" => $position->getId(),
                        "name" => $position->getName()
                    ]
                ]
            ]);
        }

        return $this->renderIt("MeetingBundle:Global:load_positions.html.twig");

    }

    public function checkPositionAction($departmentId, $positionName)
    {
        $this->renderCompanyBase();

        if(!$this->user_permissions->isGranted(MeetingBundle::GRANTED_SUPER_PERSONNEL)){
            throw $this->createAccessDeniedException($this->translator->trans("string.module_not_permitted", [], "security", $this->getLocale()));
        }

        $Position = $this->entity(Position::class)->findBy([
            "company" => $this->company->getId(),
            "department" => $departmentId,
            "name" => $positionName
        ]);

        $status = true;
        $message = null;

        if(!$Position){
            $status = false;
            $message = $this->translator->trans("string.position_could_not_found", [], $this->transDomain, $this->getLocale());
        }

        return $this->renderJson([
            "status" => $status,
            "message" => $message,
            "department" => $positionName
        ], 200);

        exit(self::class."::checkPositiont({$departmentId}, {$positionName})");
    }

    public function newPositionAction($departmentId)
    {
        $this->renderCompanyBase();

        if(!$this->user_permissions->isGranted(MeetingBundle::GRANTED_SUPER_PERSONNEL)){
            throw $this->createAccessDeniedException($this->translator->trans("string.module_not_permitted", [], "security", $this->getLocale()));
        }

        $Position = new Position();
        $Position->setCompany($this->company->getId())
            ->setDepartment($departmentId)
            ->setName($this->request->get("name"));

        $this->em()->em_persist($Position)->em_flush();

        $status = true;
        $message = null;

        if (!$Position) {
            $status = false;
            $message = $this->translator->trans("string.position_could_not_created", [], $this->transDomain, $this->getLocale());
        }

        return $this->renderJson([
            "status" => $status,
            "message" => $message
        ], 200);

        exit(self::class."::newPosition({$departmentId})");
    }

    public function editPositionAction($departmentId, $positionId)
    {
        $this->renderCompanyBase();

        if(!$this->user_permissions->isGranted(MeetingBundle::GRANTED_SUPER_PERSONNEL)){
            throw $this->createAccessDeniedException($this->translator->trans("string.module_not_permitted", [], "security", $this->getLocale()));
        }

        $Position = $this->entity(Position::class)->find($positionId);

        $status = true;
        $message = null;

        if(!$Position){
            $status = false;
            $message = $this->translator->trans("string.position_could_not_found", [], $this->transDomain, $this->getLocale());
        }else{
            $Position->setName($this->request->get("name"));
            $this->em_flush();
        }

        return $this->renderJson([
            "status" => $status,
            "message" => $message,
            "department" => $departmentId,
            "position" => $positionId,
            "position_name" => $this->request->get("name")
        ], 200);

        exit(self::class."::editPosition({$departmentId}, {$positionId})");
    }

    public function deletePositionAction($departmentId, $positionId)
    {
        $this->renderCompanyBase();

        if(!$this->user_permissions->isGranted(MeetingBundle::GRANTED_SUPER_PERSONNEL)){
            throw $this->createAccessDeniedException($this->translator->trans("string.module_not_permitted", [], "security", $this->getLocale()));
        }

        $Position = $this->entity(Position::class)->find($positionId);

        $status = true;
        $message = null;

        if(!$Position){
            $status = false;
            $message = $this->translator->trans("string.position_could_not_found", [], $this->transDomain, $this->getLocale());
        }else{
            $this->em_remove($Position);
            $this->em_flush();
        }

        return $this->renderJson([
            "status" => $status,
            "message" => $message,
            "department" => $departmentId,
            "position" => $positionId
        ], 200);

        exit(self::class."::deletePosition({$departmentId}, {$positionId})");
    }

}