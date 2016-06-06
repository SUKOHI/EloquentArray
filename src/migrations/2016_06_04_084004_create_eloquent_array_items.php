<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEloquentArrayItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::create('eloquent_array_items', function (Blueprint $table) {
			$table->increments('id');
			$table->string('model')->index();
			$table->integer('parent_id')->unsigned()->index();
			$table->string('key')->index();
			$table->text('value');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('eloquent_array_items');
    }
}
