<?php
declare(strict_types = 1);

namespace Selami\View;

use Psr\Container\ContainerInterface;

interface ViewInterface
{
    public static function viewFactory(ContainerInterface $container, array $config) : ViewInterface;
    public function addGlobal(string $name, $value) : void;
    public function render(string $templateFile, array $parameters = []) : string;
}
