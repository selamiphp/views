<?php
declare(strict_types = 1);

namespace Selami\View\Twig;

use Selami\View\ExtensionsAbstract;
use Twig_Environment;
use Camel\CaseTransformer;
use Camel\Format;
use InvalidArgumentException;
use BadMethodCallException;

/**
 * Class TwigExtensions extends ViewExtensionsAbstracts
 * @package Selami\View\Twig
 */
class TwigExtensions extends ExtensionsAbstract
{
    private $twig;
    private $config;
    private $toCamelCase;
    private $toSnakeCase;

    public function __construct(Twig_Environment $twig, array $config)
    {
        $this->twig = $twig;
        $this->config = $config;
        $this->toCamelCase = new CaseTransformer(new Format\SnakeCase, new Format\StudlyCaps);
        $this->toSnakeCase = new CaseTransformer(new Format\CamelCase, new Format\SnakeCase);
        $this->loadExtensions();
        $this->loadFunctions();
    }

    protected function loadExtensions()
    {
        $this->twig->addExtension(new \Twig_Extensions_Extension_Date());
        $this->twig->addExtension(new \Twig_Extensions_Extension_Intl());
        $this->twig->addExtension(new \Twig_Extensions_Extension_Text());
        $this->twig->addExtension(new \Twig_Extensions_Extension_I18n());
    }

    protected function extendForTranslation()
    {
        $filter = new \Twig_SimpleFunction(
            '_t',
            function (
                $string,
                array $findAndReplace = null
            ) {
                $translation = _($string);
                if (is_array($findAndReplace)) {
                    $translateArray = $this->buildTranslateArray($findAndReplace);
                    $translation = strtr($translation, $translateArray);
                }
                return $translation;
            },
            array('is_safe' => array('html'))
        );
        $this->twig->addFunction($filter);
    }

    /**
     * Build translate array.
     * @param $translateArray
     * @return array
     */
    private function buildTranslateArray(array $translateArray)
    {
        $tmpArray = [];
        foreach ($translateArray as $key => $value) {
            $tmpArray['@'.$key] = $value;
        }
        return $tmpArray;
    }

    protected function extendForGetUrl()
    {
        $filter = new \Twig_SimpleFunction(
            'getUrl',
            function (
                $alias,
                $params = []
            ) {
                if (array_key_exists($alias, $this->config['aliases'])) {
                    $data = $this->config['aliases'][$alias];
                    $relative_path = $data;
                    foreach ($params as $param => $value) {
                        if (strpos($param, ':') === strlen($param)-1) {
                            $relative_path = preg_replace('/{'.$param.'(.*?)}/msi', $value, $relative_path);
                        } else {
                            $relative_path = str_replace('{'.$param.'}', $value, $relative_path);
                        }
                    }
                    return $this->config['base_url'] . '/' . $relative_path;
                }
                return '';
            },
            array('is_safe' => array('html'))
        );
        $this->twig->addFunction($filter);
    }

    protected function extendForWidget()
    {
        $filter = new \Twig_SimpleFunction(
            'Widget_*_*',
            function ($widgetNameStr, $widgetActionStr, $args = []) {
                $widgetAction = $this->toCamelCase->transform($widgetActionStr);
                $widgetName = $this->toCamelCase->transform($widgetNameStr);
                $widget = '\\' . $this->config['app_namespace'] . '\\Widget\\' . $widgetName;
                if (!class_exists($widget)) {
                    $message = 'Widget ' . $widgetName . '_' . $widgetAction . ' has not class name as ' . $widget;
                    throw new BadMethodCallException($message);
                }
                $widgetInstance = new $widget($args);
                if (!method_exists($widgetInstance, $widgetAction)) {
                    $message = 'Widget ' . $widget . ' has not method name as ' . $widgetAction;
                    throw new BadMethodCallException($message);
                }
                $templateFileBasename = $args['template'] ?? $this->toSnakeCase->transform($widgetActionStr) . '.twig';
                $templateFullPath = $this->config['templates_dir'] . '/_widgets/'
                    . $this->toSnakeCase->transform($widgetNameStr) . '/' . $templateFileBasename;

                if (!file_exists($templateFullPath)) {
                    $message = sprintf(
                        '%s  template file not found! %s needs a main template file at: %s',
                        $templateFileBasename,
                        $widgetNameStr . '_' . $widgetActionStr,
                        $templateFullPath
                    );
                    throw new InvalidArgumentException($message);
                }
                $templateFile =  '_widgets/'
                    . $this->toSnakeCase->transform($widgetNameStr) . '/' . $templateFileBasename;
                $widgetData = $widgetInstance->{$widgetAction}();
                return $this->twig->render($templateFile, $widgetData);
            },
            array('is_safe' => array('html'))
        );
        $this->twig->addFunction($filter);
    }

    protected function extendForQueryParams()
    {
        $filter = new \Twig_SimpleFunction(
            'queryParams',
            function ($queryParams, $prefix = '?') {
                return $prefix . http_build_query($queryParams);
            },
            array('is_safe' => array('html'))
        );
        $this->twig->addFunction($filter);
    }

    protected function extendForSiteUrl()
    {
        $filter = new \Twig_SimpleFunction(
            'siteUrl',
            function ($path = '') {
                return $this->config['base_url'] . $path;
            },
            array('is_safe' => array('html'))
        );
        $this->twig->addFunction($filter);
    }

    protected function extendForVarDump()
    {
        $filter = new \Twig_SimpleFunction(
            'varDump',
            function ($args) {
                /** @noinspection ForgottenDebugOutputInspection */
                var_dump($args);
            },
            array('is_safe' => array('html'))
        );
        $this->twig->addFunction($filter);
    }

    protected function extendForPagination()
    {
        /** @noinspection MoreThanThreeArgumentsInspection */
        $filter = new \Twig_SimpleFunction(
            'Pagination',
            function (
                int $total,
                int $current,
                string $linkTemplate,
                string $parentTemplate = '<ul class="pagination">(items)</ul>',
                string $itemTemplate = '<li class="(item_class)">(link)</li>',
                string $linkItemTemplate = '<a href="(href)" class="(link_class)">(text)</a>',
                string $ellipsesTemplate = '<li><a>...</a></li>'
            ) {
                $items =  '';
                $pageGroupLimit = 3;
                $useEllipses = ($total >10) ? 1 : 0;
                $renderedEllipses = 0;
                $values = [
                    '(item_class)'  => '',
                    '(href)'        => '',
                    '(link_class)'  => '',
                    '(text)'  => ''
                ];
                for ($i=1; $i <= $total; $i++) {
                    $values['(link_class)'] = '';
                    $values['(item_class)'] = '';
                    if ($i === $current) {
                        $values['(link_class)'] = 'active';
                        $values['(item_class)'] = 'active';
                    }
                    $values['(text)'] = $i;
                    $values['(href)'] = str_replace('(page_num)', $i, $linkTemplate);
                    $link = strtr($linkItemTemplate, $values);
                    $useLink = 1;
                    if ($useEllipses === 1) {
                        $useLink = 0;
                        if (($i <= $pageGroupLimit)
                            ||  ($i >= ($current-1) && $i <= ($current+1))
                            || ($i >= ($total-2))
                        ) {
                            $useLink = 1;
                            $renderedEllipses = 0;
                        } else {
                            if ($renderedEllipses === 0) {
                                $items .= $ellipsesTemplate;
                            }
                            $renderedEllipses = 1;
                        }
                    }
                    if ($useLink === 1) {
                        $thisItemTemplate = str_replace(
                            array('(link)'),
                            array($link),
                            $itemTemplate
                        );
                        $items .= strtr($thisItemTemplate, $values);
                    }
                }
                return str_replace('(items)', $items, $parentTemplate);
            },
            array('is_safe' => array('html'))
        );
        $this->twig->addFunction($filter);
    }
}
