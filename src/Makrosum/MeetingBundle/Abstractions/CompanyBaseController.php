<?php
/**
 * Created by PhpStorm.
 * User: Musa ATALAY
 * Date: 2.03.2016
 * Time: 23:38
 */

namespace Makrosum\MeetingBundle\Abstractions;

use Makrosum\MeetingBundle\MeetingBundle;
use Makrosum\MeetingBundle\Entity\Company;
use Makrosum\MeetingBundle\Entity\Permission;

class CompanyBaseController extends BaseController
{

    protected $company;
    protected $companyDomain;
    protected $isRelated = false;
    protected $user_permissions;
    protected $check_relations = [];

    protected function renderCompanyBase(Array $base_data = array(), $trans_domain = "company")
    {
        $this->renderBase(true);
        $this->transDomain($trans_domain);
        $this->data(array_merge([
            "is_owner" => false,
            "is_related" => false,
            "has_meeting_permission" => false,
            "has_user_permission" => false,
            "IS_GRANTED_SUPER_PERSONNEL" => false,
            "IS_GRANTED_SUPER_MEETING" => false
        ], $base_data));
        
        if($this->request->get('subdomain')!=null&&$this->companyDomain!="www"){
            $this->companyDomain = $this->request->get('subdomain');
            $this->param("company_domain", $this->request->get('subdomain'));
            $this->data(array("company_domain" => $this->request->get("subdomain")));
            $this->company = $this->entity(Company::class)->findOneBy([
                "domain" => $this->request->get('subdomain')
            ]);
            if(!$this->company){
                throw $this->createNotFoundException($this->translator->trans("string.company_could_not_found", [], "company", $this->getLocale()));
            }
            if(!$this->company->isActive()){
                throw $this->createNotFoundException($this->translator->trans("string.company_could_not_found", [], "company", $this->getLocale()));
            }
            $this->renderData["title"] .= " | ".$this->company->getName();
            $this->data(array("company_name" => $this->company->getName()));
            $this->data(array("company_logo" => $this->company->getLogo()));
            if($this->isRole(MeetingBundle::ROLE_USER)){
                if($this->company->isOwner($this->user)){
                    $this->renderData["is_owner"] = true;
                    $this->renderData["IS_GRANTED_SUPER_PERSONNEL"] = true;
                    $this->renderData["IS_GRANTED_SUPER_MEETING"] = true;
                    $this->user_permissions = new Permission();
                    $this->user_permissions->setGrant(MeetingBundle::GRANTED_SUPER_PERSONNEL, true);
                    $this->user_permissions->setGrant(MeetingBundle::GRANTED_SUPER_MEETING, true);
                    $this->user_permissions->setGrant(MeetingBundle::GRANTED_CREATE_MEETING, true);
                    $this->user_permissions->setGrant(MeetingBundle::GRANTED_EDIT_MEETING, true);
                    $this->user_permissions->setGrant(MeetingBundle::GRANTED_STATUS_MEETING, true);
                    $this->user_permissions->setGrant(MeetingBundle::GRANTED_DELETE_MEETING, true);
                    $this->user_permissions->setGrant(MeetingBundle::GRANTED_REQUEST_MEETING, true);
                    $this->user_permissions->setGrant(MeetingBundle::GRANTED_MATTER_MEETING, true);
                }else{
                    $this->user_permissions = $this->entity(Permission::class)->findOneBy([
                        "user" => $this->user->getId(),
                        "company" => $this->company->getId()
                    ]);
                }
                if(in_array($this->company->getDomain(), $this->related_domains)){
                    $this->isRelated = true;
                    $this->renderData["is_related"] = true;
                }
                if(!$this->isRelated&&$this->request->get("_route")!="company_homepage"){
                    if($this->request->get("_route")=="company_meeting_view_meeting"){
                        $this->check_relations["meeting"] = true;
                    }else{
                        $render = $this->redirectToRoute("company_homepage", ["subdomain" => $this->company->getDomain()]);
                        $render->send();
                    }
                }
            }
        }

    }

    protected function isRelated()
    {
        return $this->isRelated;
    }

}