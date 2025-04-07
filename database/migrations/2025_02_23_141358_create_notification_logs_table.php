<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('type'); // holiday, birthday
            $table->string('title');
            $table->text('content');
            $table->string('status'); // sent, failed
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('notification_logs');
    }
};
