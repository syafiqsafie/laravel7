<?php

namespace App\Console\Commands\replace;

use Illuminate\Database\Migrations\Migration as DefaultMigration;
use Illuminate\Database\Schema\Blueprint;

use DB;

class Migration extends DefaultMigration
{

    public function timestamps(Blueprint $table)
    {
        $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->timestamp('updated_at')->nullable()->default(DB::raw('NULL on update CURRENT_TIMESTAMP'));
    }

    public function ownerships(Blueprint $table)
    {
        $table->string('created_from', 10)->default('')->comment('eg. PCRM, DCRM, SAP ');
        $table->string('created_by', 50)->default(0)->comment('emp code. 0 = admin');
        $table->string('edited_by', 50)->nullable();
    }

    public function createdAndUpdatedBy($table)
    {
        $table->string('created_by')->length(10)->nullable();
        $table->string('updated_by')->length(10)->nullable();
    }

    public function client($table)
    {
        $table->string('client_id', 14);
        $table->foreign('client_id')->references('id')->on('client');
    }
}
