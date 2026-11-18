<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('student')->after('email');
            $table->string('phone', 20)->nullable()->after('role');
            $table->string('matric_no', 30)->nullable()->after('phone');
            $table->boolean('is_active')->default(true)->after('matric_no');
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('unit', 20);                 // security | works | medical | counselling
            $table->string('color', 9)->default('#0b6e4f');
            $table->string('icon', 40)->default('shield');
            $table->string('description')->nullable();
            $table->json('subtypes')->nullable();
            $table->boolean('is_confidential')->default(false); // never shown on public map
            $table->unsignedTinyInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 12)->unique();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('subtype')->nullable();
            $table->string('title');
            $table->text('description');
            $table->string('severity', 10)->default('medium');
            $table->string('status', 15)->default('pending');
            $table->string('location_name')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->dateTime('occurred_at')->nullable();
            $table->string('photo_path')->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->boolean('is_sos')->default(false);
            $table->foreignId('reporter_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reporter_contact')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('verified_at')->nullable();
            $table->dateTime('resolved_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'category_id']);
        });

        Schema::create('report_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('from_status', 15)->nullable();
            $table->string('to_status', 15)->nullable();
            $table->text('note')->nullable();
            $table->boolean('is_internal')->default(false); // hidden from the reporter
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_updates');
        Schema::dropIfExists('reports');
        Schema::dropIfExists('categories');
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['role', 'phone', 'matric_no', 'is_active']));
    }
};
