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
		$records = $this->loadWilayahRecords(database_path('migrations/0.0.2/dataset/wilayah.sql'));

		foreach (array_chunk($records, 1000) as $chunk) {
			DB::table('wilayah')->insert($chunk);
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		$records = $this->loadWilayahRecords(database_path('migrations/0.0.2/dataset/wilayah.sql'));
		$codes = array_column($records, 'kode');

		foreach (array_chunk($codes, 1000) as $chunk) {
			DB::table('wilayah')->whereIn('kode', $chunk)->delete();
		}
	}

	/**
	 * @return array<int, array{kode: string, nama: string}>
	 */
	private function loadWilayahRecords(string $path): array
	{
		$sql = file_get_contents($path);

		if ($sql === false) {
			throw new RuntimeException('Unable to read wilayah dataset.');
		}

		preg_match_all(
			"/\\(\\s*'((?:[^']|'')*)'\\s*,\\s*'((?:[^']|'')*)'\\s*\\)/",
			$sql,
			$matches,
			PREG_SET_ORDER
		);

		return array_map(static function (array $match): array {
			return [
				'kode' => str_replace("''", "'", $match[1]),
				'nama' => str_replace("''", "'", $match[2]),
			];
		}, $matches);
	}
};
