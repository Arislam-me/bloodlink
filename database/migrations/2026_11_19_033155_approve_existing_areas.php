<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Areas / neighborhoods are now auto-approved; approve the ones still waiting. */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('areas')->where('is_approved', false)->update(['is_approved' => true]);
    }

    public function down(): void
    {
        // Nothing to undo.
    }
};
