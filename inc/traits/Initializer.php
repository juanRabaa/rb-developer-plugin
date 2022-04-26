<?php


trait Initializer {
    static private $initialized = false;

    static public function init(){
        if(self::$initialized)
            return;

        self::$initialized = true;
        self::on_init();
    }

    abstract static protected function on_init();
}
