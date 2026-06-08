<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		if (DB::getDriverName() !== 'pgsql') {
			return;
		}

		$indexes = [
			'CREATE INDEX IF NOT EXISTS idx_references_group_code_act ON "references" ("group_id", "code") WHERE "delete_status" = false',
			'CREATE INDEX IF NOT EXISTS idx_settings_group_code_act ON "settings" ("group_id", "code") WHERE "delete_status" = false',
			'CREATE INDEX IF NOT EXISTS idx_settings_group_type_act ON "settings" ("group_id", "type_id") WHERE "delete_status" = false',
			'CREATE INDEX IF NOT EXISTS idx_locations_parent_level_act ON "locations" ("parent_id", "level_id") WHERE "delete_status" = false',
			'CREATE INDEX IF NOT EXISTS idx_locations_level_wilayah_act ON "locations" ("level_id", "wilayah_id") WHERE "delete_status" = false',
			'CREATE INDEX IF NOT EXISTS idx_lpr_location_type_act ON "location_pricing_rules" ("location_id", "price_type") WHERE "delete_status" = false',
			'CREATE INDEX IF NOT EXISTS idx_attachments_type_name_act ON "attachments" ("type_file", "name") WHERE "delete_status" = false',
			'CREATE INDEX IF NOT EXISTS idx_packages_type_status_act ON "packages" ("package_type", "status_id") WHERE "delete_status" = false',
			'CREATE INDEX IF NOT EXISTS idx_pkg_benefits_package_act ON "package_benefits" ("package_id") WHERE "delete_status" = false',
			'CREATE INDEX IF NOT EXISTS idx_roles_name_act ON "roles" ("name") WHERE "delete_status" = false',
			'CREATE INDEX IF NOT EXISTS idx_users_role_act ON "users" ("role_id") WHERE "delete_status" = false',
			'CREATE INDEX IF NOT EXISTS idx_users_username_act ON "users" ("username") WHERE "delete_status" = false',
			'CREATE INDEX IF NOT EXISTS idx_customers_email_act ON "customers" ("email") WHERE "delete_status" = false',
			'CREATE INDEX IF NOT EXISTS idx_customers_phone_act ON "customers" ("phone_number") WHERE "delete_status" = false',
			'CREATE INDEX IF NOT EXISTS idx_customers_name_act ON "customers" ("first_name", "last_name") WHERE "delete_status" = false',
			'CREATE INDEX IF NOT EXISTS idx_bookings_status_event_date_act ON "bookings" ("status_id", "event_date") WHERE "delete_status" = false',
			'CREATE INDEX IF NOT EXISTS idx_bookings_customer_created_act ON "bookings" ("customer_id", "created_at" DESC) WHERE "delete_status" = false',
			'CREATE INDEX IF NOT EXISTS idx_bookings_operator_status_act ON "bookings" ("operator_id", "status_id") WHERE "delete_status" = false',
			'CREATE INDEX IF NOT EXISTS idx_bookings_location_event_date_act ON "bookings" ("location_id", "event_date") WHERE "delete_status" = false',
			'CREATE INDEX IF NOT EXISTS idx_bookings_package_event_date_act ON "bookings" ("package_id", "event_date") WHERE "delete_status" = false',
			'CREATE INDEX IF NOT EXISTS idx_bookings_reschedule_date_act ON "bookings" ("rechedule_date") WHERE "delete_status" = false AND "rechedule_date" IS NOT NULL',
			'CREATE INDEX IF NOT EXISTS idx_bookings_force_majeure_date_act ON "bookings" ("force_majeure_date") WHERE "delete_status" = false AND "force_majeure_date" IS NOT NULL',
			'CREATE INDEX IF NOT EXISTS idx_bkh_booking_created_act ON "booking_history" ("booking_id", "created_at" DESC) WHERE "delete_status" = false',
			'CREATE INDEX IF NOT EXISTS idx_bkh_status_created_act ON "booking_history" ("status_id", "created_at" DESC) WHERE "delete_status" = false',
			'CREATE INDEX IF NOT EXISTS idx_billings_booking_status_act ON "billings" ("booking_id", "status_id") WHERE "delete_status" = false',
			'CREATE INDEX IF NOT EXISTS idx_billings_status_created_act ON "billings" ("status_id", "created_at" DESC) WHERE "delete_status" = false',
			'CREATE INDEX IF NOT EXISTS idx_billing_details_bill_type_act ON "billing_details" ("billing_id", "billing_type") WHERE "delete_status" = false',
			'CREATE INDEX IF NOT EXISTS idx_billing_inst_bill_status_act ON "billing_installments" ("billing_id", "status_id") WHERE "delete_status" = false',
			'CREATE INDEX IF NOT EXISTS idx_billing_inst_status_due_act ON "billing_installments" ("status_id", "due_date") WHERE "delete_status" = false',
			'CREATE INDEX IF NOT EXISTS idx_payments_inst_status_act ON "payments" ("billing_installment_id", "status_id") WHERE "delete_status" = false',
			'CREATE INDEX IF NOT EXISTS idx_payments_status_paid_act ON "payments" ("status_id", "paid_at" DESC) WHERE "delete_status" = false',
			'CREATE INDEX IF NOT EXISTS idx_payments_method_paid_act ON "payments" ("payment_method", "paid_at" DESC) WHERE "delete_status" = false',
			'CREATE INDEX IF NOT EXISTS idx_prt_created_at ON "password_reset_tokens" ("created_at")',
			'CREATE INDEX IF NOT EXISTS idx_sessions_user_last_activity ON "sessions" ("user_id", "last_activity")',
			'CREATE INDEX IF NOT EXISTS idx_jobs_queue_reserved_available ON "jobs" ("queue", "reserved_at", "available_at")',
		];

		foreach ($indexes as $sql) {
			DB::statement($sql);
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		if (DB::getDriverName() !== 'pgsql') {
			return;
		}

		$indexes = [
			'idx_references_group_code_act',
			'idx_settings_group_code_act',
			'idx_settings_group_type_act',
			'idx_locations_parent_level_act',
			'idx_locations_level_wilayah_act',
			'idx_lpr_location_type_act',
			'idx_attachments_type_name_act',
			'idx_packages_type_status_act',
			'idx_pkg_benefits_package_act',
			'idx_roles_name_act',
			'idx_users_role_act',
			'idx_users_username_act',
			'idx_customers_email_act',
			'idx_customers_phone_act',
			'idx_customers_name_act',
			'idx_bookings_status_event_date_act',
			'idx_bookings_customer_created_act',
			'idx_bookings_operator_status_act',
			'idx_bookings_location_event_date_act',
			'idx_bookings_package_event_date_act',
			'idx_bookings_reschedule_date_act',
			'idx_bookings_force_majeure_date_act',
			'idx_bkh_booking_created_act',
			'idx_bkh_status_created_act',
			'idx_billings_booking_status_act',
			'idx_billings_status_created_act',
			'idx_billing_details_bill_type_act',
			'idx_billing_inst_bill_status_act',
			'idx_billing_inst_status_due_act',
			'idx_payments_inst_status_act',
			'idx_payments_status_paid_act',
			'idx_payments_method_paid_act',
			'idx_prt_created_at',
			'idx_sessions_user_last_activity',
			'idx_jobs_queue_reserved_available',
		];

		foreach ($indexes as $index) {
			DB::statement("DROP INDEX IF EXISTS {$index}");
		}
	}
};
