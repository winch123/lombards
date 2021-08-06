<?php

use PolombardamModels\Page;

class StaticController extends ControllerBase {

    public function initialize() {
        $this->crumbs->add('home', '/', 'Главная');
    }

    public function indexAction() {
        $this->meta->setData(
                $this->dispatcher->getControllerName(), 'index'
        );

        $this->crumbs->add('static', '/article/', 'Статьи', false);

        $pages = Page::find("controller = 'static' AND parent = 'static' AND disabled IS NULL");

        $this->view->pagesUrl = '/article/';
        $this->view->pages = $pages;
    }

    public function pageAction() {
        $this->meta->setData(
                $this->dispatcher->getControllerName(), $this->dispatcher->getParam('page')
        );

        if ($this->meta->page->parent == 'static') {
            $this->crumbs->add('static', '/article/', 'Статьи');
        }

        $this->crumbs->add('page', '/article/' . $this->meta->page->action, $this->meta->page->title, false);
    }

}
