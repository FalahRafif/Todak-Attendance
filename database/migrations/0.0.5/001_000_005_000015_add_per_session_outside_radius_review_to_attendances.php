<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table): void {
            if (! Schema::hasColumn('attendances', 'check_in_review_status_id')) {
                $table->unsignedBigInteger('check_in_review_status_id')->nullable()->after('check_in_note')->index();
            }
            if (! Schema::hasColumn('attendances', 'check_in_reviewed_by')) {
                $table->unsignedBigInteger('check_in_reviewed_by')->nullable()->after('check_in_review_status_id')->index();
            }
            if (! Schema::hasColumn('attendances', 'check_in_reviewed_at')) {
                $table->timestamp('check_in_reviewed_at')->nullable()->after('check_in_reviewed_by');
            }
            if (! Schema::hasColumn('attendances', 'check_in_review_note')) {
                $table->text('check_in_review_note')->nullable()->after('check_in_reviewed_at');
            }

            if (! Schema::hasColumn('attendances', 'check_out_review_status_id')) {
                $table->unsignedBigInteger('check_out_review_status_id')->nullable()->after('check_out_note')->index();
            }
            if (! Schema::hasColumn('attendances', 'check_out_reviewed_by')) {
                $table->unsignedBigInteger('check_out_reviewed_by')->nullable()->after('check_out_review_status_id')->index();
            }
            if (! Schema::hasColumn('attendances', 'check_out_reviewed_at')) {
                $table->timestamp('check_out_reviewed_at')->nullable()->after('check_out_reviewed_by');
            }
            if (! Schema::hasColumn('attendances', 'check_out_review_note')) {
                $table->text('check_out_review_note')->nullable()->after('check_out_reviewed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table): void {
            $table->dropColumn([
                'check_in_review_status_id',
                'check_in_reviewed_by',
                'check_in_reviewed_at',
                'check_in_review_note',
                'check_out_review_status_id',
                'check_out_reviewed_by',
                'check_out_reviewed_at',
                'check_out_review_note',
            ]);
        });
    }
};
