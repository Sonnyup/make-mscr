<?php

namespace Jixk\MakeMscr;

use Illuminate\Support\ServiceProvider;

class CreateMscrServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->commands([
            MakeServiceCommand::class,
            MakeModelCommand::class,
            MSCRCommand::class,
        ]);
    }
}
