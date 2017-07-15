<?php
declare(strict_types=1);

namespace Selami\View\Twig\Functions;

use Selami\View\FunctionInterface;

class GetUrl implements FunctionInterface
{
    private $aliases;
    private $alias;
    private $baseUrl;
    private $params;

    public function __construct(string $baseUrl, array $aliases, string $alias, array $params = [])
    {
        $this->aliases = $aliases;
        $this->alias = $alias;
        $this->params = $params;
        $this->baseUrl = $baseUrl;
    }

    public function run() : string
    {
        if (array_key_exists($this->alias, $this->aliases)) {
            return $this->findAlias();
        }
        return '';
    }

    private function findAlias() : string
    {
        $relativePath = $this->aliases[$this->alias];
        foreach ($this->params as $param => $value) {
            $relativePath = $this->findRelativePath($relativePath, $param, $value);
        }
        return $this->baseUrl . '/' . $relativePath;
    }

    private function findRelativePath($relativePath, $param, $value): string
    {
        if (strpos($param, ':') === strlen($param)-1) {
            return preg_replace('/{'.$param.'(.*?)}/msi', $value, $relativePath);
        }
        return str_replace('{'.$param.'}', $value, $relativePath);
    }
}
