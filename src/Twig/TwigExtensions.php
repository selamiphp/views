<?php
declare(strict_types = 1);

namespace Selami\View\Twig;

use Selami\Stdlib\CaseConverter;
use Selami\View\ExtensionsAbstract;
use Twig\Environment;
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

    public function __construct(Environment $twig, array $config)
    {
        $this->twig = $twig;
        $this->config = $config;
        $this->loadFunctions();
    }

    protected function extendForTranslator() : void
    {
        $dictionary = $this->config['dictionary']?? [];
        $filter = new \Twig_SimpleFunction(
            'translate',
            function (
                $string, ...$findAndReplace
            ) use ($dictionary) {
                if (array_key_exists($string, $dictionary)) {
                    $string = $dictionary[$string];
                }
                if (! empty($findAndReplace)) {
                    foreach ($findAndReplace[0] as $find => $replace) {
                        $string = str_replace('{{'.$find.'}}', $replace, $string);
                    }
                }
                return $string;
            },
            array('is_safe' => array('html'))
        );
        $this->twig->addFunction($filter);
    }

    protected function extendForGetUrl() : void
    {
        $filter = new Twig_SimpleFunction(
            'getUrl',
            function (
                $alias,
                $params = []
            ) {
                $function = new Functions\GetUrl(
                    $this->config['runtime']['base_url'],
                    $this->config['runtime']['aliases'],
                    $alias,
                    $params
                );
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
                return $this->config['runtime']['base_url'] . $path;
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

                $function = new Functions\Pagination(
                    $total,
                    $current,
                    $linkTemplate,
                    $parentTemplate,
                    $itemTemplate,
                    $linkItemTemplate,
                    $ellipsesTemplate
                );
                return $function->run();
            },
            array('is_safe' => array('html'))
        );
        $this->twig->addFunction($filter);
    }
}
