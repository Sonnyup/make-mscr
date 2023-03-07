<?php

namespace Jixk\MakeMscr;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class MakeModelCommand extends GeneratorCommand
{
    protected $name = 'create:model';

    protected $description = 'Create a new model class';

    protected $type = 'Model';

    protected function getStub()
    {
        return __DIR__ . '/stubs/model.stub';
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getPath($name)
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);
        return $this->laravel['path'].'/'.str_replace('\\', '/', str_replace(class_basename($name), Str::studly(class_basename($name)), $name)).'.php';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Models';
    }

    protected function replaceNamespace(&$stub, $name)
    {
        $stub = str_replace(
            ['DummyNamespace', 'DummyRootNamespace', 'DummyClass', 'DummyTableName'],
            [$this->getNamespace($name), $this->rootNamespace(), Str::studly(class_basename($this->getNameInput())), Str::replaceFirst($this->rootNamespace()."Models\\", '', $name)],
            $stub
        );

        return $this;
    }
}
