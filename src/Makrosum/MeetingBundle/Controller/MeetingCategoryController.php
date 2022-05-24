<?php
/**
 * Created by PhpStorm.
 * User: musaatalay
 * Date: 19.03.2016
 * Time: 23:11
 */

namespace Makrosum\MeetingBundle\Controller;


use Makrosum\MeetingBundle\Abstractions\CompanyBaseController;
use Makrosum\MeetingBundle\Entity\Company;
use Makrosum\MeetingBundle\Entity\MeetingCategory;

class MeetingCategoryController extends CompanyBaseController
{
    public function categoriesAction()
    {
        $this->renderCompanyBase(["categories" => []], "meeting");

        $categories = $this->entity(MeetingCategory::class)->findBy([
            "company" => $this->company->getId()
        ]);

        foreach ($categories as $category) {
            $this->data([
                "categories" => [
                    [
                        "id" => $category->getId(),
                        "name" => $category->getName(),
                        "company" => $this->entity(Company::class)->find($category->getCompany())->getName()
                    ]
                ]
            ]);
        }

        return $this->renderJson();

        exit(self::class."::categories()");
    }

    public function checkCategoryAction($categoryName)
    {
        $this->renderCompanyBase([], "meeting");

        $MeetingCategory = $this->entity(MeetingCategory::class)->findBy([
            "company" => $this->company->getId(),
            "name" => $categoryName
        ]);

        $status = true;
        $message = null;

        if(!$MeetingCategory){
            $status = false;
            $message = $this->translator->trans("string.category_could_not_found", [], $this->transDomain, $this->getLocale());
        }

        return $this->renderJson([
            "status" => $status,
            "message" => $message,
            "department" => $categoryName
        ], 200);

        exit(self::class."::checkCategory({$categoryName})");
    }

    public function newCategoryAction()
    {
        $this->renderCompanyBase([], "meeting");

        $MeetingCategory = new MeetingCategory();
        $MeetingCategory->setCompany($this->company);
        $MeetingCategory->setName($this->request->get("name"));

        $this->em()->em_persist($MeetingCategory)->em_flush();

        $status = true;
        $message = null;

        if(!$MeetingCategory->getId()||$MeetingCategory->getId()==null){
            $status = false;
            $message = $this->translator->trans("string.category_could_not_created", [], $this->transDomain, $this->getLocale());
        }

        return $this->renderJson([
            "status" => $status,
            "message" => $message
        ], 200);

        exit(self::class."::newCategory()");
    }

    public function editCategoryAction($categoryId)
    {
        $this->renderCompanyBase([], "meeting");

        $MeetingCategory = $this->entity(MeetingCategory::class)->find($categoryId);

        $status = true;
        $message = null;

        if(!$MeetingCategory){
            $status = false;
            $message = $this->translator->trans("string.category_could_not_found", [], $this->transDomain, $this->getLocale());
        }else{
            $MeetingCategory->setName($this->request->get("name"));
            $this->em_flush();
        }

        return $this->renderJson([
            "status" => $status,
            "message" => $message,
            "department" => $categoryId,
            "department_name" => $this->request->get("name")
        ], 200);

        exit(self::class."::editCategory{$categoryId})");
    }

    public function deleteCategoryAction($categoryId)
    {
        $this->renderCompanyBase([], "meeting");

        $MeetingCategory = $this->entity(MeetingCategory::class)->find($categoryId);

        $status = true;
        $message = null;

        if(!$MeetingCategory){
            $status = false;
            $message = $this->translator->trans("string.category_could_not_found", [], $this->transDomain, $this->getLocale());
        }else{
            $this->em_remove($MeetingCategory);
            $this->em_flush();
        }

        return $this->renderJson([
            "status" => $status,
            "message" => $message,
            "department" => $categoryId
        ], 200);

        exit(self::class."::deleteCategory({$categoryId})");
    }
}