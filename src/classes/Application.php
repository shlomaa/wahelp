<?php

namespace classes;

use classes\Request;

class Application {

    public static $config;

    public function __construct($conf) {
        if (!empty($conf)) {
            self::$config = $conf;
        }
    }

    public static function run() {
        $request = new Request();

        try {
            $respond = $request->runRoute();
        }
        catch (\Exception $e) {
            echo 'Error:<br/>';
            echo $e->getMessage();
        }

        print $respond;
    }
}
