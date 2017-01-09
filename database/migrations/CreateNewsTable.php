<?php
 
use Illuminate\Database\Capsule\Manager as Capsule;
 
class CreateNewsTable
{
    public function up()
    {
        Capsule::schema()->create('news', function($table)
        {
            $table->increments('id');
            $table->string('title');
            $table->text('description');
            $table->text('link');
            $table->string('pubDate');

            $table->timestamps();
        });
    }

    public function down()
    {
        Capsule::schema()->dropIfExists('news');
    }
}
