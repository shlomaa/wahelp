<?php

namespace controllers;

use classes\Request;
use classes\Application;

class Test {


    public function actionIndex(Request $request, $arguments = []) {
        // set post fields
        $post = [
            'file' => file_get_contents(Application::$config['app']['document_root'] . '/web/test.csv'),
        ];

        $ch = curl_init('nginx:8080/api/document/upload');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);
        var_dump($response);
        // Check the return value of curl_exec(), too
        if ($response === false) {
            throw new \Exception(curl_error($ch), curl_errno($ch));
        }

        return '';
    }

}
