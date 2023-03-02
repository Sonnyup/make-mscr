<?php

namespace Jixk\MakeMscr;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Support\Str;

class MakeServiceCommand extends GeneratorCommand
{
    protected $name = 'create:service';

    protected $description = '创建一个Service类';

    protected $type = 'Service';

    protected function getStub()
    {
        return __DIR__ . '/stubs/service.stub';
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
        return $this->laravel['path'].'/Services/'. Str::studly(basename(str_replace('\\', '/', $name))) .'.php';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Services';
    }

    protected function replaceNamespace(&$stub, $name)
    {
        $model = $this->argument('model');
        $stub = str_replace(
            ['DummyNamespace', 'DummyRootNamespace', 'DummyClass', 'DummyModelClass', 'DummyModelVariable'],
            [$this->getNamespace($name), $this->rootNamespace(), class_basename(str_replace('\\', '/', $this->getNameInput())), Str::studly(class_basename($model)), Str::camel(class_basename($model))],
            $stub
        );

        return $this;
    }

    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'Service的类名'],
            ['model', InputArgument::REQUIRED, '服务将使用的模型类的名称'],
        ];
    }
}
