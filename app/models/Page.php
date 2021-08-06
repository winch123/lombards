<?php

namespace PolombardamModels;

class Page extends ModelBase {

    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var integer
     */
    public $disabled;

    /**
     *
     * @var string
     */
    public $controller;

    /**
     *
     * @var string
     */
    public $action;

    /**
     *
     * @var string
     */
    public $parent;

    /**
     *
     * @var string
     */
    public $title;

    /**
     *
     * @var string
     */
    public $title_extra;

    /**
     *
     * @var string
     */
    public $keywords;

    /**
     *
     * @var string
     */
    public $description;

    /**
     *
     * @var string
     */
    public $top_content;

    /**
     *
     * @var string
     */
    public $content;

    /**
     *
     * @var int
     */
    public $sort;

    public function initialize() {
        $this->setSource('page');
    }

    /**
     *
     * @return string
     */
    public function getUrl() {
        if ($this->action == 'index' && $this->controller == 'index') {
            return '/';
        } else {
            if ($this->action == 'index') {
                $url = '';
            } else if ($this->controller == 'static') {
                $url = 'article/' . $this->action;
            } else {
                $url = $this->controller . '/' . $this->action;
            }

            return '/' . $url;
        }
    }

}
