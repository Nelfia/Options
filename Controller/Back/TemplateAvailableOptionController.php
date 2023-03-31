<?php

namespace Option\Controller\Back;

use Exception;
use Option\Form\TemplateAvailableOptionForm;
use Option\Service\OptionProduct;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Log\Tlog;
use Thelia\Model\TemplateQuery;
use Thelia\Tools\URL;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/option/template", name="admin_option_template")
 */
class TemplateAvailableOptionController extends BaseAdminController
{
    /**
     * @param OptionProduct $optionProductService
     * @return Response
     *
     * @Route("/set", name="_option_templates_set", methods="POST")
     */
    public function setOptionProductOnTemplate(OptionProduct $optionProductService): Response
    {
        $form = $this->createForm(TemplateAvailableOptionForm::class);

        try {
            $viewForm = $this->validateForm($form);
            $data = $viewForm->getData();
            $template = TemplateQuery::create()->findPk($data['template_id']);
            $optionProductService->setOptionOnTemplateProducts($template, $data['option_id']);

            return $this->generateSuccessRedirect($form);
        } catch (Exception $ex) {
            $errorMessage = $ex->getMessage();

            Tlog::getInstance()->error("Failed to validate template option form: $errorMessage");
        }

        $this->setupFormErrorContext(
            'Failed to process template option tab form data',
            $errorMessage,
            $form
        );

        return $this->generateErrorRedirect($form);
    }

    /**
     * @param Request $request
     * @param OptionProduct $optionProductService
     * @return Response
     *
     * @Route("/delete", name="_option_template_delete", methods="GET")
     */
    public function deleteOptionProductOnTemplate( Request $request, OptionProduct $optionProductService): Response
    {
        try {
            $optionProductId = $request->get('option_product_id');
            $templateId = $request->get('template_id');

            if (!$optionProductId || !$templateId) {
                return $this->pageNotFound();
            }

            $template = TemplateQuery::create()->findPk($templateId);
            $optionProductService->deleteOptionOnTemplateProducts($template, $optionProductId);

        } catch (\Exception $ex) {
            Tlog::getInstance()->addError($ex->getMessage());
        }

        return $this->generateRedirect(URL::getInstance()->absoluteUrl('/admin/configuration/templates/update', [
            "current_tab" => "template_option_tab",
            "template_id" => $templateId ?? null
        ]));
    }
}