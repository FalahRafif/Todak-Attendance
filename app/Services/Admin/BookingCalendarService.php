<?php

namespace App\Services\Admin;

use App\Models\Booking;
use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Repositories\Contracts\ReferenceRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class BookingCalendarService
{
    private const STATUS_GROUP = 'booking_status';

    /**
     * @var array<int, string>
     */
    private const WAITING_STATUS_CODES = [
        'BS_WAITING_APPROVAL',
        'BS_APPROVED_WAITING_DP',
    ];

    /**
     * @var array<int, string>
     */
    private const ACTIVE_STATUS_CODES = [
        'BS_APPROVED_WAITING_FINAL_PAYMENT',
        'BS_CONFIRMED',
        'BS_COMPLETE',
    ];

    /**
     * @var array<int, string>
     */
    private const CLOSED_STATUS_CODES = [
        'BS_CANCEL',
        'BS_EXPIRED',
        'BS_EXPIRED_DP',
        'BS_REFUND',
        'BS_RESCHEDULE',
        'BS_FORCE_MAJEURE',
    ];

    public function __construct(
        private BookingRepositoryInterface $bookingRepository,
        private ReferenceRepositoryInterface $referenceRepository
    ) {
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getPagePayload(array $filters): array
    {
        $resolvedFilters = $this->resolveFilters($filters);

        $baseQuery = $this->bookingRepository
            ->query(true)
            ->whereNotNull('event_date');

        $this->applyCustomDateRangeFilter($baseQuery, $resolvedFilters);

        $statusOptions = $this->getStatusOptions();
        $totalCount = (clone $baseQuery)->count();
        $statusFilters = $this->buildStatusFilters($statusOptions, $resolvedFilters, $baseQuery, $totalCount);

        return [
            'filters' => $resolvedFilters,
            'statusFilters' => $statusFilters,
            'stats' => $this->buildStats($baseQuery),
            'upcomingBookings' => $this->buildUpcomingBookings($baseQuery),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    public function getCalendarEvents(array $filters): array
    {
        $resolvedFilters = $this->resolveEventFilters($filters);

        $query = $this->bookingRepository
            ->query(true)
            ->with([
                'customer:id,first_name,last_name,phone_number',
                'package:id,name',
                'status:id,code,description',
                'eventSession:id,code,description',
                'location:id,name',
            ])
            ->whereNotNull('event_date');

        $this->applyStatusFilter($query, $resolvedFilters['status']);

        if ($resolvedFilters['view_start'] !== '') {
            $query->whereDate('event_date', '>=', $resolvedFilters['view_start']);
        }

        if ($resolvedFilters['view_end'] !== '') {
            $query->whereDate('event_date', '<=', $resolvedFilters['view_end']);
        }

        $this->applyCustomDateRangeFilter($query, $resolvedFilters);

        $bookings = $query
            ->orderBy('event_date')
            ->orderBy('id')
            ->limit(1000)
            ->get([
                'id',
                'uuid',
                'customer_id',
                'package_id',
                'status_id',
                'location_id',
                'event_date',
                'event_session',
                'created_at',
            ]);

        return $bookings->map(function (Booking $booking): array {
            $caseId = $this->buildCaseId($booking);
            $customerName = trim(implode(' ', array_filter([
                $booking->customer?->first_name,
                $booking->customer?->last_name,
            ])));
            $customerName = $customerName !== '' ? $customerName : 'Customer';

            $statusCode = strtoupper(trim((string) ($booking->status?->code ?? '')));
            $statusLabel = $this->resolveStatusLabel(
                $statusCode,
                (string) ($booking->status?->description ?? '')
            );
            $color = $this->resolveStatusColor($statusCode);
            $eventDate = $booking->event_date?->toDateString() ?? '';
            $eventSessionLabel = trim((string) ($booking->eventSession?->description ?? '-'));
            $packageName = trim((string) ($booking->package?->name ?? '-'));
            $locationName = trim((string) ($booking->location?->name ?? '-'));

            return [
                'id' => (string) $booking->getKey(),
                'title' => sprintf('%s • %s', $caseId, $customerName),
                'start' => $eventDate,
                'allDay' => true,
                'backgroundColor' => $color,
                'borderColor' => $color,
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'case_id' => $caseId,
                    'status_code' => $statusCode,
                    'status_label' => $statusLabel,
                    'session_label' => $eventSessionLabel !== '' ? $eventSessionLabel : '-',
                    'package_name' => $packageName !== '' ? $packageName : '-',
                    'location_name' => $locationName !== '' ? $locationName : '-',
                    'detail_url' => panel_route('admin.bookings.detail', ['booking' => $caseId]),
                ],
            ];
        })->all();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, string>
     */
    private function resolveFilters(array $filters): array
    {
        return [
            'status' => strtoupper(trim((string) ($filters['status'] ?? ''))),
            'date_start' => $this->normalizeDateString((string) ($filters['date_start'] ?? '')),
            'date_end' => $this->normalizeDateString((string) ($filters['date_end'] ?? '')),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, string>
     */
    private function resolveEventFilters(array $filters): array
    {
        $resolved = $this->resolveFilters($filters);

        return array_merge($resolved, [
            'view_start' => $this->normalizeDateString((string) ($filters['start'] ?? '')),
            'view_end' => $this->normalizeDateString((string) ($filters['end'] ?? '')),
        ]);
    }

    private function applyCustomDateRangeFilter(Builder $query, array $filters): void
    {
        $dateStart = trim((string) ($filters['date_start'] ?? ''));
        $dateEnd = trim((string) ($filters['date_end'] ?? ''));

        if ($dateStart !== '' && $dateEnd !== '') {
            $query->whereBetween('event_date', [$dateStart, $dateEnd]);
            return;
        }

        if ($dateStart !== '') {
            $query->whereDate('event_date', '>=', $dateStart);
        }

        if ($dateEnd !== '') {
            $query->whereDate('event_date', '<=', $dateEnd);
        }
    }

    private function applyStatusFilter(Builder $query, string $statusCode): void
    {
        $normalizedCode = strtoupper(trim($statusCode));
        if ($normalizedCode === '') {
            return;
        }

        $query->whereHas('status', function (Builder $builder) use ($normalizedCode): void {
            $builder
                ->where('group_id', self::STATUS_GROUP)
                ->where('code', $normalizedCode);
        });
    }

    /**
     * @return array<int, array{code:string,label:string}>
     */
    private function getStatusOptions(): array
    {
        $rows = $this->referenceRepository
            ->query(true)
            ->where('group_id', self::STATUS_GROUP)
            ->orderBy('id')
            ->get(['code', 'description']);

        return $rows->map(function ($row): array {
            $code = strtoupper(trim((string) $row->code));
            $label = $this->resolveStatusLabel($code, (string) ($row->description ?? ''));

            return [
                'code' => $code,
                'label' => $label,
            ];
        })->values()->all();
    }

    /**
     * @param  array<int, array{code:string,label:string}>  $statusOptions
     * @param  array<string, string>  $filters
     * @return array<int, array{code:string,label:string,count:int,is_active:bool,tone:string}>
     */
    private function buildStatusFilters(array $statusOptions, array $filters, Builder $baseQuery, int $totalCount): array
    {
        $currentStatus = strtoupper(trim((string) ($filters['status'] ?? '')));
        $filtersOutput = [
            [
                'code' => '',
                'label' => 'Semua Status',
                'count' => $totalCount,
                'is_active' => $currentStatus === '',
                'tone' => 'primary',
            ],
        ];

        foreach ($statusOptions as $status) {
            $count = (clone $baseQuery)
                ->whereHas('status', function (Builder $query) use ($status): void {
                    $query
                        ->where('group_id', self::STATUS_GROUP)
                        ->where('code', $status['code']);
                })
                ->count();

            $filtersOutput[] = [
                'code' => $status['code'],
                'label' => $status['label'],
                'count' => $count,
                'is_active' => $currentStatus === $status['code'],
                'tone' => $this->resolveStatusTone($status['code']),
            ];
        }

        return $filtersOutput;
    }

    /**
     * @return array<int, array{label:string,value:int,hint:string,tone:string}>
     */
    private function buildStats(Builder $baseQuery): array
    {
        $total = (clone $baseQuery)->count();
        $waiting = $this->countByStatusCodes($baseQuery, self::WAITING_STATUS_CODES);
        $active = $this->countByStatusCodes($baseQuery, self::ACTIVE_STATUS_CODES);
        $closed = $this->countByStatusCodes($baseQuery, self::CLOSED_STATUS_CODES);
        $occupiedDays = (clone $baseQuery)->distinct('event_date')->count('event_date');

        return [
            ['label' => 'Total Booking', 'value' => $total, 'hint' => 'Sesuai filter tanggal', 'tone' => 'primary'],
            ['label' => 'Menunggu Proses', 'value' => $waiting, 'hint' => 'Belum selesai', 'tone' => 'warning'],
            ['label' => 'Booking Aktif', 'value' => $active, 'hint' => 'Sudah berjalan', 'tone' => 'success'],
            ['label' => 'Hari Terisi', 'value' => $occupiedDays, 'hint' => 'Jumlah tanggal event', 'tone' => 'info'],
            ['label' => 'Closed / Inactive', 'value' => $closed, 'hint' => 'Selesai / batal / expired', 'tone' => 'danger'],
        ];
    }

    /**
     * @return array<int, array{case_id:string,customer:string,date:string,session:string,status_label:string,tone:string}>
     */
    private function buildUpcomingBookings(Builder $baseQuery): array
    {
        $today = Carbon::today()->toDateString();

        $rows = (clone $baseQuery)
            ->with([
                'customer:id,first_name,last_name',
                'status:id,code,description',
                'eventSession:id,description',
            ])
            ->whereDate('event_date', '>=', $today)
            ->orderBy('event_date')
            ->orderBy('id')
            ->limit(8)
            ->get(['id', 'event_date', 'event_session', 'status_id', 'customer_id', 'created_at']);

        return $rows->map(function (Booking $booking): array {
            $statusCode = strtoupper(trim((string) ($booking->status?->code ?? '')));
            $statusLabel = $this->resolveStatusLabel(
                $statusCode,
                (string) ($booking->status?->description ?? '')
            );
            $customerName = trim(implode(' ', array_filter([
                $booking->customer?->first_name,
                $booking->customer?->last_name,
            ])));

            return [
                'case_id' => $this->buildCaseId($booking),
                'customer' => $customerName !== '' ? $customerName : '-',
                'date' => $booking->event_date?->format('d M Y') ?? '-',
                'session' => trim((string) ($booking->eventSession?->description ?? '-')),
                'status_label' => $statusLabel,
                'tone' => $this->resolveStatusTone($statusCode),
            ];
        })->all();
    }

    /**
     * @param  array<int, string>  $codes
     */
    private function countByStatusCodes(Builder $baseQuery, array $codes): int
    {
        return (clone $baseQuery)
            ->whereHas('status', function (Builder $query) use ($codes): void {
                $query
                    ->where('group_id', self::STATUS_GROUP)
                    ->whereIn('code', $codes);
            })
            ->count();
    }

    private function buildCaseId(Booking $booking): string
    {
        $storedCaseId = trim((string) ($booking->case_id ?? ''));
        if ($storedCaseId !== '') {
            return $storedCaseId;
        }

        $createdAt = $booking->created_at ?? now();
        $id = (int) $booking->getKey();

        return sprintf('ETH-%s-%05d', $createdAt->format('Ymd'), $id);
    }

    private function normalizeDateString(string $rawDate): string
    {
        $normalized = trim($rawDate);
        if ($normalized === '') {
            return '';
        }

        try {
            return Carbon::parse($normalized)->toDateString();
        } catch (\Throwable) {
            return '';
        }
    }

    private function resolveStatusLabel(string $statusCode, string $description): string
    {
        $cleanDescription = trim(preg_replace('/\s*\(.*\)$/', '', trim($description)) ?? '');
        if ($cleanDescription !== '') {
            return $cleanDescription;
        }

        if ($statusCode === '') {
            return '-';
        }

        $label = str_replace('_', ' ', strtolower(str_replace('BS_', '', $statusCode)));

        return ucwords($label);
    }

    private function resolveStatusTone(string $statusCode): string
    {
        $normalized = strtoupper(trim($statusCode));

        return match ($normalized) {
            'BS_WAITING_APPROVAL' => 'warning',
            'BS_APPROVED_WAITING_DP', 'BS_APPROVED_WAITING_FINAL_PAYMENT' => 'info',
            'BS_CONFIRMED', 'BS_COMPLETE' => 'success',
            'BS_CANCEL', 'BS_EXPIRED', 'BS_EXPIRED_DP', 'BS_REFUND' => 'danger',
            default => 'secondary',
        };
    }

    private function resolveStatusColor(string $statusCode): string
    {
        $normalized = strtoupper(trim($statusCode));

        return match ($normalized) {
            'BS_WAITING_APPROVAL' => '#f59e0b',
            'BS_APPROVED_WAITING_DP', 'BS_APPROVED_WAITING_FINAL_PAYMENT' => '#0ea5e9',
            'BS_CONFIRMED', 'BS_COMPLETE' => '#10b981',
            'BS_CANCEL', 'BS_EXPIRED', 'BS_EXPIRED_DP', 'BS_REFUND' => '#ef4444',
            'BS_RESCHEDULE', 'BS_FORCE_MAJEURE' => '#8b5cf6',
            default => '#64748b',
        };
    }
}

