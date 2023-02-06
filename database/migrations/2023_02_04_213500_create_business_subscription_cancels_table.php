<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBusinessSubscriptionCancelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('business_subscription_cancels', function (Blueprint $table) {
            $table->increments('id');
            $table->string('business_profile_id');
            $table->integer('business_subscription_id')->comment('business subscription table id');
            $table->text('transaction_log')->nullable();
            $table->text('reason')->nullable();
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
        Schema::dropIfExists('business_subscription_cancels');
    }
}
