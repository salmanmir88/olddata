<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-seo
 * @version   2.1.11
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\SeoContent\Ui\Component;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\AbstractComponent;
use Mirasvit\Seo\Api\Service\TemplateEngineServiceInterface;

class TemplateSyntaxComponent extends AbstractComponent
{
    /**
     * @var TemplateEngineServiceInterface
     */
    private $templateEngineService;

    /**
     * TemplateSyntaxComponent constructor.
     * @param TemplateEngineServiceInterface $templateEngineService
     * @param ContextInterface $context
     * @param array $components
     * @param array $data
     */
    public function __construct(
        TemplateEngineServiceInterface $templateEngineService,
        ContextInterface $context,
        $components = [],
        array $data = []
    ) {
        $this->templateEngineService = $templateEngineService;

        $data['config']['component'] = 'Mirasvit_SeoContent/js/component/template-syntax';

        parent::__construct($context, $components, $data);
    }

    /**
     * @return string
     */
    public function getComponentName()
    {
        return 'template_syntax';
    }

    public function prepare()
    {
        parent::prepare();

        $config = $this->getData('config');

        foreach ($this->templateEngineService->getData() as $scope => $dataObject) {
            $scopeData = [
                'label' => $dataObject->getTitle(),
            ];
            foreach ($dataObject->getVariables() as $var) {
                $scopeData['vars'][] = $scope . '_' . $var;
            }

            $config['scopeData'][] = $scopeData;
        }

        $this->setData('config', $config);
    }
}
