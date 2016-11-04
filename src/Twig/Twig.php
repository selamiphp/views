<?php
declare(strict_types = 1);

namespace Selami\View\Twig;

use Selami\View\ViewInterface;

class Twig implements ViewInterface
{
    private $twig = null;
    private $config = [
        'templates_dir' => '',
        'cache_dir'     => '',
        'debug'         => true,
        'auto_reload'  => true

    ];
    public function __construct($config, $queryParams)
    {
        $this->config = array_merge($this->config,$config);
        $loader     = new \Twig_Loader_Filesystem($this->config['templates_dir']);
        $this->twig = new \Twig_Environment($loader, [
            'cache'         => $this->config['cache_dir'],
            'debug'         => $this->config['debug'],
            'auto_reload'   => $this->config['auto_reload']
        ]);
        new TwigExtensions($this->twig, $this->config);
        $this->twig->addGlobal('_RC', $this->config); // Runtime Config values
        $this->twig->addGlobal('_QP', $queryParams); // Query Parameters ($_REQUEST)
    }

    public function addGlobal(string $name, $value)
    {
        $this->twig->addGlobal($name, $value);
    }

    public function render(string $templateFile, array $parameters=[])
    {
        $output = $this->twig->render($templateFile, $parameters);
        preg_match('/{{(\s*)Widget\_/msi', $output, $matches);
        if (isset($matches[1])) {
            $template = $this->twig->createTemplate($output);
            $output = $template->render([]);
        }
        return $output;
    }
}