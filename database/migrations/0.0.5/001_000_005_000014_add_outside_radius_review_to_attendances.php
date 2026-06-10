<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table): void {
            if (! Schema::hasColumn('attendances', 'outside_radius_review_status_id')) {
                $table->unsignedBigInteger('outside_radius_review_status_id')->nullable()->after('is_need_approval')->index();
            }
            if (! Schema::hasColumn('attendances', 'outside_radius_reviewed_by')) {
                $table->unsignedBigInteger('outside_radius_reviewed_by')->nullable()->after('outside_radius_review_status_id')->index();
            }
            if (! Schema::hasColumn('attendances', 'outside_radius_reviewed_at')) {
                $table->timestamp('outside_radius_reviewed_at')->nullable()->after('outside_radius_reviewed_by');
            }
            if (! Schema::hasColumn('attendances', 'outside_radius_review_note')) {
                $table->text('outside_radius_review_note')->nullable()->after('outside_radius_reviewed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table): void {
            $table->dropColumn(['outside_radius_review_status_id', 'outside_radius_reviewed_by', 'outside_radius_reviewed_at', 'outside_radius_review_note']);
        });
    }
};
