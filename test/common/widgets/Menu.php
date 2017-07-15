<?php

namespace TwigTest\Widget;

class Menu
{

    public function Top()
    {
        return ['menu'=>'top'];
    }


    public function Top1()
    {
        return ['menu'=>'top1'];
    }

    public function Top2()
    {
        return ['menu'=>'top2'];
    }
    public function TopTemplateException()
    {
        return ['menu'=>'top_template_exception'];
    }
}
