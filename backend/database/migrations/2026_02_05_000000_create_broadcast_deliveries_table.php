<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up()
  {
    Schema::create('broadcast_deliveries', function (Blueprint $table) {
      $table->id();
      $table->uuid('broadcast_id');
      $table->string('recipient_name');
      $table->string('recipient_phone');
      $table->string('channel');
      $table->string('status');
      $table->string('error_message')->nullable();
      $table->timestamp('delivered_at')->nullable();
      $table->timestamps();

      $table->foreign('broadcast_id')->references('id')->on('broadcasts')->onDelete('cascade');
    });
  }

  public function down()
  {
    Schema::dropIfExists('broadcast_deliveries');
  }
};
