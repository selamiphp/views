<?php
declare(strict_types = 1);

namespace Selami\View;

interface ViewInterface
{
    public function addGlobal(string $name, $value) : void;

    public function render(string $templateFile, array $parameters = []) : string;
}
