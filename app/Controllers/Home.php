<?php

namespace App\Controllers;


class Home extends BaseController
{



    public function index(): string
    {
        $data = [

            'siteTitle' => "Dashboard"
        ];

        return view('welcome_message', $data);
    }
}
