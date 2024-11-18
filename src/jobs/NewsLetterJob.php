<?php

namespace jobs;

use jobs\JobInterface;

class NewsLetterJob implements JobInterface{

    private $data;

    public function __construct($data) {
        $this->data = $data;
    }

    public function exec()
    {
        $this->newsletterSend();
    }

    private function newsletterSend() {
        // @TODO: code here
    }
}
