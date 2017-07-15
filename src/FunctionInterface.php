<?php
declare(strict_types=1);

namespace Selami\View;

interface FunctionInterface
{
    public function run() : string;
}
