<?php
declare(strict_types = 1);

namespace Selami\View\NullView;

use Selami\View\ViewInterface;
use Psr\Container\ContainerInterface;

class NullView implements ViewInterface
{
    public function __construct()
    {
    }

    public static function viewFactory(ContainerInterface $container, array $config) : ViewInterface
    {
        return new self();
    }

    public function addGlobal(string $name, $value) : void
    {
    }

    public function render(string $templateFile, array $parameters = []) : string
    {
        return $templateFile;
    }
}
