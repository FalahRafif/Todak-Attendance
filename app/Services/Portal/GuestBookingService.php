<?php

namespace App\Services\Portal;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Package;
use App\Models\Reference;
use App\Repositories\Contracts\BookingHistoryRepositoryInterface;
use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use App\Repositories\Contracts\LocationPricingRuleRepositoryInterface;
use App\Repositories\Contracts\LocationRepositoryInterface;
use App\Repositories\Contracts\PackageRepositoryInterface;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use App\Repositories\Contracts\BillingInstallmentRepositoryInterface;
use App\Repositories\Contracts\ReferenceRepositoryInterface;
use App\Repositories\Contracts\SettingRepositoryInterface;
use App\Repositories\Contracts\AttachmentRepositoryInterface;
use App\Services\AttachmentSecurityService;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Illuminate\Http\UploadedFile;

class GuestBookingService
{
    private const INITIAL_BOOKING_STATUS_CODE = 'BS_WAITING_APPROVAL';

    private const ACTIVE_PACKAGE_STATUS_CODE = 'PS_ACTIVE';

    private const PACKAGE_TYPE_WEDDING_CODE = 'PKT_WEDDING';

    private const PACKAGE_TYPE_NON_WEDDING_CODE = 'PKT_NON_WEDDING';

    private const LOCATION_LEVEL_PROVINCE_CODE = 'LL_PV';

    private const LOCATION_LEVEL_CITY_CODE = 'LL_CT';

    private const LOCATION_LEVEL_DISTRICT_CODE = 'LL_KC';

    private const LOCATION_LEVEL_VILLAGE_CODE = 'LL_KL';

    private const EVENT_SESSION_MORNING_CODE = 'ES_PAGI_SIANG';

    private const EVENT_SESSION_EVENING_CODE = 'ES_SORE_MALAM';

    private const QUOTA_SETTING_MORNING_CODE = 'PKDR_MAX_QUOTA_PAGI_SIANG';

    private const QUOTA_SETTING_EVENING_CODE = 'PKDR_MAX_QUOTA_SORE_MALAM';

    private const PAYMENT_DATE_RULE_GROUP_ID = 'paymet_date_rule';

    private const PAYMENT_DATE_RULE_DP_CODE = 'PDR_DP';

    private const PAYMENT_DATE_RULE_FINAL_CODE = 'PDR_MAX_FINAL';

    private const DP_PERCENTAGE_GROUP_ID = 'payment_type_price_percentage';

    private const SUBMISSION_PROOF_DIRECTORY = 'booking-submission-proofs';

    private const BOOKING_CASE_ID_PREFIX = 'ETH';

    private const BOOKING_CASE_ID_PATTERN = '/^ETH-(\d{8})-(\d{5})$/i';

    private const BOOKING_REQUEST_CODE_PATTERN = '/^ETH-REQ-(\d{4})-(\d{6})$/i';

    /**
     * @var array<int, string>
     */
    private const NON_QUOTA_BOOKING_STATUS_CODES = [
        'BS_EXPIRED',
        'BS_EXPIRED_DP',
        'BS_CANCEL',
        'BS_RESCHEDULE',
        'BS_FORCE_MAJEURE',
        'BS_REFUND',
    ];

    /**
     * @var array{
     *     ids: array<int, int>,
     *     labels_by_code: array<string, string>,
     *     ids_by_code: array<string, int>
     * }|null
     */
    private ?array $locationLevelMeta = null;

    /**
     * @var array{
     *     ids_by_code: array<string, int>,
     *     labels_by_code: array<string, string>
     * }|null
     */
    private ?array $eventSessionMeta = null;

    /**
     * @var array<string, int>|null
     */
    private ?array $quotaMaxByCode = null;

    public function __construct(
        private PackageRepositoryInterface $packageRepository,
        private ReferenceRepositoryInterface $referenceRepository,
        private SettingRepositoryInterface $settingRepository,
        private LocationRepositoryInterface $locationRepository,
        private LocationPricingRuleRepositoryInterface $locationPricingRuleRepository,
        private CustomerRepositoryInterface $customerRepository,
        private BookingRepositoryInterface $bookingRepository,
        private BookingHistoryRepositoryInterface $bookingHistoryRepository,
        private PaymentRepositoryInterface $paymentRepository,
        private BillingInstallmentRepositoryInterface $billingInstallmentRepository,
        private AttachmentRepositoryInterface $attachmentRepository,
        private AttachmentSecurityService $attachmentSecurityService
    ) {
    }

    /**
     * @return array{
     *   packageTypeOptions: Collection<int, Reference>,
     *   packageOptions: Collection<int, Package>,
     *   eventSessionOptions: Collection<int, Reference>,
     *   provinceOptions: Collection<int, Location>
     * }
     */
    public function getFormPayload(): array
    {
        return [
            'packageTypeOptions' => $this->resolvePackageTypeOptions(),
            'packageOptions' => $this->resolveActivePackageOptions(),
            'eventSessionOptions' => $this->resolveEventSessionOptions(),
            'provinceOptions' => $this->resolveProvinceOptions(),
        ];
    }

    public function createBookingRequest(array $payload): Booking
    {
        return DB::transaction(function () use ($payload): Booking {
            $initialStatus = $this->resolveReferenceByGroupAndCode(
                'booking_status',
                self::INITIAL_BOOKING_STATUS_CODE,
                'Status awal booking tidak ditemukan.'
            );

            $selectedPackageType = $this->resolveReferenceByIdAndGroup(
                (int) $payload['package_type_id'],
                'package_type',
                'Tipe paket yang dipilih tidak valid.'
            );

            $selectedPackage = $this->resolveActivePackageOrFail((int) $payload['package_id']);
            if ((int) $selectedPackage->package_type !== (int) $selectedPackageType->getKey()) {
                throw new RuntimeException('Paket tidak sesuai dengan tipe paket yang dipilih.');
            }
            $selectedPackage = $this->ensurePackageCaseId($selectedPackage);

            $selectedVillage = $this->resolveLocationHierarchyOrFail(
                (int) $payload['location_province_id'],
                (int) $payload['location_city_id'],
                (int) $payload['location_district_id'],
                (int) $payload['location_village_id'],
                (int) $payload['location_id']
            );

            $selectedSession = $this->resolveReferenceByIdAndGroup(
                (int) $payload['event_session_id'],
                'event_session',
                'Sesi acara yang dipilih tidak valid.'
            );
            $this->assertSessionQuotaAvailable((string) $payload['event_date'], (int) $selectedSession->getKey());

            [$firstName, $lastName] = $this->splitCustomerName((string) $payload['name']);

            /** @var Customer $customer */
            $customer = $this->customerRepository->create([
                'uuid' => (string) Str::uuid(),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone_number' => (string) $payload['phone_number'],
            ]);

            /** @var Booking $booking */
            $booking = $this->bookingRepository->create([
                'uuid' => (string) Str::uuid(),
                'customer_id' => (int) $customer->getKey(),
                'package_id' => (int) $selectedPackage->getKey(),
                'status_id' => (int) $initialStatus->getKey(),
                'location_id' => (int) $selectedVillage->getKey(),
                'event_date' => (string) $payload['event_date'],
                'event_session' => (int) $selectedSession->getKey(),
                'event_detail' => $payload['event_detail'] ?? null,
                'google_maps_pin' => (string) $payload['google_maps_pin'],
            ]);

            $caseId = sprintf(
                '%s-%s-%05d',
                self::BOOKING_CASE_ID_PREFIX,
                ($booking->created_at ?? now())->format('Ymd'),
                (int) $booking->getKey()
            );
            $this->bookingRepository->update($booking, ['case_id' => $caseId]);

            $this->bookingHistoryRepository->create([
                'uuid' => (string) Str::uuid(),
                'booking_id' => (int) $booking->getKey(),
                'status_id' => (int) $initialStatus->getKey(),
                'operator_id' => null,
            ]);

            return $booking->loadMissing([
                'customer:id,first_name,last_name,phone_number',
                'package:id,case_id,name,address,price,package_type',
                'package.packageType:id,code,description',
                'status:id,code,description',
                'eventSession:id,code,description',
                'location:id,name',
            ]);
        });
    }

    public function buildRequestCode(Booking $booking): string
    {
        $createdAt = $booking->created_at ?? now();
        $bookingId = (int) $booking->getKey();

        return sprintf('ETH-REQ-%s-%06d', $createdAt->format('Y'), $bookingId);
    }

    public function buildBookingCaseId(Booking $booking): string
    {
        $storedCaseId = trim((string) ($booking->case_id ?? ''));
        if ($storedCaseId !== '') {
            return $storedCaseId;
        }

        $createdAt = $booking->created_at instanceof Carbon ? $booking->created_at : now();
        $bookingId = (int) $booking->getKey();

        return sprintf('%s-%s-%05d', self::BOOKING_CASE_ID_PREFIX, $createdAt->format('Ymd'), $bookingId);
    }

    /**
     * @return array{path:string,filename:string}
     */
    public function ensureSubmissionProofDocument(Booking $booking): array
    {
        $resolvedBooking = $this->hydrateBookingForSubmissionProof($booking);
        $relativePath = $this->resolveSubmissionProofRelativePath($resolvedBooking);

        if (!Storage::disk('local')->exists($relativePath)) {
            $payload = $this->buildSubmissionProofPayload($resolvedBooking);
            $pdf = Pdf::setPaper('a4')
                ->loadView('pages.public.booking-page.support.submission-proof-pdf', $payload);

            Storage::disk('local')->put($relativePath, $pdf->output());
        }

        return [
            'path' => $relativePath,
            'filename' => $this->buildSubmissionProofFileName($resolvedBooking),
        ];
    }

    public function getBookingForSubmissionProofByUuidOrFail(string $bookingUuid): Booking
    {
        $normalizedUuid = trim($bookingUuid);
        if ($normalizedUuid === '') {
            throw new RuntimeException('Data booking tidak valid.');
        }

        $booking = $this->bookingRepository
            ->query(true)
            ->where('uuid', $normalizedUuid)
            ->first([
                'id',
                'uuid',
                'customer_id',
                'package_id',
                'status_id',
                'location_id',
                'event_date',
                'event_session',
                'event_detail',
                'google_maps_pin',
                'created_at',
            ]);

        if (!$booking instanceof Booking) {
            throw new RuntimeException('Data booking tidak ditemukan.');
        }

        return $this->hydrateBookingForSubmissionProof($booking);
    }

    /**
     * @return array{
     *   booking_uuid:string,
     *   booking_case_id:string,
     *   request_code:string,
     *   status:array{code:string,label:string,tone:string},
     *   customer:array{name:string,phone_masked:string},
     *   event:array{date_label:string,session:string,detail:string,submitted_at:string},
     *   package:array{name:string,type:string,case_id:string,price:string,address:string},
     *   location_label:string,
     *   google_maps_pin:string,
     *   billing:array{status:string,total:string,paid:string,remaining:string}|null,
     *   history:array<int,array{status:string,time:string}>
     * }
     */
    public function getBookingStatusPayload(string $bookingCode, string $phoneLast4): array
    {
        $normalizedCode = trim($bookingCode);
        $normalizedPhoneLast4 = preg_replace('/\D+/', '', trim($phoneLast4)) ?? '';

        if ($normalizedCode === '' || strlen($normalizedPhoneLast4) !== 4) {
            throw new RuntimeException('Kode booking dan 4 digit verifikasi wajib diisi.');
        }

        $booking = $this->resolveBookingForStatusLookupOrFail($normalizedCode);
        $this->assertPhoneLastFourMatchesBooking($booking, $normalizedPhoneLast4);

        if ($booking->package instanceof Package) {
            $resolvedPackage = $this->ensurePackageCaseId($booking->package);
            $booking->setRelation('package', $resolvedPackage);
        }

        $eventDateLabel = '-';
        if ($booking->event_date instanceof Carbon) {
            $eventDateLabel = $booking->event_date->translatedFormat('d F Y');
        } elseif (!empty($booking->event_date)) {
            try {
                $eventDateLabel = Carbon::parse((string) $booking->event_date)->translatedFormat('d F Y');
            } catch (\Throwable) {
                $eventDateLabel = (string) $booking->event_date;
            }
        }

        $submittedAt = $booking->created_at instanceof Carbon
            ? $booking->created_at->translatedFormat('d F Y H:i')
            : now()->translatedFormat('d F Y H:i');

        $eventDetail = trim((string) ($booking->event_detail ?? ''));
        $statusCode = strtoupper(trim((string) ($booking->status?->code ?? '')));
        $statusDescription = trim((string) ($booking->status?->description ?? ''));
        $statusLabel = $statusDescription !== '' ? preg_replace('/\s*\(.*\)$/', '', $statusDescription) : '';
        $statusLabel = trim((string) $statusLabel);
        if ($statusLabel === '') {
            $statusLabel = $statusCode !== '' ? str_replace('_', ' ', strtolower(str_replace('BS_', '', $statusCode))) : '-';
            $statusLabel = ucwords($statusLabel);
        }

        $latestBilling = $booking->billings
            ->sortByDesc(function ($billing): int {
                $createdAt = $billing->created_at instanceof Carbon ? $billing->created_at : null;

                return $createdAt?->timestamp ?? 0;
            })
            ->first();

        $billingPayload = null;
        if ($latestBilling !== null) {
            $totalAmount = (float) ($latestBilling->total_amount ?? 0);
            $paidAmount = (float) ($latestBilling->total_paid ?? 0);
            $refundedAmount = (float) ($latestBilling->refunded_amount ?? 0);
            $remainingAmount = max($totalAmount - $paidAmount, 0);

            $billingStatusCode = strtoupper(trim((string) ($latestBilling->status?->code ?? '')));
            $billingStatusDescription = trim((string) ($latestBilling->status?->description ?? ''));

            $detailsPayload = $latestBilling->details->map(function ($d) {
                return [
                    'name' => trim((string) ($d->name ?? '-')),
                    'type' => trim((string) ($d->billingType?->description ?? '-')),
                    'amount' => (float) $d->amount,
                    'amount_label' => $this->formatRupiah((float) $d->amount),
                ];
            })->values()->all();

            $installmentsPayload = $latestBilling->installments->map(function ($inst) {
                $remainingAmount = max((float) $inst->amount - (float) $inst->paid_amount, 0);
                $hasPendingPayment = $inst->payments->contains(function ($p) {
                    return strtoupper(trim((string) ($p->status?->code ?? ''))) === 'PYS_PEDING';
                });
                return [
                    'id' => (int) $inst->id,
                    'type_code' => strtoupper(trim((string) ($inst->installmentType?->code ?? ''))),
                    'type' => trim((string) ($inst->installmentType?->description ?? '-')),
                    'amount' => (float) $inst->amount,
                    'amount_label' => $this->formatRupiah((float) $inst->amount),
                    'paid_amount' => (float) $inst->paid_amount,
                    'paid_label' => $this->formatRupiah((float) $inst->paid_amount),
                    'remaining_amount' => $remainingAmount,
                    'remaining_label' => $this->formatRupiah($remainingAmount),
                    'due_date' => $inst->due_date instanceof Carbon ? $inst->due_date->translatedFormat('d F Y') : trim((string) ($inst->due_date ?? '-')),
                    'status_code' => strtoupper(trim((string) ($inst->status?->code ?? ''))),
                    'status' => trim((string) ($inst->status?->description ?? '-')),
                    'has_pending_payment' => $hasPendingPayment,
                    'payments' => $inst->payments->map(function ($p) {
                        $pStatusCode = strtoupper(trim((string) ($p->status?->code ?? '')));
                        return [
                            'amount' => (float) $p->amount,
                            'amount_label' => $this->formatRupiah((float) $p->amount),
                            'method' => trim((string) ($p->paymentMethod?->description ?? '-')),
                            'status' => trim((string) ($p->status?->description ?? '-')),
                            'status_code' => $pStatusCode,
                            'paid_at' => $p->paid_at instanceof Carbon ? $p->paid_at->translatedFormat('d M Y H:i') : '-',
                            'rejection_reason' => $pStatusCode === 'PYS_FAILED' ? trim((string) ($p->rejection_reason ?? '')) : null,
                        ];
                    })->values()->all(),
                ];
            })->values()->all();

            $billingPayload = [
                'status_code' => $billingStatusCode,
                'status' => $billingStatusDescription !== '' ? $billingStatusDescription : '-',
                'total' => $this->formatRupiah($totalAmount),
                'total_raw' => $totalAmount,
                'paid' => $this->formatRupiah($paidAmount),
                'paid_raw' => $paidAmount,
                'refunded' => $this->formatRupiah($refundedAmount),
                'remaining' => $this->formatRupiah($remainingAmount),
                'remaining_raw' => $remainingAmount,
                'details' => $detailsPayload,
                'installments' => $installmentsPayload,
            ];
        }

        $historyPayload = $booking->history
            ->sortBy(function ($history): int {
                $createdAt = $history->created_at instanceof Carbon ? $history->created_at : null;

                return $createdAt?->timestamp ?? 0;
            })
            ->values()
            ->map(function ($history): array {
                $statusDescription = trim((string) ($history->status?->description ?? ''));
                $statusCode = strtoupper(trim((string) ($history->status?->code ?? '')));
                $statusLabel = $statusDescription !== '' ? preg_replace('/\s*\(.*\)$/', '', $statusDescription) : '';
                $statusLabel = trim((string) $statusLabel);
                if ($statusLabel === '') {
                    $statusLabel = $statusCode !== '' ? str_replace('_', ' ', strtolower(str_replace('BS_', '', $statusCode))) : '-';
                    $statusLabel = ucwords($statusLabel);
                }

                $timeLabel = '-';
                if ($history->created_at instanceof Carbon) {
                    $timeLabel = $history->created_at->translatedFormat('d M Y H:i');
                } elseif (!empty($history->created_at)) {
                    try {
                        $timeLabel = Carbon::parse((string) $history->created_at)->translatedFormat('d M Y H:i');
                    } catch (\Throwable) {
                        $timeLabel = (string) $history->created_at;
                    }
                }

                return [
                    'status' => $statusLabel,
                    'time' => $timeLabel,
                    'description' => trim((string) ($history->description ?? '')),
                ];
            })
            ->all();

        return [
            'booking_uuid' => trim((string) ($booking->uuid ?? '')),
            'booking_case_id' => $this->buildBookingCaseId($booking),
            'request_code' => $this->buildRequestCode($booking),
            'status' => [
                'code' => $statusCode,
                'label' => $statusLabel,
                'tone' => $this->resolveStatusTone($statusCode),
            ],
            'customer' => [
                'name' => trim(implode(' ', array_filter([
                    $booking->customer?->first_name,
                    $booking->customer?->last_name,
                ]))) ?: '-',
                'phone_masked' => $this->maskPhoneNumber((string) ($booking->customer?->phone_number ?? '')),
            ],
            'event' => [
                'date_label' => $eventDateLabel,
                'session' => trim((string) ($booking->eventSession?->description ?? '')) ?: '-',
                'detail' => $eventDetail !== '' ? $eventDetail : '-',
                'submitted_at' => $submittedAt,
            ],
            'package' => [
                'name' => trim((string) ($booking->package?->name ?? '')) ?: '-',
                'type' => trim((string) ($booking->package?->packageType?->description ?? '')) ?: '-',
                'case_id' => trim((string) ($booking->package?->case_id ?? '')) ?: '-',
                'price' => $this->formatRupiah((float) ($booking->package?->price ?? 0)),
                'address' => trim((string) ($booking->package?->address ?? '')) ?: '-',
            ],
            'location_label' => $this->resolveLocationHierarchyLabel($booking->location),
            'google_maps_pin' => trim((string) ($booking->google_maps_pin ?? '')) ?: '-',
            'billing' => $billingPayload,
            'history' => $historyPayload,
            'customer_actions' => $this->resolveCustomerActions($statusCode, $billingPayload),
            'admin_whatsapp' => $this->getAdminWhatsApp(),
            'whatsapp_templates' => $this->buildWhatsAppTemplates($statusCode, $booking),
        ];
    }

    /**
     * @return array{
     *     date: string,
     *     slots: array{
     *         morning: array{session_id:int,session_code:string,max:int,used:int,remaining:int,status:string,label:string},
     *         evening: array{session_id:int,session_code:string,max:int,used:int,remaining:int,status:string,label:string}
     *     }
     * }
     */
    public function getDateAvailability(string $eventDate): array
    {
        $date = Carbon::parse($eventDate)->toDateString();
        $sessionMeta = $this->resolveEventSessionMeta();
        $quotaMaxByCode = $this->resolveQuotaMaxByCode();

        $morningId = (int) ($sessionMeta['ids_by_code'][self::EVENT_SESSION_MORNING_CODE] ?? 0);
        $eveningId = (int) ($sessionMeta['ids_by_code'][self::EVENT_SESSION_EVENING_CODE] ?? 0);
        $usageBySessionId = $this->resolveBookedCountByDate($date, [$morningId, $eveningId]);

        $morningMax = (int) ($quotaMaxByCode[self::QUOTA_SETTING_MORNING_CODE] ?? 1);
        $morningUsed = (int) ($usageBySessionId[$morningId] ?? 0);
        $morningRemaining = max($morningMax - $morningUsed, 0);
        $morningStatus = $this->resolveQuotaStatus($morningUsed, $morningMax);

        $eveningMax = (int) ($quotaMaxByCode[self::QUOTA_SETTING_EVENING_CODE] ?? 1);
        $eveningUsed = (int) ($usageBySessionId[$eveningId] ?? 0);
        $eveningRemaining = max($eveningMax - $eveningUsed, 0);
        $eveningStatus = $this->resolveQuotaStatus($eveningUsed, $eveningMax);

        return [
            'date' => $date,
            'slots' => [
                'morning' => [
                    'session_id' => $morningId,
                    'session_code' => self::EVENT_SESSION_MORNING_CODE,
                    'max' => $morningMax,
                    'used' => $morningUsed,
                    'remaining' => $morningRemaining,
                    'status' => $morningStatus,
                    'label' => $this->resolveQuotaLabel($morningStatus, $morningRemaining, $morningMax),
                ],
                'evening' => [
                    'session_id' => $eveningId,
                    'session_code' => self::EVENT_SESSION_EVENING_CODE,
                    'max' => $eveningMax,
                    'used' => $eveningUsed,
                    'remaining' => $eveningRemaining,
                    'status' => $eveningStatus,
                    'label' => $this->resolveQuotaLabel($eveningStatus, $eveningRemaining, $eveningMax),
                ],
            ],
        ];
    }

    /**
     * @return Collection<int, Package>
     */
    private function resolveActivePackageOptions(): Collection
    {
        return $this->packageRepository
            ->query(true)
            ->with([
                'packageType:id,code,description',
                'status:id,code,description',
            ])
            ->whereHas('status', function (Builder $query): void {
                $query
                    ->where('group_id', 'package_status')
                    ->where('code', self::ACTIVE_PACKAGE_STATUS_CODE);
            })
            ->whereHas('packageType', function (Builder $query): void {
                $query
                    ->where('group_id', 'package_type')
                    ->whereIn('code', [
                        self::PACKAGE_TYPE_WEDDING_CODE,
                        self::PACKAGE_TYPE_NON_WEDDING_CODE,
                    ]);
            })
            ->orderBy('price')
            ->orderBy('name')
            ->get(['id', 'name', 'address', 'price', 'package_type', 'status_id']);
    }

    /**
     * @return Collection<int, Reference>
     */
    private function resolvePackageTypeOptions(): Collection
    {
        return $this->referenceRepository
            ->query(true)
            ->where('group_id', 'package_type')
            ->whereIn('code', [self::PACKAGE_TYPE_WEDDING_CODE, self::PACKAGE_TYPE_NON_WEDDING_CODE])
            ->orderByRaw("CASE WHEN code = ? THEN 0 WHEN code = ? THEN 1 ELSE 99 END", [
                self::PACKAGE_TYPE_WEDDING_CODE,
                self::PACKAGE_TYPE_NON_WEDDING_CODE,
            ])
            ->get(['id', 'code', 'description']);
    }

    /**
     * @return Collection<int, Reference>
     */
    private function resolveEventSessionOptions(): Collection
    {
        return $this->referenceRepository
            ->query(true)
            ->where('group_id', 'event_session')
            ->orderByRaw("CASE WHEN code = ? THEN 0 WHEN code = ? THEN 1 ELSE 99 END", [
                'ES_PAGI_SIANG',
                'ES_SORE_MALAM',
            ])
            ->get(['id', 'code', 'description']);
    }

    /**
     * @return Collection<int, Location>
     */
    private function resolveProvinceOptions(): Collection
    {
        return $this->locationRepository
            ->query(true)
            ->where('level_id', (int) $this->resolveLocationLevelIdByCodeOrFail(self::LOCATION_LEVEL_PROVINCE_CODE))
            ->orderBy('name')
            ->get(['id', 'name', 'level_id']);
    }

    /**
     * @return Collection<int, array{id: int, name: string}>
     */
    public function getLocationOptions(string $levelCode, ?int $parentId = null): Collection
    {
        $normalizedLevelCode = strtoupper(trim($levelCode));
        $levelId = $this->resolveLocationLevelIdByCodeOrFail($normalizedLevelCode);

        $query = $this->locationRepository
            ->query(true)
            ->where('level_id', $levelId);

        if ($normalizedLevelCode === self::LOCATION_LEVEL_PROVINCE_CODE) {
            $query->whereNull('parent_id');
        } else {
            $parent = is_int($parentId) && $parentId > 0 ? $parentId : null;
            if ($parent === null) {
                return collect();
            }

            $query->where('parent_id', $parent);
        }

        return $query
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(static fn (Location $location): array => [
                'id' => (int) $location->getKey(),
                'name' => $location->name,
            ])
            ->values();
    }

    /**
     * @param  array{
     *   package_id?: int|string|null,
     *   location_province_id?: int|string|null,
     *   location_city_id?: int|string|null,
     *   location_district_id?: int|string|null,
     *   location_village_id?: int|string|null,
     *   event_date?: string|null
     * }  $payload
     * @return array{
     *   package: array{
     *     id: int,
     *     name: string,
     *     type: string,
     *     address: string,
     *     description: string,
     *     benefits: array<int, string>,
     *     price: float,
     *     price_formatted: string
     *   },
     *   location_pricing_rule: array{
     *     found: bool,
     *     level_code: string|null,
     *     level_label: string|null,
     *     location_name: string|null,
     *     price_type_code: string|null,
     *     price_type_label: string|null,
     *     detail: string
     *   },
     *   payment: array{
     *     dp_percentage: int,
     *     dp_amount: float,
     *     dp_amount_formatted: string,
     *     remaining_amount: float,
     *     remaining_amount_formatted: string,
     *     dp_rule: string,
     *     dp_note: string,
     *     final_rule: string,
     *     final_note: string,
     *     final_due_date: string|null,
     *     final_due_date_label: string|null
     *   }
     * }
     */
    public function getPriceEstimate(array $payload): array
    {
        $packageId = (int) ($payload['package_id'] ?? 0);
        $package = $this->resolveActivePackageForEstimateOrFail($packageId);

        $orderedLocationCandidates = [
            [
                'id' => (int) ($payload['location_district_id'] ?? 0),
                'level_code' => self::LOCATION_LEVEL_DISTRICT_CODE,
                'level_label' => 'Kecamatan',
            ],
            [
                'id' => (int) ($payload['location_village_id'] ?? 0),
                'level_code' => self::LOCATION_LEVEL_VILLAGE_CODE,
                'level_label' => 'Kelurahan',
            ],
            [
                'id' => (int) ($payload['location_city_id'] ?? 0),
                'level_code' => self::LOCATION_LEVEL_CITY_CODE,
                'level_label' => 'Kota/Kabupaten',
            ],
            [
                'id' => (int) ($payload['location_province_id'] ?? 0),
                'level_code' => self::LOCATION_LEVEL_PROVINCE_CODE,
                'level_label' => 'Provinsi',
            ],
        ];

        $candidateIds = [];
        foreach ($orderedLocationCandidates as $candidate) {
            $candidateId = (int) ($candidate['id'] ?? 0);
            if ($candidateId > 0) {
                $candidateIds[] = $candidateId;
            }
        }

        $locationRule = null;
        if ($candidateIds !== []) {
            $rulesByLocationId = $this->locationPricingRuleRepository
                ->query(true)
                ->with([
                    'location:id,name,level_id,parent_id',
                    'location.level:id,code,description',
                    'priceType:id,code,description',
                ])
                ->whereIn('location_id', $candidateIds)
                ->get(['id', 'location_id', 'price_type'])
                ->keyBy('location_id');

            foreach ($orderedLocationCandidates as $candidate) {
                $candidateId = (int) ($candidate['id'] ?? 0);
                if ($candidateId <= 0) {
                    continue;
                }

                $matchedRule = $rulesByLocationId->get($candidateId);
                if ($matchedRule === null) {
                    continue;
                }

                $locationRule = [
                    'found' => true,
                    'level_code' => strtoupper((string) ($matchedRule->location?->level?->code ?? (string) $candidate['level_code'])),
                    'level_label' => trim((string) ($matchedRule->location?->level?->description ?? (string) $candidate['level_label'])) ?: (string) $candidate['level_label'],
                    'location_name' => trim((string) ($matchedRule->location?->name ?? '')),
                    'price_type_code' => strtoupper((string) ($matchedRule->priceType?->code ?? '')),
                    'price_type_label' => trim((string) ($matchedRule->priceType?->description ?? '')),
                    'detail' => sprintf(
                        'Prioritas level %s: %s',
                        (string) $candidate['level_label'],
                        trim((string) ($matchedRule->priceType?->description ?? '-'))
                    ),
                ];
                break;
            }
        }

        if (!is_array($locationRule)) {
            $locationRule = [
                'found' => false,
                'level_code' => null,
                'level_label' => null,
                'location_name' => null,
                'price_type_code' => null,
                'price_type_label' => null,
                'detail' => 'Belum ada aturan harga lokasi yang cocok. Silakan lengkapi lokasi acara.',
            ];
        }

        $packageTypeCode = strtoupper(trim((string) ($package->packageType?->code ?? '')));
        $defaultDpPercentageByPackageType = [
            self::PACKAGE_TYPE_WEDDING_CODE => 15,
            self::PACKAGE_TYPE_NON_WEDDING_CODE => 10,
        ];

        $dpSetting = $this->settingRepository
            ->query(true)
            ->where('group_id', self::DP_PERCENTAGE_GROUP_ID)
            ->where('type_id', (int) $package->package_type)
            ->first(['id', 'value']);

        $resolvedDpPercentage = $dpSetting !== null
            ? $this->parsePositiveNumber((string) $dpSetting->value)
            : 0;

        $dpPercentage = $resolvedDpPercentage > 0
            ? $resolvedDpPercentage
            : (int) ($defaultDpPercentageByPackageType[$packageTypeCode] ?? 0);

        $basePrice = (float) $package->price;
        $dpAmount = round(($basePrice * $dpPercentage) / 100, 2);
        $remainingAmount = max($basePrice - $dpAmount, 0);

        $paymentDateRules = $this->settingRepository
            ->query(true)
            ->where('group_id', self::PAYMENT_DATE_RULE_GROUP_ID)
            ->whereIn('code', [self::PAYMENT_DATE_RULE_DP_CODE, self::PAYMENT_DATE_RULE_FINAL_CODE])
            ->get(['code', 'value']);

        $dpRuleValue = '';
        $finalRuleValue = '';
        foreach ($paymentDateRules as $rule) {
            $code = strtoupper(trim((string) $rule->code));
            if ($code === self::PAYMENT_DATE_RULE_DP_CODE) {
                $dpRuleValue = strtoupper(trim((string) $rule->value));
            }
            if ($code === self::PAYMENT_DATE_RULE_FINAL_CODE) {
                $finalRuleValue = strtoupper(trim((string) $rule->value));
            }
        }

        $dpRuleParsed = $this->parseDayRule($dpRuleValue);
        $finalRuleParsed = $this->parseDayRule($finalRuleValue);

        $eventDateString = trim((string) ($payload['event_date'] ?? ''));
        $finalDueDate = null;
        if ($eventDateString !== '' && $finalRuleParsed !== null) {
            try {
                $eventDate = Carbon::parse($eventDateString)->startOfDay();
                if ($finalRuleParsed['operator'] === 'H+') {
                    $finalDueDate = $eventDate->copy()->addDays($finalRuleParsed['days']);
                } else {
                    $finalDueDate = $eventDate->copy()->subDays($finalRuleParsed['days']);
                }
            } catch (\Throwable) {
                $finalDueDate = null;
            }
        }

        $benefits = $package->benefits
            ->map(static function ($benefit): string {
                $name = trim((string) ($benefit->name ?? ''));
                $description = trim((string) ($benefit->description ?? ''));

                if ($name === '' && $description === '') {
                    return '';
                }

                if ($name !== '' && $description !== '') {
                    return $name . ' - ' . $description;
                }

                return $name !== '' ? $name : $description;
            })
            ->filter(static fn (string $benefit): bool => $benefit !== '')
            ->values()
            ->all();

        return [
            'package' => [
                'id' => (int) $package->getKey(),
                'name' => trim((string) $package->name),
                'type' => trim((string) ($package->packageType?->description ?? '-')),
                'address' => trim((string) ($package->address ?? '')),
                'description' => trim((string) ($package->description ?? '')),
                'benefits' => $benefits,
                'price' => $basePrice,
                'price_formatted' => $this->formatRupiah($basePrice),
            ],
            'location_pricing_rule' => $locationRule,
            'payment' => [
                'dp_percentage' => $dpPercentage,
                'dp_amount' => $dpAmount,
                'dp_amount_formatted' => $this->formatRupiah($dpAmount),
                'remaining_amount' => $remainingAmount,
                'remaining_amount_formatted' => $this->formatRupiah($remainingAmount),
                'dp_rule' => $dpRuleValue,
                'dp_note' => $this->resolveDayRuleNote($dpRuleParsed, 'approval/penawaran dibuat'),
                'final_rule' => $finalRuleValue,
                'final_note' => $this->resolveDayRuleNote($finalRuleParsed, 'tanggal acara'),
                'final_due_date' => $finalDueDate?->toDateString(),
                'final_due_date_label' => $finalDueDate?->translatedFormat('d F Y'),
            ],
        ];
    }

    private function assertSessionQuotaAvailable(string $eventDate, int $eventSessionId): void
    {
        $availability = $this->getDateAvailability($eventDate);
        $slots = $availability['slots'] ?? [];

        foreach ($slots as $slot) {
            if ((int) ($slot['session_id'] ?? 0) !== $eventSessionId) {
                continue;
            }

            if (($slot['status'] ?? 'unknown') === 'full') {
                throw new RuntimeException('Slot sesi pada tanggal yang dipilih sudah penuh. Pilih tanggal atau sesi lain.');
            }

            return;
        }
    }

    /**
     * @param  array<int, int>  $sessionIds
     * @return array<int, int>
     */
    private function resolveBookedCountByDate(string $eventDate, array $sessionIds): array
    {
        $filteredSessionIds = array_values(array_filter(array_map('intval', $sessionIds), static fn (int $id): bool => $id > 0));
        if ($filteredSessionIds === []) {
            return [];
        }

        $rows = $this->bookingRepository
            ->query(true)
            ->whereDate('event_date', $eventDate)
            ->whereIn('event_session', $filteredSessionIds)
            ->whereHas('status', function (Builder $query): void {
                $query
                    ->where('group_id', 'booking_status')
                    ->whereNotIn('code', self::NON_QUOTA_BOOKING_STATUS_CODES);
            })
            ->selectRaw('event_session, COUNT(*) AS aggregate')
            ->groupBy('event_session')
            ->get();

        $usageBySessionId = [];
        foreach ($rows as $row) {
            $sessionId = (int) ($row->event_session ?? 0);
            if ($sessionId <= 0) {
                continue;
            }

            $usageBySessionId[$sessionId] = (int) ($row->aggregate ?? 0);
        }

        return $usageBySessionId;
    }

    /**
     * @return array<string, int>
     */
    private function resolveQuotaMaxByCode(): array
    {
        if (is_array($this->quotaMaxByCode)) {
            return $this->quotaMaxByCode;
        }

        $defaults = [
            self::QUOTA_SETTING_MORNING_CODE => 1,
            self::QUOTA_SETTING_EVENING_CODE => 1,
        ];

        $settings = $this->settingRepository
            ->query(true)
            ->where('group_id', 'package_date_rule')
            ->whereIn('code', array_keys($defaults))
            ->get(['code', 'value']);

        foreach ($settings as $setting) {
            $code = strtoupper(trim((string) $setting->code));
            if (!array_key_exists($code, $defaults)) {
                continue;
            }

            $rawValue = trim((string) $setting->value);
            $parsedValue = (int) preg_replace('/[^0-9-]/', '', $rawValue);
            $defaults[$code] = $parsedValue > 0 ? $parsedValue : 1;
        }

        $this->quotaMaxByCode = $defaults;

        return $this->quotaMaxByCode;
    }

    /**
     * @return array{
     *     ids_by_code: array<string, int>,
     *     labels_by_code: array<string, string>
     * }
     */
    private function resolveEventSessionMeta(): array
    {
        if (is_array($this->eventSessionMeta)) {
            return $this->eventSessionMeta;
        }

        $required = [
            self::EVENT_SESSION_MORNING_CODE => 'Pagi-Siang',
            self::EVENT_SESSION_EVENING_CODE => 'Sore-Malam',
        ];

        $references = $this->referenceRepository
            ->query(true)
            ->where('group_id', 'event_session')
            ->whereIn('code', array_keys($required))
            ->get(['id', 'code', 'description']);

        $meta = [
            'ids_by_code' => [],
            'labels_by_code' => [],
        ];

        foreach ($references as $reference) {
            $code = strtoupper(trim((string) $reference->code));
            if (!isset($required[$code])) {
                continue;
            }

            $meta['ids_by_code'][$code] = (int) $reference->id;
            $description = trim((string) $reference->description);
            $meta['labels_by_code'][$code] = $description !== '' ? $description : $required[$code];
        }

        foreach (array_keys($required) as $code) {
            if (!isset($meta['ids_by_code'][$code])) {
                throw new RuntimeException('Reference sesi acara tidak lengkap.');
            }
        }

        $this->eventSessionMeta = $meta;

        return $this->eventSessionMeta;
    }

    private function resolveQuotaStatus(int $used, int $max): string
    {
        if ($max <= 0 || $used >= $max) {
            return 'full';
        }

        $remaining = $max - $used;
        if ($remaining <= 1) {
            return 'limited';
        }

        return 'available';
    }

    private function resolveQuotaLabel(string $status, int $remaining, int $max): string
    {
        if ($max <= 0) {
            return 'Penuh';
        }

        if ($status === 'full') {
            return 'Penuh';
        }

        if ($status === 'limited') {
            return sprintf('Tersisa %d dari %d slot', $remaining, $max);
        }

        return sprintf('Tersedia %d dari %d slot', $remaining, $max);
    }

    private function parsePositiveNumber(string $rawValue): int
    {
        $sanitized = preg_replace('/[^0-9]/', '', $rawValue);
        if ($sanitized === null || $sanitized === '') {
            return 0;
        }

        $parsed = (int) $sanitized;

        return $parsed > 0 ? $parsed : 0;
    }

    /**
     * @return array{operator: string, days: int}|null
     */
    private function parseDayRule(string $rawRule): ?array
    {
        $normalizedRule = strtoupper(trim($rawRule));
        if ($normalizedRule === '') {
            return null;
        }

        if (preg_match('/^(H\+|H-)(\d+)$/', $normalizedRule, $matches) !== 1) {
            return null;
        }

        $days = (int) $matches[2];
        if ($days < 0) {
            return null;
        }

        return [
            'operator' => $matches[1],
            'days' => $days,
        ];
    }

    /**
     * @param  array{operator: string, days: int}|null  $rule
     */
    private function resolveDayRuleNote(?array $rule, string $baseLabel): string
    {
        if ($rule === null) {
            return 'Aturan belum tersedia.';
        }

        $days = (int) ($rule['days'] ?? 0);
        $operator = (string) ($rule['operator'] ?? '');

        if ($operator === 'H+') {
            return sprintf('Maksimal %d hari setelah %s.', $days, $baseLabel);
        }

        return sprintf('Maksimal %d hari sebelum %s.', $days, $baseLabel);
    }

    private function formatRupiah(float $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    public function uploadPaymentProof(string $bookingCode, string $phoneLast4, int $installmentId, $transferReceipt): array
    {
        $booking = $this->resolveBookingForStatusLookupOrFail(trim($bookingCode));
        $this->assertPhoneLastFourMatchesBooking($booking, preg_replace('/\D+/', '', trim($phoneLast4)) ?? '');

        $statusCode = strtoupper(trim((string) ($booking->status?->code ?? '')));
        if (!in_array($statusCode, ['BS_APPROVED_WAITING_DP', 'BS_APPROVED_WAITING_FINAL_PAYMENT'], true)) {
            throw new RuntimeException('Upload bukti pembayaran tidak tersedia untuk status booking saat ini.');
        }

        $billing = $booking->billings->sortByDesc('id')->first();
        if (!$billing) {
            throw new RuntimeException('Billing tidak ditemukan.');
        }

        $billing->loadMissing([
            'installments.installmentType:id,code,description',
            'installments.status:id,code,description',
        ]);

        $installment = $billing->installments->first(function ($inst) use ($installmentId) {
            return (int) $inst->id === $installmentId;
        });

        if (!$installment) {
            throw new RuntimeException('Tagihan tidak ditemukan.');
        }

        $remainingAmount = max((float) $installment->amount - (float) $installment->paid_amount, 0);
        if ($remainingAmount <= 0) {
            throw new RuntimeException('Tagihan ini sudah lunas.');
        }

        $typeCode = strtoupper(trim((string) ($installment->installmentType?->code ?? '')));
        $pendingRef = $this->resolveReferenceByGroupAndCode('payment_status', 'PYS_PEDING', 'Status pembayaran tidak ditemukan.');

        $pendingCount = $this->paymentRepository
            ->query(true)
            ->where('billing_installment_id', $installment->id)
            ->where('status_id', $pendingRef->id)
            ->count();

        if ($pendingCount > 0) {
            throw new RuntimeException('Masih ada bukti pembayaran yang sedang menunggu verifikasi. Harap tunggu verifikasi dari tim kami.');
        }

        $paymentTypeCode = $typeCode === 'INS_DP' ? 'PYT_DP' : 'PYT_FINAL';
        $paymentTypeRef = $this->resolveReferenceByGroupAndCode('payment_type', $paymentTypeCode, 'Tipe pembayaran tidak ditemukan.');
        $bankTransferRef = $this->resolveReferenceByGroupAndCode('payment_method', 'PYM_BANK_TRANSFER', 'Metode pembayaran tidak ditemukan.');

        $paymentData = [
            'uuid' => Str::uuid()->toString(),
            'billing_installment_id' => $installment->id,
            'payment_type' => $paymentTypeRef->id,
            'status_id' => $pendingRef->id,
            'payment_method' => $bankTransferRef->id,
            'amount' => $remainingAmount,
            'paid_at' => now()->toDateTimeString(),
            'created_by' => null,
        ];

        if (!($transferReceipt instanceof UploadedFile)) {
            throw new RuntimeException('Bukti transfer wajib diunggah. Pilih file bukti transfer Anda.');
        }

        $fileSize = $transferReceipt->getSize();
        $maxSize = 10 * 1024 * 1024;
        if ($fileSize > $maxSize) {
            throw new RuntimeException('Ukuran file melebihi batas 10MB.');
        }

        $originalName = trim((string) ($transferReceipt->getClientOriginalName() ?? ''));
        if ($originalName !== '') {
            $duplicateFile = $this->attachmentRepository
                ->query(true)
                ->where('name', $originalName)
                ->whereHas('paymentsAsTransferReceipt', function ($q) use ($booking): void {
                    $q->whereHas('billingInstallment', function ($qi) use ($booking): void {
                        $qi->whereHas('billing', function ($qb) use ($booking): void {
                            $qb->where('booking_id', $booking->id);
                        });
                    });
                })
                ->exists();

            if ($duplicateFile) {
                throw new RuntimeException('File bukti transfer ini sudah pernah diunggah untuk booking yang sama. Harap unggah file yang berbeda.');
            }
        }

        $storagePayload = $this->attachmentSecurityService->storeEncryptedUploadedFile($transferReceipt, 'transfer-receipts');
        $encryptedPath = trim((string) ($storagePayload['encrypted_path'] ?? ''));
        if ($encryptedPath !== '') {
            $mimeType = strtolower(trim((string) ($transferReceipt->getMimeType() ?? '')));
            $typeCode = str_contains($mimeType, 'pdf') ? 'TF_DOC' : 'TF_IMG';
            $typeRef = $this->referenceRepository->query(true)
                ->where('group_id', 'type_file')
                ->where('code', $typeCode)
                ->firstOrFail();

            $attachment = $this->attachmentRepository->create([
                'uuid' => Str::uuid()->toString(),
                'name' => $originalName ?: 'transfer-receipt',
                'path' => $encryptedPath,
                'type_file' => $typeRef->id,
            ]);
            $paymentData['transfer_receipt_attachment_id'] = (int) $attachment->getKey();
        }

        $payment = $this->paymentRepository->create($paymentData);

        return [
            'payment_id' => (int) $payment->id,
            'amount' => $this->formatRupiah($remainingAmount),
            'status' => 'Menunggu verifikasi',
        ];
    }

    private function resolveBookingForStatusLookupOrFail(string $bookingCode): Booking
    {
        $query = $this->bookingRepository->query(true);
        $matched = false;

        if (preg_match(self::BOOKING_CASE_ID_PATTERN, $bookingCode, $matches) === 1) {
            $dateRaw = trim((string) ($matches[1] ?? ''));
            $idRaw = trim((string) ($matches[2] ?? ''));
            $bookingId = (int) ltrim($idRaw, '0');
            $bookingId = $bookingId > 0 ? $bookingId : 0;

            try {
                $date = Carbon::createFromFormat('Ymd', $dateRaw)->toDateString();
                $query->where('id', $bookingId)->whereDate('created_at', $date);
                $matched = true;
            } catch (\Throwable) {
                $matched = false;
            }
        }

        if (!$matched && preg_match(self::BOOKING_REQUEST_CODE_PATTERN, $bookingCode, $matches) === 1) {
            $year = (int) ($matches[1] ?? 0);
            $bookingId = (int) ltrim((string) ($matches[2] ?? ''), '0');
            $bookingId = $bookingId > 0 ? $bookingId : 0;

            if ($year > 0 && $bookingId > 0) {
                $query->where('id', $bookingId)->whereYear('created_at', $year);
                $matched = true;
            }
        }

        if (!$matched && ctype_digit($bookingCode)) {
            $query->where('id', (int) $bookingCode);
            $matched = true;
        }

        if (!$matched) {
            $query->where('uuid', trim($bookingCode));
            $matched = true;
        }

        $booking = $query->first([
            'id',
            'uuid',
            'customer_id',
            'package_id',
            'status_id',
            'location_id',
            'event_date',
            'event_session',
            'event_detail',
            'google_maps_pin',
            'created_at',
        ]);

        if (!$booking instanceof Booking) {
            throw new RuntimeException('Data booking tidak ditemukan atau verifikasi tidak sesuai.');
        }

        return $this->hydrateBookingForStatusLookup($booking);
    }

    public function getAdminWhatsApp(): string
    {
        $setting = $this->settingRepository->query(true)
            ->where('group_id', 'operational_config')
            ->where('code', 'ADMIN_WHATSAPP')
            ->first();

        return trim((string) ($setting?->value ?? ''));
    }

    private function buildWhatsAppTemplates(string $statusCode, Booking $booking): array
    {
        $caseId = $this->buildBookingCaseId($booking);
        $customerName = trim(implode(' ', array_filter([
            $booking->customer?->first_name,
            $booking->customer?->last_name,
        ])));

        $templates = [
            'support' => 'Halo tim Etherno, saya ' . $customerName . ' dengan Case ID ' . $caseId . '. Saya butuh bantuan terkait booking saya.',
        ];

        if ($statusCode === 'BS_APPROVED_WAITING_DP') {
            $templates['dp_paid'] = 'Halo tim Etherno, saya ' . $customerName . ' dengan Case ID ' . $caseId . '. Saya sudah melakukan pembayaran DP. Mohon diproses verifikasinya. Terima kasih.';
        }

        if ($statusCode === 'BS_APPROVED_WAITING_FINAL_PAYMENT') {
            $templates['final_paid'] = 'Halo tim Etherno, saya ' . $customerName . ' dengan Case ID ' . $caseId . '. Saya sudah melakukan pembayaran pelunasan. Mohon diproses verifikasinya. Terima kasih.';
        }

        return $templates;
    }

    private function resolveCustomerActions(string $statusCode, ?array $billingPayload): array
    {
        $actions = [];

        $dpInstallment = null;
        $payableInstallments = [];

        if ($billingPayload !== null) {
            foreach ($billingPayload['installments'] ?? [] as $inst) {
                $typeCode = strtoupper(trim((string) ($inst['type_code'] ?? '')));
                $hasPending = !empty($inst['has_pending_payment']);
                $hasFailed = false;
                if (is_array($inst['payments'] ?? null)) {
                    foreach ($inst['payments'] as $p) {
                        if (strtoupper(trim((string) ($p['status_code'] ?? ''))) === 'PYS_FAILED') {
                            $hasFailed = true;
                            break;
                        }
                    }
                }
                $remaining = (float) ($inst['remaining_amount'] ?? 0);
                $canUpload = ($remaining > 0 || $hasFailed) && !$hasPending && $typeCode !== 'INS_REFUND';
                if ($typeCode === 'INS_DP') {
                    $dpInstallment = $inst;
                }
                if ($canUpload && $typeCode !== 'INS_REFUND') {
                    $payableInstallments[] = $inst;
                }
            }
        }

        if ($statusCode === 'BS_APPROVED_WAITING_DP' && $dpInstallment !== null && !empty($dpInstallment['has_pending_payment'])) {
            $actions[] = 'upload_dp_pending';
        } elseif ($statusCode === 'BS_APPROVED_WAITING_DP' && $dpInstallment !== null && in_array($dpInstallment, $payableInstallments, true)) {
            $actions[] = 'upload_dp';
        }

        if ($statusCode === 'BS_APPROVED_WAITING_FINAL_PAYMENT') {
            $hasPendingFinal = false;
            foreach ($payableInstallments as $inst) {
                $typeCode = strtoupper(trim((string) ($inst['type_code'] ?? '')));
                if ($typeCode === 'INS_DP') continue;
                if (!empty($inst['has_pending_payment'])) {
                    $hasPendingFinal = true;
                }
            }
            if ($hasPendingFinal) {
                $actions[] = 'upload_final_pending';
            }
            foreach ($payableInstallments as $inst) {
                $typeCode = strtoupper(trim((string) ($inst['type_code'] ?? '')));
                if ($typeCode !== 'INS_DP' && empty($inst['has_pending_payment'])) {
                    $actions[] = 'upload_final';
                    break;
                }
            }
        }

        if ($statusCode === 'BS_CONFIRMED') {
            $actions[] = 'reschedule_request';
        }

        return $actions;
    }

    private function hydrateBookingForStatusLookup(Booking $booking): Booking
    {
        return $booking->loadMissing([
            'customer:id,first_name,last_name,phone_number',
            'package:id,case_id,name,address,price,package_type,created_at',
            'package.packageType:id,code,description',
            'status:id,code,description',
            'eventSession:id,code,description',
            'location:id,name,parent_id,level_id',
            'location.level:id,code,description',
            'location.parent:id,name,parent_id,level_id',
            'location.parent.parent:id,name,parent_id,level_id',
            'location.parent.parent.parent:id,name,parent_id,level_id',
            'history:id,booking_id,status_id,operator_id,description,created_at',
            'history.status:id,code,description',
            'history.operator:id,name',
            'billings:id,booking_id,total_amount,total_paid,refunded_amount,status_id,created_at',
            'billings.status:id,code,description',
            'billings.details:id,billing_id,billing_type,name,description,amount',
            'billings.details.billingType:id,code,description',
            'billings.installments:id,billing_id,installment_type,status_id,amount,due_date,paid_amount',
            'billings.installments.installmentType:id,code,description',
            'billings.installments.status:id,code,description',
            'billings.installments.payments:id,billing_installment_id,payment_type,status_id,payment_method,amount,paid_at',
            'billings.installments.payments.paymentType:id,code,description',
            'billings.installments.payments.status:id,code,description',
            'billings.installments.payments.paymentMethod:id,code,description',
        ]);
    }

    private function assertPhoneLastFourMatchesBooking(Booking $booking, string $phoneLast4): void
    {
        $customerPhone = trim((string) ($booking->customer?->phone_number ?? ''));
        $customerDigits = preg_replace('/\D+/', '', $customerPhone) ?? '';

        if ($customerDigits === '' || strlen($customerDigits) < 4) {
            throw new RuntimeException('Data booking tidak ditemukan atau verifikasi tidak sesuai.');
        }

        $expectedLast4 = substr($customerDigits, -4);
        if (!hash_equals($expectedLast4, $phoneLast4)) {
            throw new RuntimeException('Data booking tidak ditemukan atau verifikasi tidak sesuai.');
        }
    }

    public function submitRescheduleRequest(string $bookingCode, string $phoneLast4, string $proposedDate, string $reason): array
    {
        $normalizedCode = trim($bookingCode);
        $normalizedPhoneLast4 = preg_replace('/\D+/', '', trim($phoneLast4)) ?? '';

        if ($normalizedCode === '' || strlen($normalizedPhoneLast4) !== 4) {
            throw new RuntimeException('Kode booking dan 4 digit verifikasi wajib diisi.');
        }

        $reason = trim($reason);
        if ($reason === '') {
            throw new RuntimeException('Alasan reschedule wajib diisi.');
        }

        $parsedDate = null;
        try {
            $parsedDate = Carbon::parse($proposedDate);
        } catch (\Throwable) {
            throw new RuntimeException('Tanggal usulan tidak valid.');
        }

        $booking = $this->resolveBookingForStatusLookupOrFail($normalizedCode);
        $this->assertPhoneLastFourMatchesBooking($booking, $normalizedPhoneLast4);

        $statusCode = strtoupper(trim((string) ($booking->status?->code ?? '')));
        $allowedStatuses = ['BS_CONFIRMED', 'BS_APPROVED_WAITING_FINAL_PAYMENT'];
        if (!in_array($statusCode, $allowedStatuses, true)) {
            throw new RuntimeException('Reschedule hanya bisa diajukan untuk booking dengan status Confirmed atau Menunggu Pelunasan.');
        }

        $eventDate = $booking->event_date;
        if (!$eventDate instanceof Carbon) {
            throw new RuntimeException('Tanggal acara tidak ditemukan pada booking ini.');
        }

        $maxRescheduleSetting = $this->settingRepository
            ->query(true)
            ->where('group_id', 'package_date_rule')
            ->where('code', 'PKDR_MAX_RECHEDULE_DATE')
            ->first(['value']);

        $maxDaysBefore = 14;
        if ($maxRescheduleSetting && is_numeric(ltrim((string) ($maxRescheduleSetting->value ?? ''), 'Hh+-'))) {
            $maxDaysBefore = (int) ltrim((string) $maxRescheduleSetting->value, 'Hh+-');
        }

        $deadline = $eventDate->copy()->subDays($maxDaysBefore);
        if (Carbon::now()->startOfDay()->gt($deadline->startOfDay())) {
            throw new RuntimeException("Reschedule hanya dapat diajukan minimal {$maxDaysBefore} hari sebelum tanggal acara ({$deadline->translatedFormat('d F Y')}).");
        }

        if ($parsedDate->startOfDay()->lt(Carbon::now()->startOfDay())) {
            throw new RuntimeException('Tanggal usulan tidak boleh tanggal yang sudah lewat.');
        }

        return DB::transaction(function () use ($booking, $proposedDate, $reason): array {
            $rescheduleStatus = $this->resolveReferenceByGroupAndCode(
                'booking_status',
                'BS_RESCHEDULE',
                'Status reschedule tidak ditemukan.'
            );

            $caseId = $this->buildBookingCaseId($booking);
            $description = "Customer mengajukan reschedule ke {$proposedDate}. Alasan: {$reason}";

            $this->bookingHistoryRepository->create([
                'uuid' => (string) Str::uuid(),
                'booking_id' => $booking->id,
                'status_id' => $rescheduleStatus->id,
                'description' => $description,
            ]);

            $this->bookingRepository->update($booking, [
                'reschedule_date' => $proposedDate,
                'reschedule_reason' => $reason,
                'status_id' => $rescheduleStatus->id,
            ]);

            return [
                'case_id' => $caseId,
                'proposed_date' => $proposedDate,
                'message' => 'Request reschedule berhasil dikirim. Tim kami akan menghubungi Anda melalui WhatsApp.',
            ];
        });
    }

    private function maskPhoneNumber(string $phoneNumber): string
    {
        $digits = preg_replace('/\D+/', '', trim($phoneNumber)) ?? '';
        if ($digits === '') {
            return '-';
        }

        if (strlen($digits) <= 4) {
            return str_repeat('*', strlen($digits));
        }

        $maskedPrefix = str_repeat('*', max(strlen($digits) - 4, 0));
        $last4 = substr($digits, -4);

        return $maskedPrefix . $last4;
    }

    private function resolveStatusTone(string $statusCode): string
    {
        $normalized = strtoupper(trim($statusCode));

        return match ($normalized) {
            'BS_WAITING_APPROVAL' => 'warning',
            'BS_APPROVED_WAITING_DP', 'BS_APPROVED_WAITING_FINAL_PAYMENT' => 'info',
            'BS_CONFIRMED', 'BS_COMPLETE' => 'success',
            'BS_CANCEL', 'BS_EXPIRED', 'BS_EXPIRED_DP', 'BS_REFUND' => 'danger',
            default => 'neutral',
        };
    }

    private function hydrateBookingForSubmissionProof(Booking $booking): Booking
    {
        return $booking->loadMissing([
            'customer:id,first_name,last_name,phone_number',
            'package:id,case_id,name,address,price,package_type',
            'package.packageType:id,code,description',
            'status:id,code,description',
            'eventSession:id,code,description',
            'location:id,name,parent_id,level_id',
            'location.level:id,code,description',
            'location.parent:id,name,parent_id,level_id',
            'location.parent.parent:id,name,parent_id,level_id',
            'location.parent.parent.parent:id,name,parent_id,level_id',
        ]);
    }

    private function resolveSubmissionProofRelativePath(Booking $booking): string
    {
        $uuid = trim((string) ($booking->uuid ?? ''));
        if ($uuid === '') {
            $uuid = (string) Str::uuid();
        }

        return self::SUBMISSION_PROOF_DIRECTORY . '/' . $uuid . '.pdf';
    }

    private function buildSubmissionProofFileName(Booking $booking): string
    {
        $caseId = $this->buildBookingCaseId($booking);
        $safeCaseId = preg_replace('/[^A-Za-z0-9\-]/', '-', $caseId) ?: 'booking-proof';

        return 'Bukti-Pengajuan-' . $safeCaseId . '.pdf';
    }

    /**
     * @return array{
     *   booking_case_id:string,
     *   request_code:string,
     *   booking_date_label:string,
     *   submitted_at_label:string,
     *   customer_name:string,
     *   customer_phone:string,
     *   package_name:string,
     *   package_case_id:string,
     *   package_type:string,
     *   package_price:string,
     *   package_address:string,
     *   event_session:string,
     *   location_label:string,
     *   event_detail:string,
     *   google_maps_pin:string
     * }
     */
    private function buildSubmissionProofPayload(Booking $booking): array
    {
        $eventDateLabel = '-';
        if ($booking->event_date instanceof Carbon) {
            $eventDateLabel = $booking->event_date->translatedFormat('d F Y');
        } elseif (!empty($booking->event_date)) {
            try {
                $eventDateLabel = Carbon::parse((string) $booking->event_date)->translatedFormat('d F Y');
            } catch (\Throwable) {
                $eventDateLabel = (string) $booking->event_date;
            }
        }

        $submittedAt = $booking->created_at instanceof Carbon ? $booking->created_at : now();
        $eventDetail = trim((string) ($booking->event_detail ?? ''));
        $eventDetail = $eventDetail !== '' ? $eventDetail : '-';

        return [
            'booking_case_id' => $this->buildBookingCaseId($booking),
            'request_code' => $this->buildRequestCode($booking),
            'booking_date_label' => $eventDateLabel,
            'submitted_at_label' => $submittedAt->translatedFormat('d F Y H:i'),
            'customer_name' => trim(implode(' ', array_filter([
                $booking->customer?->first_name,
                $booking->customer?->last_name,
            ]))) ?: '-',
            'customer_phone' => trim((string) ($booking->customer?->phone_number ?? '')) ?: '-',
            'package_name' => trim((string) ($booking->package?->name ?? '')) ?: '-',
            'package_case_id' => trim((string) ($booking->package?->case_id ?? '')) ?: '-',
            'package_type' => trim((string) ($booking->package?->packageType?->description ?? '')) ?: '-',
            'package_price' => $this->formatRupiah((float) ($booking->package?->price ?? 0)),
            'package_address' => trim((string) ($booking->package?->address ?? '')) ?: '-',
            'event_session' => trim((string) ($booking->eventSession?->description ?? '')) ?: '-',
            'location_label' => $this->resolveLocationHierarchyLabel($booking->location),
            'event_detail' => $eventDetail,
            'google_maps_pin' => trim((string) ($booking->google_maps_pin ?? '')) ?: '-',
        ];
    }

    private function resolveLocationHierarchyLabel(?Location $location): string
    {
        if (!$location instanceof Location) {
            return '-';
        }

        $segments = [];
        $current = $location;

        while ($current instanceof Location) {
            $name = trim((string) ($current->name ?? ''));
            if ($name !== '') {
                $segments[] = $name;
            }

            $current = $current->parent;
        }

        if ($segments === []) {
            return '-';
        }

        return implode(', ', array_reverse($segments));
    }

    private function resolveActivePackageForEstimateOrFail(int $packageId): Package
    {
        $package = $this->packageRepository
            ->query(true)
            ->with([
                'packageType:id,code,description',
                'benefits:id,package_id,name,description',
            ])
            ->whereKey($packageId)
            ->whereHas('status', function (Builder $query): void {
                $query
                    ->where('group_id', 'package_status')
                    ->where('code', self::ACTIVE_PACKAGE_STATUS_CODE);
            })
            ->first(['id', 'name', 'address', 'description', 'price', 'status_id', 'package_type']);

        if (!$package instanceof Package) {
            throw new RuntimeException('Paket yang dipilih tidak tersedia atau tidak aktif.');
        }

        return $package;
    }

    private function resolveActivePackageOrFail(int $packageId): Package
    {
        $package = $this->packageRepository
            ->query(true)
            ->whereKey($packageId)
            ->whereHas('status', function (Builder $query): void {
                $query
                    ->where('group_id', 'package_status')
                    ->where('code', self::ACTIVE_PACKAGE_STATUS_CODE);
            })
            ->first(['id', 'name', 'case_id', 'price', 'status_id', 'package_type', 'created_at']);

        if (!$package instanceof Package) {
            throw new RuntimeException('Paket yang dipilih tidak tersedia atau tidak aktif.');
        }

        return $package;
    }

    private function ensurePackageCaseId(Package $package): Package
    {
        $existingCaseId = strtoupper(trim((string) ($package->case_id ?? '')));
        if ($existingCaseId !== '') {
            return $package;
        }

        $createdAt = $package->created_at instanceof Carbon ? $package->created_at : now();
        $packageId = (int) $package->getKey();
        $baseCaseId = sprintf('PKG-%s-%05d', $createdAt->format('Ymd'), $packageId);

        $candidate = $baseCaseId;
        $suffix = 1;
        while ($this->isPackageCaseIdUsedByAnotherPackage($candidate, $packageId)) {
            $candidate = sprintf('%s-%02d', $baseCaseId, $suffix);
            $suffix++;

            if ($suffix > 99) {
                $candidate = sprintf('PKG-%s-%s', $createdAt->format('Ymd'), strtoupper(substr((string) Str::uuid(), 0, 8)));
                if (!$this->isPackageCaseIdUsedByAnotherPackage($candidate, $packageId)) {
                    break;
                }
            }
        }

        /** @var Package $updatedPackage */
        $updatedPackage = $this->packageRepository->update($package, [
            'case_id' => $candidate,
        ]);

        return $updatedPackage;
    }

    private function isPackageCaseIdUsedByAnotherPackage(string $caseId, int $excludePackageId): bool
    {
        $normalizedCaseId = strtoupper(trim($caseId));
        if ($normalizedCaseId === '') {
            return false;
        }

        return $this->packageRepository
            ->query(true)
            ->whereRaw('UPPER(case_id) = ?', [$normalizedCaseId])
            ->where('id', '<>', $excludePackageId)
            ->exists();
    }

    private function resolveLocationHierarchyOrFail(
        int $provinceId,
        int $cityId,
        int $districtId,
        int $villageId,
        int $selectedLocationId
    ): Location
    {
        $province = $this->resolveLocationByIdAndLevelOrFail(
            $provinceId,
            self::LOCATION_LEVEL_PROVINCE_CODE,
            'Provinsi acara tidak valid.'
        );

        $city = $this->resolveLocationByIdAndLevelOrFail(
            $cityId,
            self::LOCATION_LEVEL_CITY_CODE,
            'Kota acara tidak valid.'
        );
        if ((int) ($city->parent_id ?? 0) !== (int) $province->getKey()) {
            throw new RuntimeException('Kota yang dipilih tidak berada pada provinsi yang dipilih.');
        }

        $district = $this->resolveLocationByIdAndLevelOrFail(
            $districtId,
            self::LOCATION_LEVEL_DISTRICT_CODE,
            'Kecamatan acara tidak valid.'
        );
        if ((int) ($district->parent_id ?? 0) !== (int) $city->getKey()) {
            throw new RuntimeException('Kecamatan yang dipilih tidak berada pada kota yang dipilih.');
        }

        $village = $this->resolveLocationByIdAndLevelOrFail(
            $villageId,
            self::LOCATION_LEVEL_VILLAGE_CODE,
            'Kelurahan acara tidak valid.'
        );
        if ((int) ($village->parent_id ?? 0) !== (int) $district->getKey()) {
            throw new RuntimeException('Kelurahan yang dipilih tidak berada pada kecamatan yang dipilih.');
        }

        if ((int) $village->getKey() !== $selectedLocationId) {
            throw new RuntimeException('Lokasi akhir acara tidak sinkron. Pilih ulang kelurahan.');
        }

        return $village;
    }

    private function resolveLocationByIdAndLevelOrFail(int $locationId, string $levelCode, string $errorMessage): Location
    {
        $location = $this->locationRepository
            ->query(true)
            ->whereKey($locationId)
            ->where('level_id', $this->resolveLocationLevelIdByCodeOrFail($levelCode))
            ->first(['id', 'name', 'level_id', 'parent_id']);

        if (!$location instanceof Location) {
            throw new RuntimeException($errorMessage);
        }

        return $location;
    }

    private function resolveLocationLevelIdByCodeOrFail(string $levelCode): int
    {
        $normalizedCode = strtoupper(trim($levelCode));
        $meta = $this->resolveLocationLevelMeta();

        if (!isset($meta['ids_by_code'][$normalizedCode])) {
            throw new RuntimeException('Reference level lokasi tidak lengkap.');
        }

        return (int) $meta['ids_by_code'][$normalizedCode];
    }

    /**
     * @return array{
     *     ids: array<int, int>,
     *     labels_by_code: array<string, string>,
     *     ids_by_code: array<string, int>
     * }
     */
    private function resolveLocationLevelMeta(): array
    {
        if (is_array($this->locationLevelMeta)) {
            return $this->locationLevelMeta;
        }

        $requiredCodes = [
            self::LOCATION_LEVEL_PROVINCE_CODE => 'Provinsi',
            self::LOCATION_LEVEL_CITY_CODE => 'Kota/Kabupaten',
            self::LOCATION_LEVEL_DISTRICT_CODE => 'Kecamatan',
            self::LOCATION_LEVEL_VILLAGE_CODE => 'Kelurahan',
        ];

        $references = $this->referenceRepository
            ->query(true)
            ->where('group_id', 'location_level')
            ->whereIn('code', array_keys($requiredCodes))
            ->get(['id', 'code', 'description']);

        $meta = [
            'ids' => [],
            'labels_by_code' => [],
            'ids_by_code' => [],
        ];

        foreach ($references as $reference) {
            $code = strtoupper((string) $reference->code);
            if (!isset($requiredCodes[$code])) {
                continue;
            }

            $id = (int) $reference->id;
            $meta['ids'][] = $id;
            $meta['labels_by_code'][$code] = $requiredCodes[$code];
            $meta['ids_by_code'][$code] = $id;
        }

        foreach (array_keys($requiredCodes) as $requiredCode) {
            if (!isset($meta['ids_by_code'][$requiredCode])) {
                throw new RuntimeException('Reference level lokasi tidak lengkap.');
            }
        }

        $this->locationLevelMeta = $meta;

        return $this->locationLevelMeta;
    }

    private function resolveReferenceByIdAndGroup(int $referenceId, string $groupId, string $errorMessage): Reference
    {
        $reference = $this->referenceRepository
            ->query(true)
            ->where('group_id', $groupId)
            ->whereKey($referenceId)
            ->first(['id', 'code', 'description', 'group_id']);

        if (!$reference instanceof Reference) {
            throw new RuntimeException($errorMessage);
        }

        return $reference;
    }

    private function resolveReferenceByGroupAndCode(string $groupId, string $code, string $errorMessage): Reference
    {
        $reference = $this->referenceRepository
            ->query(true)
            ->where('group_id', $groupId)
            ->where('code', $code)
            ->first(['id', 'code', 'description', 'group_id']);

        if (!$reference instanceof Reference) {
            throw new RuntimeException($errorMessage);
        }

        return $reference;
    }

    /**
     * @return array{0:string,1:string|null}
     */
    private function splitCustomerName(string $fullName): array
    {
        $normalized = trim(preg_replace('/\s+/', ' ', $fullName) ?? '');
        if ($normalized === '') {
            throw new RuntimeException('Nama customer wajib diisi.');
        }

        $segments = explode(' ', $normalized);
        $firstName = trim((string) array_shift($segments));
        $lastName = count($segments) > 0 ? trim(implode(' ', $segments)) : null;

        return [$firstName, $lastName !== '' ? $lastName : null];
    }
}
