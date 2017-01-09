<?php
 
use Illuminate\Database\Capsule\Manager as Capsule;
 
class CreateMigrationsTable
{
    public function up()
    {
        Capsule::schema()->create('migrations', function($table)
        {
            $table->increments('id');
            $table->string('name');
            $table->string('batch');

            $table->timestamps();
        });
    }
}
