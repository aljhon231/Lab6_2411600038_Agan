<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table): void {
            $table->renameColumn('capacity', 'maximum_slots');
            $table->string('category')->default('General');
            $table->string('instructor')->nullable();
        });

        Schema::table('enrollment_transactions', function (Blueprint $table): void {
            $table->string('type')->default('adjustment');
            $table->integer('quantity')->default(0);
            $table->string('reference_number')->nullable()->unique();
            $table->text('notes')->nullable();
        });

        DB::table('enrollment_transactions')->update([
            'quantity' => DB::raw('slot_change'),
            'notes' => DB::raw('reason'),
        ]);
        DB::table('enrollment_transactions')->where('action', 'enrolled')->update(['type' => 'enroll']);
        DB::table('enrollment_transactions')->where('action', 'cancelled')->update(['type' => 'cancel']);
        DB::table('enrollment_transactions')->where('action', 'slot_adjustment')->update(['type' => 'adjustment']);

        Schema::table('enrollment_transactions', function (Blueprint $table): void {
            $table->dropColumn(['action', 'slot_change', 'reason']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollment_transactions', function (Blueprint $table): void {
            $table->string('action')->default('slot_adjustment');
            $table->integer('slot_change')->default(0);
            $table->text('reason')->nullable();
        });

        DB::table('enrollment_transactions')->update([
            'slot_change' => DB::raw('quantity'),
            'reason' => DB::raw('notes'),
        ]);
        DB::table('enrollment_transactions')->where('type', 'enroll')->update(['action' => 'enrolled']);
        DB::table('enrollment_transactions')->where('type', 'cancel')->update(['action' => 'cancelled']);
        DB::table('enrollment_transactions')->where('type', 'adjustment')->update(['action' => 'slot_adjustment']);

        Schema::table('enrollment_transactions', function (Blueprint $table): void {
            $table->dropUnique(['reference_number']);
            $table->dropColumn(['type', 'quantity', 'reference_number', 'notes']);
        });

        Schema::table('courses', function (Blueprint $table): void {
            $table->renameColumn('maximum_slots', 'capacity');
            $table->dropColumn(['category', 'instructor']);
        });
    }
};
