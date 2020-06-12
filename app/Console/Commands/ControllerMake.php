<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Routing\Console\ControllerMakeCommand;
use Symfony\Component\Console\Input\InputOption;

class ControllerMake extends ControllerMakeCommand
{

    public function handle()
    {
        if( $this->option('project') ){
            parent::handle();
            $this->call('make:controller', ['name' => $this->argument('name'), '--project-api' => true]);
        }
        else{
            parent::handle();
        }
    }

    protected function getStub()
    {
        $stub = null;

        if( !$this->option('project') && !$this->option('project-api') )
            return parent::getStub();
        elseif( $this->option('project') )
            $stub = '/stubs/controller.stub';
        elseif( $this->option('project-api') )
            $stub = '/stubs/controller.api.stub';


        return __DIR__.$stub;
    }

    protected function getNameInput()
    {
        if( $this->option('project')  )
            return 'Web\\'.ucfirst(trim($this->argument('name'))).'Controller';
        elseif( $this->option('project-api') )
            return 'Api\\'.ucfirst(trim($this->argument('name'))).'ApiController';
        else
            return parent::getNameInput();
    }

    protected function buildClass($name)
    {
        parent::buildClass($name);

        $replace = [];

        if( $this->option('project') ){
            $replace = $this->replaceExtend($name);
        }
        elseif( $this->option('project-api') ){
            $replace = $this->replaceExtendApi($name);
        }
        return str_replace(
            array_keys($replace), array_values($replace), parent::buildClass($name)
        );
    }

    protected function getOptions()
    {
        return [
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'Generate a resource controller for the given model.'],
            ['resource', 'r', InputOption::VALUE_NONE, 'Generate a resource controller class.'],
            ['invokable', 'i', InputOption::VALUE_NONE, 'Generate a single method, invokable controller class.'],
            ['parent', 'p', InputOption::VALUE_OPTIONAL, 'Generate a nested resource controller class.'],
            ['api', null, InputOption::VALUE_NONE, 'Exclude the create and edit methods from the controller.'],
            ['project', null, InputOption::VALUE_NONE, 'Create Controller based on project developer coding standard'],
            ['project-api', null, InputOption::VALUE_NONE, 'Create Only Api Controller based on project developer coding standard'],
        ];
    }

    /**
     * Replace parent controller with api Controller
     *
     * @param $stub
     *
     * @return mixed
     */
    protected function replaceExtend($name)
    {

        $name = str_replace('\Web\\', '\\', $name);
        $controllerNamespace = $this->getNamespace($name);

        $replace = [];

        $replace["NamespaceDummyApiClass"] = "{$controllerNamespace}\Api\\" . ucfirst(trim($this->argument('name'))) . "ApiController";
        $replace['DummyApiClass'] = ucfirst(trim($this->argument('name'))) . 'ApiController';

        return $replace;
    }

    /**
     * Add Controller namespace
     *
     * @param $stub
     *
     * @return mixed
     */
    protected function replaceExtendApi($name)
    {
        $name = str_replace('\Api\\', '\\', $name);
        $controllerNamespace = $this->getNamespace($name);

        $replace = [];

        $replace["NamespaceDummyApiClass"] = "{$controllerNamespace}\Controller;";

        return $replace;
    }
}
