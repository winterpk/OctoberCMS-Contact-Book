<?php namespace Winterpk\Contactbook\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateContactsTable extends Migration
{

    public function up()
    {
        Schema::create('winterpk_contactbook_contacts', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
			$table->integer('user_id');
			$table->string('first_name');
			$table->string('last_name');
			$table->string('email');
			$table->string('street_address');
			$table->string('city');
			$table->string('state');
			$table->string('zip');
			$table->string('phone');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('winterpk_contactbook_contacts');
    }

}
