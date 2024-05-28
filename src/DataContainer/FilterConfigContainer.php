<?php

namespace HeimrichHannot\FilterBundle\DataContainer;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use HeimrichHannot\TwigSupportBundle\Filesystem\TwigTemplateLocator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class FilterConfigContainer
{
    private TwigTemplateLocator $twigTemplateLocator;
    private ParameterBagInterface $parameterBag;

    public function __construct(TwigTemplateLocator $twigTemplateLocator, ParameterBagInterface $parameterBag)
    {
        $this->twigTemplateLocator = $twigTemplateLocator;
        $this->parameterBag = $parameterBag;
    }

    /**
     * @Callback(table="tl_filter_config", target="fields.template.options")
     */
    public function onFieldsTemplateOptionsCallback(): array
    {
        $choices = [];

        if ($this->parameterBag->has('huh.filter')) {
            $config = $this->parameterBag->get('huh.filter');
        } else {
            return [];
        }

        $choices = $this->twigTemplateLocator->getTemplateGroup($config['filter']['template_prefixes']);


        if (isset($config['filter']['templates'])) {
            foreach ($config['filter']['templates'] as $template) {

                // remove duplicates
                $templateParts = explode('/', $template['template']);
                $templateName = str_replace('.html.twig', '', end($templateParts));
                if (isset($choices[$templateName])) {
                    unset($choices[$templateName]);
                }

                $choices[$template['name']] = $template['template'].' (Yaml)';
            }
        }

        asort($choices);

        return $choices;
    }
}