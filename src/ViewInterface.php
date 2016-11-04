<?php
declare(strict_types = 1);

namespace Selami\View;

interface ViewInterface
{
    public function addGlobal(string $name, $value);

    public function render(string $templateFile, array $parameters=[]);
}