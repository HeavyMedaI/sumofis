<?php
/**
 * Created by PhpStorm.
 * User: Musa ATALAY
 * Date: 8.03.2016
 * Time: 21:47
 */

namespace Makrosum\MeetingBundle\Controller;


use Makrosum\MeetingBundle\Abstractions\CompanyBaseController;
use Makrosum\MeetingBundle\Entity\Department;
use Makrosum\MeetingBundle\Entity\Position;
use Makrosum\MeetingBundle\MeetingBundle;

class DepartmentController extends CompanyBaseController
{

    public function checkDepartmentAction($departmentName)
    {
        $this->renderCompanyBase();

        if(!$this->user_permissions->isGranted(MeetingBundle::GRANTED_SUPER_PERSONNEL)){
            throw $this->createAccessDeniedException($this->translator->trans("string.module_not_permitted", [], "security", $this->getLocale()));
        }

        $Department = $this->entity(Department::class)->findBy([
            "company" => $this->company->getId(),
            "name" => $departmentName
        ]);

        $status = true;
        $message = null;

        if(!$Department){
            $status = false;
            $message = $this->translator->trans("string.department_could_not_found", [], $this->transDomain, $this->getLocale());
        }

        return $this->renderJson([
            "status" => $status,
            "message" => $message,
            "department" => $departmentName
        ], 200);

        exit(self::class."::checkDepartment({$departmentName})");
    }

    public function newDepartmentAction()
    {
        $this->renderCompanyBase();

        if(!$this->user_permissions->isGranted(MeetingBundle::GRANTED_SUPER_PERSONNEL)){
            throw $this->createAccessDeniedException($this->translator->trans("string.module_not_permitted", [], "security", $this->getLocale()));
        }

        $Department = new Department();
        $Department->setCompany($this->company->getId());
        $Department->setName($this->request->get("name"));

        $this->em()->em_persist($Department)->em_flush();

        $status = true;
        $message = null;

        if(!$Department->getId()||$Department->getId()==null){
            $status = false;
            $message = $this->translator->trans("string.department_could_not_created", [], $this->transDomain, $this->getLocale());
        }

        return $this->renderJson([
            "status" => $status,
            "message" => $message
        ], 200);

        exit(self::class."::newDepartment()");
    }

    public function editDepartmentAction($departmentId)
    {
        $this->renderCompanyBase();

        if(!$this->user_permissions->isGranted(MeetingBundle::GRANTED_SUPER_PERSONNEL)){
            throw $this->createAccessDeniedException($this->translator->trans("string.module_not_permitted", [], "security", $this->getLocale()));
        }

        $Department = $this->entity(Department::class)->find($departmentId);

        $status = true;
        $message = null;

        if(!$Department){
            $status = false;
            $message = $this->translator->trans("string.department_could_not_found", [], $this->transDomain, $this->getLocale());
        }else{
            $Department->setName($this->request->get("name"));
            $this->em_flush();
        }

        return $this->renderJson([
            "status" => $status,
            "message" => $message,
            "department" => $departmentId,
            "department_name" => $this->request->get("name")
        ], 200);

        exit(self::class."::editDepartment({$departmentId})");
    }

    public function deleteDepartmentAction($departmentId)
    {
        $this->renderCompanyBase();

        if(!$this->user_permissions->isGranted(MeetingBundle::GRANTED_SUPER_PERSONNEL)){
            throw $this->createAccessDeniedException($this->translator->trans("string.module_not_permitted", [], "security", $this->getLocale()));
        }

        $Department = $this->entity(Department::class)->find($departmentId);

        $status = true;
        $message = null;

        if(!$Department){
            $status = false;
            $message = $this->translator->trans("string.department_could_not_found", [], $this->transDomain, $this->getLocale());
        }else{
            $this->em_remove($Department);
            $this->em_flush();

            $Positions = $this->entity(Position::class)->findBy([
                "department" => $departmentId
            ]);

            if($Positions&&is_array($Positions)){
                foreach($Positions as $Position){
                    $this->em_remove($Position);
                    $this->em_flush();
                }
            }else if($Positions&&is_object($Positions)){
                $this->em_remove($Positions);
                $this->em_flush();
            }
        }

        return $this->renderJson([
            "status" => $status,
            "message" => $message,
            "department" => $departmentId
        ], 200);

        exit(self::class."::deleteDepartment({$departmentId})");
    }

}