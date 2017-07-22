<?php
declare(strict_types = 1);

namespace Selami\View\Twig;

use Selami\View\ViewInterface;
use Psr\Container\ContainerInterface;
use \Twig\Environment as TwigEnvironment;

class Twig implements ViewInterface
{
    private $twig;
    private $config;
    public function __construct(TwigEnvironment $twig, array $config)
    {
        $this->config = $config;
        $this->twig = $twig;
        $this->addGlobals();
        new TwigExtensions($this->twig, $this->config);
    }

    public static function viewFactory(ContainerInterface $container, array $config) : ViewInterface
    {
        $twig = $container->get(TwigEnvironment::class);
        return new static($twig, $config);
    }

    private function addGlobals() : void
    {
        // Runtime Config values
        $this->twig->addGlobal('RuntimeConfig', $this->config);
        // Query Parameters ($_REQUEST)
        $this->twig->addGlobal('QueryParameters', $this->config['runtime']['query_parameters']??[]);
        // Query Parameters ($_SESSION)
        $this->twig->addGlobal('Sessions', $this->config['runtime']['session']??[]);
        // Query Parameters ($_COOKIES)
        $this->twig->addGlobal('Cookies', $this->config['runtime']['cookies']??[]);
    }

    public function addGlobal(string $name, $value) : void
    {
        $this->twig->addGlobal($name, $value);
    }

    public function render(string $templateFile, array $parameters = []) : string
    {
        $output = $this->twig->render($templateFile, $parameters);
        preg_match('/{{(\s*)Widget\_/mi', $output, $matches);
        if (isset($matches[1])) {
            $template = $this->twig->createTemplate($output);
            $output = $template->render([]);
        }
        return $output;
    }
}
