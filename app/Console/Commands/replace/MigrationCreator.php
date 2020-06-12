<?php

namespace App\Console\Commands\replace;

use Illuminate\Database\Migrations\MigrationCreator as DefaultMigrationCreator;

class MigrationCreator extends DefaultMigrationCreator
{

    protected function getStub($table, $create)
    {

        if (is_null($table)) {
            return $this->files->get(__DIR__.'/../stubs/blank.stub');
        }

        if( !is_null($table) && !$create )
            return $this->files->get(__DIR__.'/../stubs/altermigration.stub');

        return $this->files->get(__DIR__.'/../stubs/makemigration.stub');
    }
}
