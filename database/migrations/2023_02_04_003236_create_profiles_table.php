<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('current_business_subscription_id')->nullable()->comment('Current Business subscription table id');
            $table->string('is_subscription_active');
            $table->boolean('allow_brand')->default(0);
            $table->boolean('featured_dispensaries')->default(0);
            $table->boolean('featured_products')->default(0);
            $table->boolean('featured_brand')->default(0);
            $table->boolean('featured_strains')->default(0);
            $table->string('account_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('profiles');
    }
}
