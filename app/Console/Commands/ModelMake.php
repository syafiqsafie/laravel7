<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Console\ModelMakeCommand;
use Illuminate\Support\Str;

/**
 * Modify default laravel model folder and parent class when create using artisan.
 */
class ModelMake extends ModelMakeCommand
{
    protected function getNameInput()
    {
        return ucfirst(parent::getNameInput());
    }

    protected function getPath($name)
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);

        return $this->laravel['path'].'/Models/'.str_replace('\\', '/', $name).'.php';
    }

    protected function replaceNamespace(&$stub, $name)
    {
        $stub = str_replace(
            ['DummyNamespace', 'DummyRootNamespace', 'NamespacedDummyUserModel'],
            [$this->getNamespace($name)."\\Models", $this->rootNamespace()."\\Models", $this->userProviderModel()."\\Models"],
            $stub
        );

        return $this;
    }

    protected function replaceClass($stub, $name)
    {
        $stub = parent::replaceClass($stub, $name);

        return $this->replaceExtendToBaseModel($stub);
    }

    /**
     * Replace parent class with BaseModel and remove new line
     *
     * @param $stub
     *
     * @return mixed
     */
    protected function replaceExtendToBaseModel($stub)
    {
        if( $this->alreadyExists('BaseModel') ){
            $stub = str_replace('use Illuminate\Database\Eloquent\Model;', '', $stub);
            $stub = str_replace("\n\n\n", "\n", $stub);

            return str_replace('extends Model', 'extends BaseModel', $stub);
        }
        else
            return $stub;
    }
}
