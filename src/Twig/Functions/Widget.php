<?php
declare(strict_types=1);

namespace Selami\View\Twig\Functions;

use Selami\View\FunctionInterface;
use Selami\Stdlib\CaseConverter;
use BadMethodCallException;
use InvalidArgumentException;

use Twig\Environment;

class Widget implements FunctionInterface
{
    private $twig;
    private $config;
    private $templateFile;
    private $widgetData;

    public function __construct(Environment $twig, array $config, string $widgetNameStr, string $widgetActionStr, array $args)
    {

        $this->twig = $twig;
        $this->config = $config;
        $widgetAction = CaseConverter::toPascalCase($widgetActionStr);
        $widgetName =  CaseConverter::toPascalCase($widgetNameStr);
        $widget = '\\' . $this->config['app_namespace'] . '\\Widget\\' . $widgetName;
        $this->checkClassIfExists($widget, $widgetName, $widgetAction);
        $widgetInstance = new $widget($args);
        $templateFileBasename = $args['template'] ?? CaseConverter::toSnakeCase($widgetActionStr) . '.twig';
        $this->checkIdTemplateFileExist($templateFileBasename, $widgetNameStr, $widgetActionStr);
        $this->templateFile =  '_widgets/'
            . CaseConverter::toSnakeCase($widgetNameStr) . '/' . $templateFileBasename;
        $this->widgetData = $widgetInstance->{$widgetAction}();
    }

    private function checkClassIfExists($widget, $widgetName, $widgetAction)
    {
        if (!class_exists($widget)) {
            $message = 'Widget ' . $widgetName . '_' . $widgetAction . ' has not class name as ' . $widget;
            throw new BadMethodCallException($message);
        }
        if (!method_exists($widget, $widgetAction)) {
            $message = 'Widget ' . $widget . ' has not method name as ' . $widgetAction;
            throw new BadMethodCallException($message);
        }
    }

    private function checkIdTemplateFileExist(
        string $templateFileBasename,
        string $widgetNameStr,
        string $widgetActionStr
    ) {
        $templateFullPath = $this->config['templates_dir'] . '/_widgets/'
            . CaseConverter::toSnakeCase($widgetNameStr) . '/' . $templateFileBasename;
        if (!file_exists($templateFullPath)) {
            $message = sprintf(
                '%s  template file not found! %s needs a main template file at: %s',
                $templateFileBasename,
                $widgetNameStr . '_' . $widgetActionStr,
                $templateFullPath
            );
            throw new InvalidArgumentException($message);
        }
    }

    public function run() : string
    {
        return $this->twig->render($this->templateFile, $this->widgetData);

    }
}