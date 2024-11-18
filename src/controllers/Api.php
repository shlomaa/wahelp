<?php

namespace controllers;

use classes\Connection;
use classes\Queue;
use classes\Request;
use classes\Application;
use http\Cookie;
use jobs\NewsLetterJob;

class Api {


    public function actionDocument(Request $request, $arguments = []) {
        $action = isset($arguments[0]) ? $arguments[0] : '';

        switch ($action) {
            case 'upload':
                return $this->handleFileUpload($request);
        }

        return '';
    }

    public function actionTrigger(Request $request, $arguments = []) {
        $db = new Connection();
        $users = $db->makeRequest('SELECT * from users', []);
        foreach ($users as $user) {
            Queue::push(new NewsLetterJob($user));
        }
    }

    private function handleFileUpload($request) {

        $file = $request->getPost()['file'];
        $data = $this->readCsv($file);

        foreach ($data as $item) {
            $phone = $item[0];
            $username = $item[1];
            $db = new Connection();
            $db->makeRequest('INSERT INTO users (phone, name) VALUES (:phone, :name) ON DUPLICATE KEY UPDATE name=:name;', [
                ':phone' => $phone,
                ':name' => $username,
            ]);
        }

        return ['status' => 'OK'];
    }

    private function readCsv($file_content) {
        $res = [];
        $data_array = explode("\n", $file_content);
        foreach ($data_array as $item) {
            $data = str_getcsv($item);
            array_walk($data, function (&$element) {
                $element = trim($element);
            });
            $res[] = $data;
        }

        return $res;
    }

}
