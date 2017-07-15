<?php
declare(strict_types=1);

namespace Selami\View\Twig\Functions;

use Selami\View\FunctionInterface;

class Pagination implements FunctionInterface
{
    const PAGE_GROUP_LIMIT = 3;
    private $total;
    private $current;
    private $linkTemplate;
    private $parentTemplate;
    private $itemTemplate;
    private $linkItemTemplate;
    private $ellipsesTemplate;

    public function __construct(
        int $total,
        int $current,
        string $linkTemplate,
        string $parentTemplate,
        string $itemTemplate,
        string $linkItemTemplate,
        string $ellipsesTemplate
    ) {
    
        $this->total = $total;
        $this->current = $current;
        $this->linkTemplate = $linkTemplate;
        $this->parentTemplate = $parentTemplate;
        $this->itemTemplate = $itemTemplate;
        $this->linkItemTemplate = $linkItemTemplate;
        $this->ellipsesTemplate = $ellipsesTemplate;
    }

    public function run(): string
    {
        $items =  '';
        $renderedEllipses = 0;
        $useEllipses = ($this->total > 10) ? 1 : 0;

        for ($i=1; $i <= $this->total; $i++) {
            $item = $this->getItem($i, $renderedEllipses, $useEllipses);
            $items .= $item[0];
            $renderedEllipses = $item[1];
        }
        return str_replace('(items)', $items, $this->parentTemplate);
    }

    private function getItem(int $i, $renderedEllipses, $useEllipses) : array
    {
        $values = [
            '(item_class)'  => '',
            '(href)'        => '',
            '(link_class)'  => '',
            '(text)'  => ''
        ];
        $class = $this->getClasses($i);
        $values['(link_class)'] = $class;
        $values['(item_class)'] = $class;
        $values['(text)'] = $i;
        $values['(href)'] = str_replace('(page_num)', $i, $this->linkTemplate);

        $itemData = $this->determineUseLink($i, $values, $useEllipses, $renderedEllipses);

        return $itemData;
    }

    private function determineUseLink(int $i, array $values, int $useEllipses, int $renderedEllipses) : array
    {
        $item ='';
        if ($useEllipses === 1) {
            return $this->useEllipses($i, $values, $renderedEllipses);
        }
        $link = strtr($this->linkItemTemplate, $values);
        $thisItemTemplate = str_replace(
            array('(link)'),
            array($link),
            $this->itemTemplate
        );
        $item = strtr($thisItemTemplate, $values);
        return [$item, $renderedEllipses];
    }

    private function useEllipses(int $i, array $values, int $renderedEllipses)
    {
        $pageGroupLimit = self::PAGE_GROUP_LIMIT;
        $item =  '';
        if (($i <= $pageGroupLimit)
            ||  ($i >= ($this->current-1) && $i <= ($this->current+1))
            || ($i >= ($this->total-2))
        ) {
            return $this->returnLink($values);
        }
        if ($renderedEllipses === 0) {
            $item = $this->ellipsesTemplate;
        }
        $renderedEllipses = 1;
        return [$item, $renderedEllipses];
    }

    private function returnLink($values)
    {
        $renderedEllipses = 0;
        $link = strtr($this->linkItemTemplate, $values);
        $thisItemTemplate = str_replace(
            array('(link)'),
            array($link),
            $this->itemTemplate
        );
        $item = strtr($thisItemTemplate, $values);
        return [$item, $renderedEllipses];
    }

    private function getClasses(int $i)
    {
        if ($i === $this->current) {
            return 'active';
        }
        return '';
    }
}
