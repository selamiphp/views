<?php
declare(strict_types = 1);

namespace Selami\View\Twig;

use Selami\Stdlib\CaseConverter;
use Selami\View\ExtensionsAbstract;
use Twig_Environment;
use InvalidArgumentException;
use BadMethodCallException;
use Twig_SimpleFunction;

/**
 * Class TwigExtensions extends ViewExtensionsAbstracts
 *
 * @package Selami\View\Twig
 */
class TwigExtensions extends ExtensionsAbstract
{
    private $twig;
    private $config;

    public function __construct(Twig_Environment $twig, array $config)
    {
        $this->twig = $twig;
        $this->config = $config;
        $this->loadFunctions();
    }

    protected function extendForGetUrl() : void
    {
        $filter = new Twig_SimpleFunction(
            'getUrl',
            function (
                $alias,
                $params = []
            ) {
                $function = new Functions\GetUrl($this->config['base_url'], $this->config['aliases'], $alias, $params);
                return $function->run();
            },
            array('is_safe' => array('html'))
        );
        $this->twig->addFunction($filter);
    }

    protected function extendForWidget() : void
    {
        $filter = new \Twig_SimpleFunction(
            'Widget_*_*',
            function ($widgetNameStr, $widgetActionStr, $args = []) {


                $function = new Functions\Widget($this->twig, $this->config, $widgetNameStr, $widgetActionStr, $args);
                return $function->run();

            },
            array('is_safe' => array('html'))
        );
        $this->twig->addFunction($filter);
    }

    protected function extendForQueryParams() : void
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

    protected function extendForSiteUrl() : void
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

    protected function extendForVarDump() : void
    {
        $filter = new \Twig_SimpleFunction(
            'varDump',
            function ($args) {
                /**
            * @noinspection ForgottenDebugOutputInspection 
            */
                var_dump($args);
            },
            array('is_safe' => array('html'))
        );
        $this->twig->addFunction($filter);
    }

    protected function extendForPagination() : void
    {
        /**
 * @noinspection MoreThanThreeArgumentsInspection 
*/
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
