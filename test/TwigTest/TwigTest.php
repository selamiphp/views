<?php

namespace TwigTests;

use Selami\View\Twig\Twig;
use PHPUnit\Framework\TestCase;
use Twig\Loader\FilesystemLoader;
use Twig\Environment as TwigEnvironment;
use Twig\Error\RuntimeError;
use Zend\ServiceManager\ServiceManager;

class TwigTest extends TestCase
{
    private $config = [
        'runtime' => [
            'aliases' => [
                'about' => '{lang}/about',
                'logout' => 'logout'
            ],
            'query_parameters' => [
                'param1' => 1,
                'param2' => 2,
                'param3' => 3
            ],
            'base_url' => 'http://127.0.0.1',
            'config' => [
                'title' => 'Twig::test',
            ]
        ],
        'app_namespace' => 'TwigTests',
        'templates_path' => __DIR__ . '/templates',
        'cache_dir' => '/tmp',
        'title' => 'Twig::test',
        'debug' => 1,
        'auto_reload' => 1,
        'dictionary' => [
            'Hello %s' => 'Merhaba %s'
        ]
    ];

    private $view;

    protected function setUp(): void
    {

        $container = new ServiceManager();
        $loader = new FilesystemLoader($this->config['templates_path']);
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
        $result = $this->view->render('main.twig', ['app' => ['name' => TwigTest::class]]);
        $this->assertStringContainsString(
            '<span id="add_global">ok</span>',
            $result,
            'Twig didn\'t correctly render and add globals.'
        );
        $this->assertStringContainsString(
            '<span id="url_param">1</span>',
            $result,
            'Twig didn\'t correctly render and add globals.'
        );
        $this->assertStringContainsString('<title>Twig::test</title>', $result, "Twig didn't correctly render and add globals.");
    }



    /**
     * @test
     */
    public function shouldExtendForGetUrlSuccessfully()
    {
        $result = $this->view->render('get_url.twig', ['loaded_language' => 'tr_TR']);
        $this->assertStringContainsString('<span>http://127.0.0.1/logout</span>', $result, "Twig didn't correctly get url.");
        $this->assertStringContainsString('<span>http://127.0.0.1/tr_TR/about</span>', $result, "Twig didn't correctly get url.");
    }


    /**
     * @test
     */
    public function shouldExtendForTranslatorSuccessfully()
    {
        $result = $this->view->render('translation.twig');
        $this->assertStringContainsString('Merhaba Selami', $result, "Twig didn't correctly translate.");
        $this->assertStringContainsString('Text missing in dictionary', $result, "Twig didn't correctly translate.");
    }

    /**
     * @test
     */
    public function shouldExtendForQueryParamsSuccessfully()
    {
        $result = $this->view->render('query_params.twig', ['parameters' => $this->config['runtime']['query_parameters']]);
        $this->assertStringContainsString('<span>?param1=1&param2=2&param3=3</span>', $result, "Twig didn't correctly build http query.");
        $this->assertStringContainsString('<span>?controller=login&param1=1&param2=2&param3=3</span>', $result, "Twig didn't correctly build http query.");
    }

    /**
     * @test
     */
    public function shouldExtendForSiteUrlSuccessfully()
    {
        $result = $this->view->render('site_url.twig', ['parameters' => $this->config['runtime']['query_parameters']]);
        $this->assertStringContainsString('<span id="single">http://127.0.0.1</span>', $result, "Twig didn't correctly return base_url.");
        $this->assertStringContainsString('<span id="with_parameter">http://127.0.0.1/login</span>', $result, "Twig didn't correctly return base_url.");
    }

    /**
     * @test
     */
    public function shouldExtendForVarDumpSuccessfully()
    {
        $result = $this->view->render('var_dump.twig', ['parameters' => $this->config['runtime']['query_parameters']]);
        $this->assertStringContainsString('array(3)', $result, "Twig didn't correctly dump variable.");
        $this->assertStringContainsString('param1', $result, "Twig didn't correctly dump variable.");
        $this->assertStringContainsString('param2', $result, "Twig didn't correctly dump variable.");
        $this->assertStringContainsString('param3', $result, "Twig didn't correctly dump variable.");
    }

    /**
     * @test
     */
    public function shouldExtendForPaginationSuccessfully()
    {
        $data = [
            'total' => 20,
            'current' => 8,
            'linkTemplate' => $this->config['runtime']['base_url'] . '/list?page_num=(page_num)'
        ];
        $expected = '<ul class="pagination"><li class=""><a href="http://127.0.0.1/list?page_num=1" class="">1</a></li><li class=""><a href="http://127.0.0.1/list?page_num=2" class="">2</a></li><li class=""><a href="http://127.0.0.1/list?page_num=3" class="">3</a></li><li><a>...</a></li><li class=""><a href="http://127.0.0.1/list?page_num=7" class="">7</a></li><li class="active"><a href="http://127.0.0.1/list?page_num=8" class="active">8</a></li><li class=""><a href="http://127.0.0.1/list?page_num=9" class="">9</a></li><li><a>...</a></li><li class=""><a href="http://127.0.0.1/list?page_num=18" class="">18</a></li><li class=""><a href="http://127.0.0.1/list?page_num=19" class="">19</a></li><li class=""><a href="http://127.0.0.1/list?page_num=20" class="">20</a></li></ul>';
        $result = $this->view->render('pagination.twig', $data);
        $this->assertStringContainsString($expected, $result, "Twig didn't correctly return pagination.");
    }


    /**
     * @test
     */
    public function shouldExtendForWidgetsSuccessfully()
    {
        $this->expectException(RuntimeError::class);

        $this->view->render('widgets.twig', ['parameters' => $this->config['runtime']['query_parameters']]);
    }

    /**
     * @test
     */
    public function shouldRenderWidgetFromAnotherSourcesSuccessfully()
    {
        $this->view->addGlobal('add_global', '{{Widget_menu_top()}}');
        $result = $this->view->render('main.twig', ['app' => ['name' => TwigTest::class]]);

        $this->assertStringContainsString('<span id="top">top</span></span', $result, "Twig didn't correctly render for widgets from anotherSources.");
    }
}
