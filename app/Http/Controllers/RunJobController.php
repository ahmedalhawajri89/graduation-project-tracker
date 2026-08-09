<?php

namespace App\Http\Controllers;

class RunJobController extends Controller
{
    public function work()
    {

        \Artisan::call('queue:work');

    }

    public function restart()
    {
        \Artisan::call('queue:restart');

    }
}
