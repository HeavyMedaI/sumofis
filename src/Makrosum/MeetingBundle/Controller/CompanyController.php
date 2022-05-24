<?php
/**
 * Created by PhpStorm.
 * User: Musa ATALAY
 * Date: 2.03.2016
 * Time: 18:34
 */

namespace Makrosum\MeetingBundle\Controller;

use Makrosum\MeetingBundle\Abstractions\CompanyBaseController;
use Makrosum\MeetingBundle\Abstractions\MeetingBaseController;
use Makrosum\MeetingBundle\MeetingBundle;
use Makrosum\MeetingBundle\Entity\Country;
use Makrosum\MeetingBundle\Entity\Province;
use Makrosum\MeetingBundle\Entity\City;
use Makrosum\MeetingBundle\Entity\Company;
use Makrosum\MeetingBundle\Entity\Meeting;
use Makrosum\MeetingBundle\Entity\MeetingCategory;
use Makrosum\MeetingBundle\Entity\Department;
use Makrosum\MeetingBundle\Entity\Position;
use Makrosum\MeetingBundle\Entity\Personnel;
use Makrosum\MeetingBundle\Entity\User;
use Makrosum\MeetingBundle\Entity\Permission;
use Symfony\Component\Filesystem\Filesystem;

class CompanyController extends MeetingBaseController
{
    public function indexAction()
    {
        $this->renderCompanyBase();

        if($this->isRole(MeetingBundle::ROLE_USER)&&$this->isRelated()){
            if($this->user_permissions->isGranted(MeetingBundle::GRANTED_SUPER_PERSONNEL)){
                $this->renderData["IS_GRANTED_SUPER_PERSONNEL"] = true;
            }
            return $this->renderIt("MeetingBundle:Company:dash_company.html.twig");
        }

        if($this->isRole(MeetingBundle::ROLE_ANONYMOUS)){

            $Map = explode(",", $this->company->getMap());

            $Map = array(
                "latitude" => trim($Map[0]),
                "longitude" => trim($Map[1])
            );

            $this->data([
                "company_phone" => $this->company->getPhone(),
                "company_fax" => $this->company->getFax(),
                "company_email" => $this->company->getEmail(),
                "company_website" => $this->company->getWebsite(),
                "company_address" => $this->company->getAddress(),
                "company_country" => $this->entity(Country::class)->findOneBy(["code" => $this->company->getCountry()])->getName(),
                "company_province" => $this->entity(Province::class)->findOneBy(["area_code" => $this->company->getProvince()])->getName(),
                "company_city" => $this->entity(City::class)->findOneBy(["area_code" => $this->company->getCity()])->getName(),
                "company_map" => $Map
            ]);
            return $this->renderIt("MeetingBundle:Company:view_company.html.twig");
        }

        exit($this->companyDomain);
    }

    public function newAction()
    {
        $this->renderCompanyBase([
            "action" => $this->get("router")->generate("company_backservice_create"),
            "countries" => [],
            "provinces" => [],
            "cities" => [],
            "act" => "new"
        ]);

        $this->data([
            "action_header" => $this->translator->trans("string.new_company", [], $this->transDomain, $this->getLocale())
        ]);

        $this->data([
            "company_logo" => "default.jpg",
            "form_domain" => "",
            "form_name" => "",
            "form_website" => "",
            "form_email" => "",
            "form_phone" => "+90",
            "form_fax" => "+90",
            "form_country" => "",
            "form_province" => "",
            "form_city" => "",
            "form_address" => "",
            "form_map" => ""
        ]);

        $countries = $this->getDoctrine()->getRepository("MeetingBundle:Country")->findAll();
        foreach($countries as $country){
            $selected = false;
            if($this->user->getCountry()==$country->getCode()){
                $selected = true;
            }
            $this->data([
                "countries" => [
                    $country->getLocale() => [
                        "code" => $country->getCode(),
                        "name" => $country->getName(),
                        "selected" => $selected
                    ]
                ]
            ]);
        }

        $provinces = $this->getDoctrine()->getRepository("MeetingBundle:Province")->findBy([
            "country_code" => $this->user->getCountry()
        ]);
        foreach($provinces as $province){
            $selected = false;
            if($this->user->getProvince()==$province->getArea()){
                $selected = true;
            }
            $this->data([
                "provinces" => [
                    $province->getId() => [
                        "area" => $province->getArea(),
                        "name" => $province->getName(),
                        "selected" => $selected
                    ]
                ]
            ]);
        }

        $cities = $this->getDoctrine()->getRepository("MeetingBundle:City")->findBy([
            "country_code" => $this->user->getCountry(), "province_code" => $this->user->getProvince()
        ]);
        foreach($cities as $city){
            $selected = false;
            if($this->user->getCity()==$city->getArea()){
                $selected = true;
            }
            $this->data([
                "cities" => [
                    $city->getId() => [
                        "area" => $city->getArea(),
                        "name" => $city->getName(),
                        "selected" => $selected
                    ]
                ]
            ]);
        }

        return $this->renderIt("MeetingBundle:Company:form_company.html.twig");
    }

    public function createAction()
    {
        $this->renderCompanyBase();

        $Company = new Company();
        $Company->setDomain($this->request->get("domain"));
        $Company->setName($this->request->get("name"));
        $parseWebsite = explode("/", str_replace(array("http://", "https://"), null, $this->request->get("website")));
        $Company->setWebsite($parseWebsite[0]);
        $Company->setEmail($this->request->get("email"));
        $Company->setPhone($this->request->get("phone"));
        $Company->setFax($this->request->get("fax"));
        $Company->setCountry($this->request->get("country"));
        $Company->setProvince($this->request->get("province"));
        $Company->setCity($this->request->get("city"));
        $Company->setAddress($this->request->get("address"));
        $Company->setOwner($this->user);
        $Company->setMap($this->request->get("map"));
        $Company->setLogo("default.png");
        $Company->isActive(true);

        $this->em()
            ->em_persist($Company)
            ->em_flush();

        return $this->renderJson([
            "status" => true,
            "company" => $this->request->get("domain").".".$this->param("hostname")
        ]);
    }

    public function editAction()
    {
        $this->renderCompanyBase([
            "countries" => [],
            "provinces" => [],
            "cities" => [],
            "act" => "edit"
        ]);

        $this->data([
            "action" => $this->get("router")->generate("company_backservice_update", [
                "subdomain" => $this->companyDomain
            ]),
            "action_header" => $this->translator->trans("string.edit_company", [], $this->transDomain, $this->getLocale())
        ]);

        $this->data([
            "form_domain" => $this->company->getDomain(),
            "form_name" => $this->company->getName(),
            "form_website" => $this->company->getWebsite(),
            "form_email" => $this->company->getEmail(),
            "form_phone" => $this->company->getPhone(),
            "form_fax" => $this->company->getFax(),
            "form_address" => $this->company->getAddress(),
            "form_map" => $this->company->getMap()
        ]);

        $this->renderData["company_logo"] = $this->company->getLogo();

        $countries = $this->getDoctrine()->getRepository("MeetingBundle:Country")->findAll();
        foreach($countries as $country){
            $selected = false;
            if($this->company->getCountry()==$country->getCode()){
                $selected = true;
            }
            $this->data([
                "countries" => [
                    $country->getLocale() => [
                        "code" => $country->getCode(),
                        "name" => $country->getName(),
                        "selected" => $selected
                    ]
                ]
            ]);
        }

        $provinces = $this->getDoctrine()->getRepository("MeetingBundle:Province")->findBy([
            "country_code" => $this->company->getCountry()
        ]);
        foreach($provinces as $province){
            $selected = false;
            if($this->company->getProvince()==$province->getArea()){
                $selected = true;
            }
            $this->data([
                "provinces" => [
                    $province->getId() => [
                        "area" => $province->getArea(),
                        "name" => $province->getName(),
                        "selected" => $selected
                    ]
                ]
            ]);
        }

        $cities = $this->getDoctrine()->getRepository("MeetingBundle:City")->findBy([
            "country_code" => $this->company->getCountry(), "province_code" => $this->company->getProvince()
        ]);
        foreach($cities as $city){
            $selected = false;
            if($this->company->getCity()==$city->getArea()){
                $selected = true;
            }
            $this->data([
                "cities" => [
                    $city->getId() => [
                        "area" => $city->getArea(),
                        "name" => $city->getName(),
                        "selected" => $selected
                    ]
                ]
            ]);
        }

        return $this->renderIt("MeetingBundle:Company:form_company.html.twig");
    }

    public function updateAction()
    {
        $this->renderCompanyBase();

        $this->company
            ->setName($this->request->get("name"))
            ->setEmail($this->request->get("email"))
            ->setPhone($this->request->get("phone"))
            ->setWebsite($this->request->get("website"))
            ->setFax($this->request->get("fax"))
            ->setCountry($this->request->get("country"))
            ->setProvince($this->request->get("province"))
            ->setCity($this->request->get("city"))
            ->setAddress($this->request->get("address"))
            ->setMap($this->request->get("map"));
        $this->em_flush();

        $status = true;
        $message = null;

        if(!$this->company){
            $status = false;
            $message = "Company could not update";
        }

        return $this->renderJson([
            "status" => $status,
            "message" => $message
        ]);
    }

    public function logoUploadAction()
    {
        $this->renderCompanyBase();

        $extTypes = array(
            "jpeg" => "jpg",
            "png" => "png"
        );

        $FileSystem = new Filesystem();

        $resource = $this->request->get("logo");

        $logoResource = explode(",", $resource);

        $parseDataType = explode("/", str_replace(array("data:", "base64", ";"), null, $logoResource[0]));

        $logoExt = $parseDataType[1];

        $logoName = "{$this->companyDomain}.{$extTypes[$logoExt]}";

        $FilePath = $this->get("kernel")->getBaseDir()."/web/bundles/Makrosum/Meeting/Logo/{$logoName}";

        $FileSystem->touch($FilePath);

        $FileSystem->dumpFile($FilePath, base64_decode($logoResource[1]));

        $this->company->setLogo($logoName);
        $this->em_flush();

        return $this->renderJson(["status" => true]);
    }

    public function checkDomainAction()
    {
        $company = $this->getDoctrine()->getRepository("MeetingBundle:Company")->findBy([
            "domain" => $this->getRequest()->get("domain")
        ]);
        $status = true;
        if(count($company)>0){
            $status = false;
        }
        return $this->renderJson(["status" => $status]);
    }

    public function removeAction()
    {
        $this->renderCompanyBase();

        $status = false;
        $message = "not permitted";

        if($this->company->isOwner($this->user)){
            $status = true;
            $message = null;
            $this->company->isActive(false);
            $this->em_flush();
        }

        return $this->renderJson([
            "status" => $status,
            "message" => $message
        ]);
    }

    public function leaveAction()
    {
        $this->renderCompanyBase();

        $status = false;
        $message = "not permitted";

        $Permissions = $this->entity(Personnel::class)->findOneBy([
            "company" => $this->company->getId(),
            "user" => $this->user->getId()
        ]);

        $this->em_remove($Permissions)->em_flush();
        $this->em_clear();

        return $this->renderJson([
            "status" => $status,
            "message" => $message
        ]);
    }

    public function meeting_managementAction()
    {

        $this->renderMeetingBase(false, [
            "IS_GRANTED_CREATE_MEETING" => false,
            "IS_GRANTED_EDIT_MEETING" => false,
            "IS_GRANTED_DELETE_MEETING" => false,
            "meetings" => [],
            "categories" => [],
        ], "meeting");

        $this->renderData["title"] .= " > ".$this->translator->trans("string.meetings", [], "meeting");

        if($this->user_permissions->isGranted(MeetingBundle::GRANTED_CREATE_MEETING)){
            $this->renderData["IS_GRANTED_CREATE_MEETING"] = true;
            $this->renderData["IS_GRANTED_REQUEST_MEETING"] = true;
            $this->renderData["IS_GRANTED_MATTER_MEETING"] = true;
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

        $meetings = $this->entity(Meeting::class)->findAllMeetingsRelatedToMe($this->user, $this->user_permissions, $this->company);

        foreach ($meetings as $index => $meeting) {
            $createdByMe = false;
            if($meeting->isCreator($this->user)){
                $createdByMe = true;
            }
            $this->data([
                "meetings" => [
                    $index => [
                        "id" => $meeting->getId(),
                        "header" => $meeting->getHeader(),
                        "creator" => $this->entity(User::class)->find($meeting->getCreator())->getFullname(),
                        "category_id" => $meeting->getCategory(),
                        "category_name" => $this->entity(MeetingCategory::class)->find($meeting->getCategory())->getName(),
                        "company" => $this->entity(Company::class)->find($meeting->getCompany())->getName(),
                        "begin_time" => $meeting->getBegin()->format("Y-m-d H:i:s"),
                        "begin_view" => $meeting->getBegin()->format("d.m.Y H:i:s"),
                        "end_time" => $meeting->getEnd()->format("Y-m-d H:i:s"),
                        "end_view" => $meeting->getEnd()->format("d.m.Y H:i:s"),
                        "isCreatedByMe" => $createdByMe,
                        "status" => $meeting->getStatus()
                    ]
                ]
            ]);
        }

        $categories = $this->entity(MeetingCategory::class)->findBy([
            "company" => $this->company->getId()
        ]);

        foreach ($categories as $category) {
            $this->data([
                "categories" => [
                    $category->getId() => ["id" => $category->getId(), "name" => $category->getName()]
                ]
            ]);
        }

        return $this->renderIt("MeetingBundle:Meeting:meetings.html.twig");
    }

    public function meeting_category_managementAction()
    {
        $this->renderCompanyBase([
            "categories" => []
        ], "meeting");

        if(!$this->user_permissions->isGranted(MeetingBundle::GRANTED_SUPER_MEETING)){
            throw $this->createAccessDeniedException($this->translator->trans("string.module_not_permitted", [], "security", $this->getLocale()));
        }

        $this->data([
            "company_name" => $this->company->getName()
        ]);

        $categories = $this->entity(MeetingCategory::class)->findBy([
            "company" => $this->company->getId()
        ]);

        foreach($categories as $index => $category){
            $this->data([
                "categories" => [
                    $index => [
                        "id" => $category->getId(),
                        "name" => $category->getName(),
                        "company" => $this->entity(Company::class)->find($category->getCompany())->getName()
                    ]
                ]
            ]);
        }

        return $this->renderIt("MeetingBundle:Meeting:categories.html.twig");
    }

    public function personnel_managementAction()
    {
        $this->renderCompanyBase([
            "personnels" => array()
        ], "company");

        if(!$this->user_permissions->isGranted(MeetingBundle::GRANTED_SUPER_PERSONNEL)){
            throw $this->createAccessDeniedException($this->translator->trans("string.module_not_permitted", [], "security", $this->getLocale()));
        }

        $personnels = $this->getDoctrine()->getRepository("MeetingBundle:Personnel")->getAllPersonnelsInfo($this->company);

        foreach ($personnels as $index => $personnel) {
            $this->data([
                "personnels" => [
                     $index => [
                         "personnel" => $personnel["personnel"]->getId(),
                         "fullname" => $personnel["storage"][0]->getFullname(),
                         "department" => $personnel["storage"][1]?$personnel["storage"][1]->getName():null,
                         "position" => $personnel["storage"][2]?$personnel["storage"][2]->getName():null
                    ]
                ]
            ]);
        }

        return $this->renderIt("MeetingBundle:Company:personnels.html.twig");
    }

    public function new_personnelAction()
    {
        $this->renderCompanyBase([
            "form_type" => "new",
            "display_fullname" => "hide",
            "disable_submit" => "disabled",
            "departments" => [],
            "positions" => [],
            "personnel" => [
                "id" => null,
                "user" => null,
                "name" => null,
                "email" => null,
                "disabled" => null,
                "hide_check" => null
            ]
        ]);

        if(!$this->user_permissions->isGranted(MeetingBundle::GRANTED_SUPER_PERSONNEL)){
            throw $this->createAccessDeniedException($this->translator->trans("string.module_not_permitted", [], "security", $this->getLocale()));
        }

        $form_action = $this->get("router")->generate("company_company_personnels_new_personnel", [
            "subdomain" => $this->companyDomain
        ]);

        $this->data([
            "form_action" => $form_action
        ]);

        $departments = $this->entity(Department::class)->findBy([
            "company" => $this->company->getId()
        ]);

        foreach($departments as $department){
            $this->data([
                "departments" => [
                    $department->getId() => [
                        "id" => $department->getId(),
                        "name" => $department->getName(),
                        "selected" => false
                    ]
                ]
            ]);
        }

        $this->data([
            "GRANTED_SUPER_PERSONNEL" => false,
            "GRANTED_SUPER_MEETING" => false,
            "GRANTED_CREATE_MEETING" => false,
            "GRANTED_EDIT_MEETING" => false,
            "GRANTED_STATUS_MEETING"  => false,
            "GRANTED_DELETE_MEETING"  => false,
            "GRANTED_REQUEST_MEETING" => false,
            "GRANTED_MATTER_MEETING"  => false,
        ]);

        return $this->renderIt("MeetingBundle:Company:form_personnel.html.twig");
    }

    public function edit_personnelAction($personnelId)
    {
        $this->renderCompanyBase([
            "form_type" => "edit",
            "display_fullname" => "",
            "disable_submit" => "",
            "departments" => [],
            "positions" => [],
            "personnel" => [
                "id" => null,
                "user" => null,
                "name" => null,
                "email" => null,
                "disabled" => "disabled",
                "hide_check" => "hide"
            ]
        ]);

        if(!$this->user_permissions->isGranted(MeetingBundle::GRANTED_SUPER_PERSONNEL)){
            throw $this->createAccessDeniedException($this->translator->trans("string.module_not_permitted", [], "security", $this->getLocale()));
        }

        $form_action = $this->get("router")->generate("company_company_personnels_edit_personnel", [
            "subdomain" => $this->companyDomain,
            "personnelId" => $personnelId
        ]);

        $this->data([
            "form_action" => $form_action
        ]);

        $Personnel = $this->entity(Personnel::class)->findOneBy([
            "id" => $personnelId,
            "company" => $this->company->getId()
        ]);

        if(!$Personnel){
            throw $this->createNotFoundException($this->translator->trans("string.personnel_could_not_found", [], $this->transDomain, $this->getLocale()));
        }

        $PersonnelUserData = $this->entity(User::class)->find($Personnel->getUser());

        $this->renderData["personnel"]["id"] = $personnelId;
        $this->renderData["personnel"]["user"] = $PersonnelUserData->getId();
        $this->renderData["personnel"]["name"] = $PersonnelUserData->getFullname();
        $this->renderData["personnel"]["email"] = $PersonnelUserData->getEmail();

        $departments = $this->entity(Department::class)->findBy([
            "company" => $this->company->getId()
        ]);

        foreach($departments as $department){
            $selected = false;
            if($Personnel->getDepartment()==$department->getId()){
                $selected = true;
            }
            $this->data([
                "departments" => [
                    $department->getId() => [
                        "id" => $department->getId(),
                        "name" => $department->getName(),
                        "selected" => $selected
                    ]
                ]
            ]);
        }

        $positions = $this->entity(Position::class)->findBy([
            "company" => $this->company->getId(),
            "department" => $Personnel->getDepartment()
        ]);

        foreach($positions as $position){
            $selected = false;
            if($Personnel->getPosition()==$position->getId()){
                $selected = true;
            }
            $this->data([
                "positions" => [
                    $position->getId() => [
                        "id" => $position->getId(),
                        "name" => $position->getName(),
                        "selected" => $selected
                    ]
                ]
            ]);
        }

        $Permission = $this->entity(Permission::class)->findOneBy([
            "user" => $PersonnelUserData->getId(),
            "company" => $this->company->getId()
        ]);

        $this->data([
            "GRANTED_SUPER_PERSONNEL" => $Permission->isGranted(MeetingBundle::GRANTED_SUPER_PERSONNEL),
            "GRANTED_SUPER_MEETING" => $Permission->isGranted(MeetingBundle::GRANTED_SUPER_MEETING),
            "GRANTED_CREATE_MEETING" => $Permission->isGranted(MeetingBundle::GRANTED_CREATE_MEETING),
            "GRANTED_EDIT_MEETING" => $Permission->isGranted(MeetingBundle::GRANTED_EDIT_MEETING),
            "GRANTED_STATUS_MEETING"  => $Permission->isGranted(MeetingBundle::GRANTED_STATUS_MEETING),
            "GRANTED_DELETE_MEETING"  => $Permission->isGranted(MeetingBundle::GRANTED_DELETE_MEETING),
            "GRANTED_REQUEST_MEETING" => $Permission->isGranted(MeetingBundle::GRANTED_REQUEST_MEETING),
            "GRANTED_MATTER_MEETING"  => $Permission->isGranted(MeetingBundle::GRANTED_MATTER_MEETING),
        ]);

        return $this->renderIt("MeetingBundle:Company:form_personnel.html.twig");
    }

    public function departmentsAction()
    {
        $this->renderCompanyBase([
            "departments" => []
        ]);

        if(!$this->user_permissions->isGranted(MeetingBundle::GRANTED_SUPER_PERSONNEL)){
            throw $this->createAccessDeniedException($this->translator->trans("string.module_not_permitted", [], "security", $this->getLocale()));
        }

        $departments = $this->entity(Department::class)->findAllDepartments($this->company);

        foreach($departments as $index => $department){
            $this->data([
                "departments" => [
                    $index => [
                        "id" => $department->getId(),
                        "name" => $department->getName()
                    ]
                ]
            ]);
        }

        return $this->renderIt("MeetingBundle:Company:departments.html.twig");
    }

    public function positionsAction()
    {
        $this->renderCompanyBase([
            "departments" => []
        ]);

        if(!$this->user_permissions->isGranted(MeetingBundle::GRANTED_SUPER_PERSONNEL)){
            throw $this->createAccessDeniedException($this->translator->trans("string.module_not_permitted", [], "security", $this->getLocale()));
        }

        $departments = $this->entity(Department::class)->findAllDepartments($this->company);

        $Departments = array();

        foreach ($departments as $department) {
            $PositionsOfDepartment = $this->entity(Position::class)->findAllPositionsByDepartment($department);
            $Positions = array();
            foreach ($PositionsOfDepartment as $Position) {
                $Positions[] = [
                    "id" => $Position->getId(),
                    "name" => $Position->getName()
                ];
            }

            $Departments[] = array(
                "id" => $department->getId(),
                "department_name" => $department->getName(),
                "positions" => $Positions
            );
        }

        $this->data([
            "departments" => $Departments
        ]);

        return $this->renderIt("MeetingBundle:Company:positions.html.twig");

    }

}