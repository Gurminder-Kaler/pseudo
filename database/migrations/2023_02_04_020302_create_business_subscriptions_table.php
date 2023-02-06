<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBusinessSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('business_subscriptions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('business_profile_id');
            $table->string('status');
            $table->string('subscription_id');
            $table->string('activate_datetime')->nullable();
            $table->string('expire_datetime')->nullable();
            $table->text('transaction_log');
            $table->string('transaction_message');
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
        Schema::dropIfExists('business_subscriptions');
    }
}
