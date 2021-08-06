<?php

use Phalcon\Cli\Task;
use PolombardamModels\City;
use PolombardamModels\Merchant;

class MainTask extends Task {

    public function mainAction() {
        echo "You should choose task name to continue" . PHP_EOL;
    }

    public function updateGoodCountAction() {
        Merchant::recount_good();
        City::recount_good();
    }

}
