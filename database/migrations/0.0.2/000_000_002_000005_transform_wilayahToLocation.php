<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		DB::table('locations')->update(['parent_id' => null]);
		DB::table('locations')->whereIn('wilayah_id', function ($query): void {
			$query->select('kode')->from('wilayah');
		})->delete();

		$levelIds = $this->resolveLocationLevelIds();
		$now = now();

		DB::table('wilayah')
			->select('kode', 'nama')
			->orderByRaw('LENGTH(kode) ASC')
			->orderBy('kode')
			->chunk(1000, function ($rows) use ($levelIds, $now): void {
				$records = [];

				foreach ($rows as $row) {
					$segmentCount = substr_count($row->kode, '.') + 1;
					$levelId = $this->mapSegmentCountToLevelId($segmentCount, $levelIds);

					if ($levelId === null) {
						throw new RuntimeException("Unknown wilayah code format: {$row->kode}");
					}

					$records[] = [
						'uuid' => (string) Str::uuid(),
						'wilayah_id' => $row->kode,
						'name' => $row->nama,
						'level_id' => $levelId,
						'parent_id' => null,
						'created_at' => $now,
						'created_by' => null,
						'updated_at' => $now,
						'updated_by' => null,
						'deleted_at' => null,
						'deleted_by' => null,
						'delete_status' => false,
					];
				}

				if ($records !== []) {
					DB::table('locations')->insert($records);
				}
			});

		$this->syncParentIdFromWilayahCode();
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('locations')
			->whereIn('wilayah_id', function ($query): void {
				$query->select('kode')->from('wilayah');
			})
			->delete();
	}

	/**
	 * @return array{provinsi: int, kota: int, kecamatan: int, kelurahan: int}
	 */
	private function resolveLocationLevelIds(): array
	{
		$references = DB::table('references')
			->select('id', 'code', 'description')
			->where('group_id', 'location_level')
			->where('delete_status', false)
			->get();

		$levels = [
			'provinsi' => null,
			'kota' => null,
			'kecamatan' => null,
			'kelurahan' => null,
		];

		foreach ($references as $reference) {
			$code = strtoupper((string) $reference->code);
			$description = strtolower((string) $reference->description);

			if ($code === 'LL_PV' || str_contains($description, 'provinsi')) {
				$levels['provinsi'] = (int) $reference->id;
				continue;
			}

			if ($code === 'LL_CT' || str_contains($description, 'kota')) {
				$levels['kota'] = (int) $reference->id;
				continue;
			}

			if ($code === 'LL_KC' || str_contains($description, 'kecamatan')) {
				$levels['kecamatan'] = (int) $reference->id;
				continue;
			}

			if ($code === 'LL_KL' || str_contains($description, 'kelurahan')) {
				$levels['kelurahan'] = (int) $reference->id;
			}
		}

		foreach ($levels as $name => $id) {
			if ($id === null) {
				throw new RuntimeException("Reference level '{$name}' not found in table references.");
			}
		}

		return [
			'provinsi' => $levels['provinsi'],
			'kota' => $levels['kota'],
			'kecamatan' => $levels['kecamatan'],
			'kelurahan' => $levels['kelurahan'],
		];
	}

	/**
	 * @param array{provinsi: int, kota: int, kecamatan: int, kelurahan: int} $levelIds
	 */
	private function mapSegmentCountToLevelId(int $segmentCount, array $levelIds): ?int
	{
		return match ($segmentCount) {
			1 => $levelIds['provinsi'],
			2 => $levelIds['kota'],
			3 => $levelIds['kecamatan'],
			4 => $levelIds['kelurahan'],
			default => null,
		};
	}

	private function syncParentIdFromWilayahCode(): void
	{
		if (DB::getDriverName() === 'mysql') {
			$parentIds = DB::table('locations')->pluck('id', 'wilayah_id');

			DB::table('locations')
				->select('id', 'wilayah_id')
				->where('wilayah_id', 'like', '%.%')
				->orderBy('id')
				->chunkById(1000, function ($rows) use ($parentIds): void {
					foreach ($rows as $row) {
						$parentWilayahId = preg_replace('/\.[^.]+$/', '', (string) $row->wilayah_id);
						$parentId = $parentIds[$parentWilayahId] ?? null;

						if ($parentId !== null) {
							DB::table('locations')
								->where('id', $row->id)
								->update(['parent_id' => $parentId]);
						}
					}
				});

			return;
		}

		DB::statement("
			UPDATE locations AS child
			SET parent_id = parent.id
			FROM locations AS parent
			WHERE parent.wilayah_id = regexp_replace(child.wilayah_id, '\\.[^.]+$', '')
				AND child.wilayah_id LIKE '%.%'
		");
	}
};
