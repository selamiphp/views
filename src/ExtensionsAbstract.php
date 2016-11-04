<?php
declare(strict_types = 1);

namespace Selami\View;

/**
 * Class ViewExtensionsAbstract
 * @package Selami\ViewExtensionsAbstract
 */
abstract class ExtensionsAbstract
{

    /**
     * Load functions that will be used in the templates
     */
    protected function loadFunctions(){
        $this->extendForTranslation();
        $this->extendForWidget();
        $this->extendForPagination();
        $this->extendForSiteUrl();
        $this->extendForGetUrl();
        $this->extendForQueryParams();
        $this->extendForPagination();
        $this->extendForVarDump();
    }

    /**
     * Load extensions of templating engines
     */
    protected function loadExtensions(){}

    /**
     * Extend for function _t that translates using defined global lang variable
     * $lang = ['name' => 'Selami']
     * {{ _t('Hello @name',lang) }} produces Hello Selami
     */
    protected function extendForTranslation(){}

    /**
     * Extend for function getUrl that returns url path for an alias.
     *
     * Let's say $aliases = ['home' => '/', 'about' => '/about-us']
     * and $baseUrl = 'http://127.0.0.1';
     * {{ getUrl('about') }} produces http://127.0.0.1/about-us
     *
     * If returned alias value has parametric strings, you can pass the values of these parameters.
     * Let's say $aliases = ['home' => '/', 'about' => '/{lang}/about-us']
     * and $baseUrl = 'http://127.0.0.1' and $page_lang = 'en_US'
     * {{ getUrl('about', {'lang': page_lang}) }} produces http://127.0.0.1/en_US/about-us
     */
    protected function extendForGetUrl(){}

    /**
     * Extend for wildcard Widget functions. Widget function determines the class and method that will be called.
     * For example {{Widget_menu_top({'param':2})}} acts like this anonymous function:
     *  (function(){
     *      $Widget = new Widget\Menu();
     *      $WidgetContent = $Widget->top(['param' => 2]);
     *  }
     *  )();
     */
    protected function extendForWidget(){}

    /**
     * Extend for queryParams function that returns http_build_query result using passed parameters.
     * $prefix = '?';
     * {{ queryParams({'param1':1,'param2':2}, $prefix) }} returns ?param1=1&param2=2
     *
     */
    protected function extendForQueryParams(){}

    /**
     * Extend for function siteUrl that returns defined baseUrl of the site
     * Let's say $baseUrl = 'http://127.0.0.1';
     * {{ siteUrl('/home') }} produces http://127.0.0.1/home
     */
    protected function extendForSiteUrl(){}

    /**
     * Extend for function varDump. Just outputs var_dump of passed parameter.
     *
     * Use it for debugging purposes.
     *
     */
    protected function extendForVarDump(){}

    /**
     * Extend for function Pagination. This function builds pagination html.
     * Example:
     * $total_number_of_pages = 20; // Mandatory
     * $current_page = 2; // Mandatory. Starts from 1;
     * $link_template = '/list?page_num=(page_id)'; // Mandatory. Use "(page_id)" string to replace page number.
     * $parentTemplate = '<ul class="pagination">(items)</ul>'; // Optional. This is also default value.
     *                      Use '(item)' string to place generated item htmls.
     * $itemTemplate = '<li class="(item_class)">(link)</li>'; //  Optional. This is also default value.
     *                      Use '(item_class)' string where "active" class will be placed when the page is current page.
     *                      Use '(link)' string to place generated link html.
     * $linkItemTemplate = '<a href="(href)" class="(link_class)">(text)</a>'; // Optional. This is also default value.
     *                      '(href)' string is mandatory. generated link uri will be placed.
     *                      '(link_class)' string is optional. "active" class will be placed when it is current page.
     *                      '(text)' string is mandatory. Page number will be placed.
     * $ellipsesTemplate = '<li><a>...</a></li>'; // Optional. This is also default value.
     *
     * {{ Pagination($total_number_of_pages, $current_page, $link_template, $parentTemplate, $linkItemTemplate, $ellipsesTemplate) }}
     * or you can use with default values:
     * {{ Pagination($total_number_of_pages, $current_page, $link_template) }}
     * This returns following html.
     * <ul class="pagination">
     *      <li class=""><a href="/list?page_num=1" class="">1</a></li>
     *      <li class="active"><a href="/list?page_num=2" class="active">2</a></li>
     *      <li><a href="/list?page_num=3" class="">3</a></li>
     *      <li><a href="/list?page_num=4" class="">4</a></li>
     *      <li><a href="/list?page_num=5" class="">5</a></li>
     *      <li><a>...</a></li>
     *      <li><a href="/list?page_num=18" class="">3</a></li>
     *      <li><a href="/list?page_num=19" class="">3</a></li>
     *      <li><a href="/list?page_num=20" class="">3</a></li>
     * </ul>
     */
    protected function extendForPagination(){}
}