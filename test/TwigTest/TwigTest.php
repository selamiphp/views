<?php

namespace tests;

use Selami\View\Twig\Twig;
use InvalidArgumentException;
use BadMethodCallException;
use PHPUnit\Framework\TestCase;
use Twig\Loader\FilesystemLoader;
use Twig\Environment as TwigEnvironment;
use Zend\ServiceManager\ServiceManager;


class myTwigClass extends TestCase
{
    private $config = [
        'runtime' => [
        ],
        'app_namespace' => 'TwigTest',
        'base_url' => 'http://127.0.0.1',
        'templates_dir' => __DIR__ . '/templates',
        'cache_dir' => '/tmp',
        'title' => 'Twig::test',
        'debug' => 1,
        'auto_reload' => 1,
        'aliases' => [
            'about' => '{lang}/about',
            'logout' => 'logout'
        ],
        'query_parameters' => [
            'param1' => 1,
            'param2' => 2,
            'param3' => 3
        ]
    ];

    private $view;

    public function setUp()
    {

        $container = new ServiceManager();
        $loader = new FilesystemLoader($this->config['templates_dir']);
        $twig = new TwigEnvironment($loader, [
            'cache'         => $this->config['cache_dir'],
            'debug'         => $this->config['debug'],
            'auto_reload'   => $this->config['auto_reload']
        ]);

        $container->setService(TwigEnvironment::class, $twig);

        $this->view = Twig::viewFactory($container, $this->config);
    }

    /**
     * @test
     */
    public function shouldRenderHtmlAndAddGlobalsSuccessfully()
    {
        $this->view->addGlobal('add_global', 'ok');
        $result = $this->view->render('main.twig', ['app' => ['name' => 'myTwigClassTest']]);
        $this->assertContains('<span id="add_global">ok</span>', $result,
            'Twig didn\'t correctly render and add globals.');
        $this->assertContains('<span id="url_param">1</span>', $result,
            'Twig didn\'t correctly render and add globals.');
        $this->assertContains('<title>Twig::test</title>', $result, "Twig didn't correctly render and add globals.");
    }

    /**
     * @test
     */
    public function shouldExtendForTranslationSuccessfully()
    {
        $result = $this->view->render('translation.twig', ['translation' => ['loaded_language' => 'tr_TR']]);
        $this->assertContains('<span id="translation">tr_TR</span>', $result, "Twig didn't correctly translate.");
    }

    /**
     * @test
     */
    public function shouldExtendForGetUrlSuccessfully()
    {
        $result = $this->view->render('get_url.twig', ['loaded_language' => 'tr_TR']);
        $this->assertContains('<span>http://127.0.0.1/logout</span>', $result, "Twig didn't correctly get url.");
        $this->assertContains('<span>http://127.0.0.1/tr_TR/about</span>', $result, "Twig didn't correctly get url.");
    }

    /**
     * @test
     */
    public function shouldExtendForQueryParamsSuccessfully()
    {
        $result = $this->view->render('query_params.twig', ['parameters' => $this->config['query_parameters']]);
        $this->assertContains('<span>?param1=1&param2=2&param3=3</span>', $result, "Twig didn't correctly build http query.");
        $this->assertContains('<span>?controller=login&param1=1&param2=2&param3=3</span>', $result, "Twig didn't correctly build http query.");
    }

    /**
     * @test
     */
    public function shouldExtendForSiteUrlSuccessfully()
    {
        $result = $this->view->render('site_url.twig', ['parameters' => $this->config['query_parameters']]);
        $this->assertContains('<span id="single">http://127.0.0.1</span>', $result, "Twig didn't correctly return base_url.");
        $this->assertContains('<span id="with_parameter">http://127.0.0.1/login</span>', $result, "Twig didn't correctly return base_url.");
    }

    /**
     * @test
     */
    public function shouldExtendForVarDumpSuccessfully()
    {
        $result = $this->view->render('var_dump.twig', ['parameters' => $this->config['query_parameters']]);
        $this->assertContains('array(3)', $result, "Twig didn't correctly dump variable.");
        $this->assertContains('param1', $result, "Twig didn't correctly dump variable.");
        $this->assertContains('param2', $result, "Twig didn't correctly dump variable.");
        $this->assertContains('param3', $result, "Twig didn't correctly dump variable.");
    }

    /**
     * @test
     */
    public function shouldExtendForPaginationSuccessfully()
    {
        $data = [
            'total' => 20,
            'current' => 8,
            'linkTemplate' => $this->config['base_url'] . '/list?page_num=(page_num)'
        ];
        $expected = '<ul class="pagination"><li class=""><a href="http://127.0.0.1/list?page_num=1" class="">1</a></li><li class=""><a href="http://127.0.0.1/list?page_num=2" class="">2</a></li><li class=""><a href="http://127.0.0.1/list?page_num=3" class="">3</a></li><li><a>...</a></li><li class=""><a href="http://127.0.0.1/list?page_num=7" class="">7</a></li><li class="active"><a href="http://127.0.0.1/list?page_num=8" class="active">8</a></li><li class=""><a href="http://127.0.0.1/list?page_num=9" class="">9</a></li><li><a>...</a></li><li class=""><a href="http://127.0.0.1/list?page_num=18" class="">18</a></li><li class=""><a href="http://127.0.0.1/list?page_num=19" class="">19</a></li><li class=""><a href="http://127.0.0.1/list?page_num=20" class="">20</a></li></ul>';
        $result = $this->view->render('pagination.twig', $data);
        $this->assertContains($expected, $result, "Twig didn't correctly return pagination.");
    }


    /**
     * @test
     * @expectedException Twig_Error_Runtime
     */
    public function shouldExtendForWidgetsSuccessfully()
    {
        $result = $this->view->render('widgets.twig', ['parameters' => $this->config['query_parameters']]);
        $this->assertContains('<span id="top">top</span>', $result, "Twig didn't correctly return widget.");
        $this->assertContains('<span id="top1">top1</span>', $result, "Twig didn't correctly return widget.");
        $this->assertContains('<span id="top2">top2</span>', $result, "Twig didn't correctly return widget.");
    }

    /**
     * @test
     */
    public function shouldRenderWidgetFromAnotherSourcesSuccessfully()
    {
        $this->view->addGlobal('add_global', '{{Widget_menu_top()}}');
        $result = $this->view->render('main.twig', ['app' => ['name' => 'myTwigClassTest']]);

        $this->assertContains('<span id="top">top</span></span', $result, "Twig didn't correctly render for widgets from anotherSources.");
    }

}