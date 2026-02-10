<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up()
  {
    Schema::create('broadcast_deliveries', function (Blueprint $table) {
      $table->id();
      // broadcast_id should be a uuid that references broadcasts.id (uuid)
      $table->foreignUuid('broadcast_id')->constrained('broadcasts')->onDelete('cascade');
      $table->string('recipient_name');
      $table->string('recipient_phone');
      $table->string('channel');
      $table->string('status');
      $table->string('error_message')->nullable();
      $table->timestamp('delivered_at')->nullable();
      $table->timestamps();

      // foreign key created by foreignUuid above
    });
  }

  public function down()
  {
    Schema::dropIfExists('broadcast_deliveries');
  }
};
