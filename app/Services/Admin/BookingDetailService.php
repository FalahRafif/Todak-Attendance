<?php

namespace App\Services\Admin;

use App\Models\Billing;
use App\Models\Booking;
use App\Repositories\Contracts\AttachmentRepositoryInterface;
use App\Repositories\Contracts\BillingDetailRepositoryInterface;
use App\Repositories\Contracts\BillingInstallmentRepositoryInterface;
use App\Repositories\Contracts\BillingRepositoryInterface;
use App\Repositories\Contracts\BookingHistoryRepositoryInterface;
use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Repositories\Contracts\LocationPricingRuleRepositoryInterface;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use App\Repositories\Contracts\ReferenceRepositoryInterface;
use App\Repositories\Contracts\SettingRepositoryInterface;
use App\Services\AttachmentSecurityService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use RuntimeException;

class BookingDetailService
{
    public function __construct(
        private BookingRepositoryInterface $bookingRepository,
        private BookingHistoryRepositoryInterface $bookingHistoryRepository,
        private BillingRepositoryInterface $billingRepository,
        private BillingDetailRepositoryInterface $billingDetailRepository,
        private BillingInstallmentRepositoryInterface $billingInstallmentRepository,
        private PaymentRepositoryInterface $paymentRepository,
        private AttachmentRepositoryInterface $attachmentRepository,
        private AttachmentSecurityService $attachmentSecurityService,
        private ReferenceRepositoryInterface $referenceRepository,
        private SettingRepositoryInterface $settingRepository,
        private LocationPricingRuleRepositoryInterface $locationPricingRuleRepository
    ) {}

    public function getPagePayload(string $caseId): array
    {
        $booking = $this->resolveBooking($caseId);

        if (!$booking) {
            return ['booking' => null];
        }

        $statusCode = strtoupper(trim((string) ($booking->status?->code ?? '')));
        $packageTypeCode = strtoupper(trim((string) ($booking->package?->packageType?->code ?? '')));
        $billingPayload = $this->buildBillingPayload($booking);

        return [
            'booking' => $booking,
            'caseId' => $this->buildCaseId($booking),
            'statusCode' => $statusCode,
            'statusLabel' => $this->resolveStatusLabel($statusCode),
            'statusBadge' => $this->resolveStatusBadge($statusCode),
            'paymentBadge' => $this->resolvePaymentBadge($statusCode),
            'customerName' => $this->buildCustomerName($booking),
            'packageTypeCode' => $packageTypeCode,
            'dpPercentage' => $this->getDpPercentage($packageTypeCode),
            'dpDueDays' => $this->getDpDueDays(),
            'locationChain' => $this->buildLocationChain($booking),
            'locationPricingInfo' => $this->buildLocationPricingInfo($booking),
            'googleMapsUrl' => $this->buildGoogleMapsUrl($booking),
            'packageBenefits' => $booking->package?->benefits ?? collect(),
            'timeline' => $this->buildTimeline($booking),
            'billing' => $billingPayload,
            'billingTools' => $this->buildBillingTools($booking, $statusCode, $billingPayload),
            'stats' => $this->buildStats($booking, $statusCode),
            'availableActions' => $this->resolveAvailableActions($statusCode, $booking),
        ];
    }

    public function approveBooking(int $bookingId, int $operatorId): Booking
    {
        $booking = $this->bookingRepository->findOrFail($bookingId);
        $approvedRef = $this->findReference('booking_status', 'BS_APPROVED_WAITING_DP');

        $this->bookingRepository->update($booking, [
            'status_id' => $approvedRef->id,
            'operator_id' => $operatorId,
        ]);

        $this->bookingHistoryRepository->create([
            'uuid' => Str::uuid()->toString(),
            'booking_id' => $booking->id,
            'status_id' => $approvedRef->id,
            'operator_id' => $operatorId,
        ]);

        $this->createBillingOnApproval($booking);

        return $booking->fresh([
            'customer', 'package.packageType', 'package.benefits', 'status',
            'eventSession', 'location', 'operator',
            'billings.details.billingType', 'billings.installments.installmentType',
            'billings.installments.payments.status', 'billings.status',
            'history.status', 'history.operator',
        ]);
    }

    public function rejectBooking(int $bookingId, int $operatorId): Booking
    {
        $booking = $this->bookingRepository->findOrFail($bookingId);
        $rejectedRef = $this->findReference('booking_status', 'BS_REJECTED');

        $this->bookingRepository->update($booking, [
            'status_id' => $rejectedRef->id,
            'operator_id' => $operatorId,
        ]);

        $this->bookingHistoryRepository->create([
            'uuid' => Str::uuid()->toString(),
            'booking_id' => $booking->id,
            'status_id' => $rejectedRef->id,
            'operator_id' => $operatorId,
        ]);

        return $booking->fresh([
            'customer', 'package.packageType', 'package.benefits', 'status',
            'eventSession', 'location', 'operator', 'history.status', 'history.operator',
        ]);
    }

    public function addBillingDetail(int $bookingId, int $operatorId, array $payload): Booking
    {
        $booking = $this->bookingRepository->findOrFail($bookingId, ['*'], $this->detailRelations());
        $billing = $this->resolveEditableBillingOrFail($booking);

        $billingTypeCode = 'BLT_ADDON';

        $billingTypeRef = $this->findReference('billing_type', $billingTypeCode);

        $name = trim((string) ($payload['name'] ?? ''));
        $description = trim((string) ($payload['description'] ?? ''));
        $amount = round((float) ($payload['amount'] ?? 0), 2);

        if ($name === '') {
            throw new RuntimeException('Nama komponen billing wajib diisi.');
        }

        if ($amount <= 0) {
            throw new RuntimeException('Nominal komponen billing harus lebih dari 0.');
        }

        $this->billingDetailRepository->create([
            'uuid' => Str::uuid()->toString(),
            'billing_id' => $billing->id,
            'billing_type' => $billingTypeRef->id,
            'name' => $name,
            'description' => $description !== '' ? $description : null,
            'amount' => $amount,
            'created_by' => $operatorId,
        ]);

        $billing = $this->recalculateBillingAmounts($billing);
        $this->syncBillingStatus($billing);

        return $booking->fresh($this->detailRelations());
    }

    public function generateInstallment(int $bookingId, int $operatorId, array $payload): Booking
    {
        $booking = $this->bookingRepository->findOrFail($bookingId, ['*'], $this->detailRelations());
        $billing = $this->resolveEditableBillingOrFail($booking);

        $installmentTypeCode = strtoupper(trim((string) ($payload['installment_type_code'] ?? 'INS_PARTIAL')));
        if (!in_array($installmentTypeCode, ['INS_DP', 'INS_PARTIAL', 'INS_FINAL'], true)) {
            throw new RuntimeException('Tipe tagihan tidak valid.');
        }

        $amount = round((float) ($payload['amount'] ?? 0), 2);
        if ($amount <= 0) {
            throw new RuntimeException('Nominal tagihan harus lebih dari 0.');
        }

        $dueDate = trim((string) ($payload['due_date'] ?? ''));
        if ($dueDate === '') {
            throw new RuntimeException('Tanggal jatuh tempo wajib diisi.');
        }
        try {
            $dueDateCarbon = Carbon::parse($dueDate)->startOfDay();
        } catch (\Throwable) {
            throw new RuntimeException('Format tanggal jatuh tempo tidak valid.');
        }

        $scheduledAmount = (float) $this->billingInstallmentRepository
            ->query(true)
            ->where('billing_id', $billing->id)
            ->sum('amount');

        $totalAmount = (float) ($billing->total_amount ?? 0);
        $unallocatedAmount = max($totalAmount - $scheduledAmount, 0);

        if ($unallocatedAmount <= 0) {
            throw new RuntimeException('Semua nominal billing sudah dijadwalkan ke installment. Tambah komponen billing dulu jika butuh tagihan baru.');
        }

        if ($amount > $unallocatedAmount) {
            throw new RuntimeException(
                'Nominal tagihan melebihi sisa yang belum dijadwalkan: ' . $this->formatRupiah($unallocatedAmount) . '.'
            );
        }

        $installmentTypeRef = $this->findReference('intallment_type', $installmentTypeCode);
        $unpaidRef = $this->findReference('billing_status', 'BLS_UNPAID');

        $this->billingInstallmentRepository->create([
            'uuid' => Str::uuid()->toString(),
            'billing_id' => $billing->id,
            'installment_type' => $installmentTypeRef->id,
            'status_id' => $unpaidRef->id,
            'amount' => $amount,
            'due_date' => $dueDateCarbon->toDateString(),
            'paid_amount' => 0,
            'created_by' => $operatorId,
        ]);

        $this->syncBillingStatus($billing);

        return $booking->fresh($this->detailRelations());
    }

    public function uploadManualPayment(int $bookingId, int $operatorId, array $payload): Booking
    {
        $booking = $this->bookingRepository->findOrFail($bookingId, ['*'], $this->detailRelations());
        $statusCode = strtoupper(trim((string) ($booking->status?->code ?? '')));

        if (!in_array($statusCode, ['BS_APPROVED_WAITING_DP', 'BS_APPROVED_WAITING_FINAL_PAYMENT'], true)) {
            throw new RuntimeException('Upload pembayaran hanya tersedia untuk booking yang menunggu pembayaran.');
        }

        $billing = $this->resolveEditableBillingOrFail($booking);
        $installmentId = (int) ($payload['billing_installment_id'] ?? 0);
        $installment = $this->billingInstallmentRepository->findOrFail($installmentId);
        $installment->loadMissing(['installmentType:id,code,description']);

        if ((int) $installment->billing_id !== (int) $billing->id) {
            throw new RuntimeException('Installment tidak milik billing booking ini.');
        }

        $remainingInstallment = max((float) $installment->amount - (float) $installment->paid_amount, 0);
        if ($remainingInstallment <= 0) {
            throw new RuntimeException('Installment ini sudah lunas.');
        }

        $isPartial = !empty($payload['is_partial']);
        $amount = $remainingInstallment;

        if ($isPartial) {
            $amount = round((float) ($payload['amount'] ?? 0), 2);
            if ($amount <= 0) {
                throw new RuntimeException('Nominal pembayaran harus lebih dari 0.');
            }
        }

        if ($amount > $remainingInstallment) {
            throw new RuntimeException('Nominal melebihi sisa installment: ' . $this->formatRupiah($remainingInstallment) . '.');
        }

        $paymentMethodRef = $this->findReference('payment_method', $payload['payment_method_code'] ?? 'PYM_BANK_TRANSFER');
        $paymentTypeCode = $statusCode === 'BS_APPROVED_WAITING_DP' ? 'PYT_DP' : 'PYT_FINAL';
        $paymentTypeRef = $this->findReference('payment_type', $paymentTypeCode);
        $successRef = $this->findReference('payment_status', 'PYS_SUCCESS');

        $paymentData = [
            'uuid' => Str::uuid()->toString(),
            'billing_installment_id' => $installment->id,
            'payment_type' => $paymentTypeRef->id,
            'status_id' => $successRef->id,
            'payment_method' => $paymentMethodRef->id,
            'amount' => $amount,
            'paid_at' => now()->toDateTimeString(),
            'created_by' => $operatorId,
        ];

        $transferReceipt = $payload['transfer_receipt'] ?? null;
        if ($transferReceipt instanceof UploadedFile) {
            $attachmentId = $this->storeTransferReceiptAttachment($transferReceipt);
            $paymentData['transfer_receipt_attachment_id'] = $attachmentId;
        }

        $this->paymentRepository->create($paymentData);

        $newPaidAmount = min((float) $installment->paid_amount + $amount, (float) $installment->amount);
        $this->billingInstallmentRepository->update($installment, [
            'paid_amount' => $newPaidAmount,
        ]);

        $this->syncInstallmentStatus($installment, $newPaidAmount);
        $totalPaid = $this->recalculateBillingTotalPaid($billing);
        $this->billingRepository->update($billing, ['total_paid' => $totalPaid]);
        $billing->total_paid = $totalPaid;
        $this->syncBillingStatus($billing);

        $this->autoTransitionBookingStatus($booking, $statusCode, $billing);

        return $booking->fresh($this->detailRelations());
    }

    public function verifyDp(int $bookingId, int $operatorId): Booking
    {
        $booking = $this->bookingRepository->findOrFail($bookingId, ['*'], $this->detailRelations());
        $statusCode = strtoupper(trim((string) ($booking->status?->code ?? '')));

        if ($statusCode !== 'BS_APPROVED_WAITING_DP') {
            throw new RuntimeException('Verifikasi DP hanya untuk booking dengan status Approved - Waiting DP.');
        }

        $billing = $booking->billings->sortByDesc('id')->first();
        if (!$billing) {
            throw new RuntimeException('Billing tidak ditemukan.');
        }

        $billing->loadMissing([
            'installments.installmentType:id,code,description',
            'installments.status:id,code,description',
            'installments.payments.status:id,code,description',
        ]);

        $dpInstallment = $billing->installments->first(function ($inst) {
            return strtoupper(trim((string) ($inst->installmentType?->code ?? ''))) === 'INS_DP';
        });

        if (!$dpInstallment) {
            throw new RuntimeException('Installment DP tidak ditemukan.');
        }

        $dpPaid = $this->calculateTotalPayments($dpInstallment);
        $dpAmount = (float) $dpInstallment->amount;

        if ($dpPaid < $dpAmount) {
            throw new RuntimeException('DP belum terbayar penuh. Terbayar: ' . $this->formatRupiah($dpPaid) . ' dari ' . $this->formatRupiah($dpAmount) . '.');
        }

        $this->approvePendingPayments($dpInstallment);

        $effectivePaid = min($dpPaid, $dpAmount);
        $this->billingInstallmentRepository->update($dpInstallment, [
            'paid_amount' => $effectivePaid,
        ]);
        $this->syncInstallmentStatus($dpInstallment, $effectivePaid);

        $totalPaid = $this->recalculateBillingTotalPaid($billing);
        $this->billingRepository->update($billing, ['total_paid' => $totalPaid]);
        $billing->total_paid = $totalPaid;
        $this->syncBillingStatus($billing);

        $finalRef = $this->findReference('booking_status', 'BS_APPROVED_WAITING_FINAL_PAYMENT');
        $this->bookingRepository->update($booking, [
            'status_id' => $finalRef->id,
            'operator_id' => $operatorId,
        ]);

        $this->bookingHistoryRepository->create([
            'uuid' => Str::uuid()->toString(),
            'booking_id' => $booking->id,
            'status_id' => $finalRef->id,
            'operator_id' => $operatorId,
            'description' => 'DP verified - menunggu pelunasan',
        ]);

        return $booking->fresh($this->detailRelations());
    }

    public function rejectManual(int $bookingId, int $operatorId, string $reason = ''): Booking
    {
        $booking = $this->bookingRepository->findOrFail($bookingId, ['*'], $this->detailRelations());
        $statusCode = strtoupper(trim((string) ($booking->status?->code ?? '')));

        if (!in_array($statusCode, ['BS_APPROVED_WAITING_DP'], true)) {
            throw new RuntimeException('Reject manual hanya tersedia untuk booking yang menunggu DP.');
        }

        $rejectedRef = $this->findReference('booking_status', 'BS_REJECTED');

        $this->bookingRepository->update($booking, [
            'status_id' => $rejectedRef->id,
            'operator_id' => $operatorId,
        ]);

        $historyData = [
            'uuid' => Str::uuid()->toString(),
            'booking_id' => $booking->id,
            'status_id' => $rejectedRef->id,
            'operator_id' => $operatorId,
        ];
        if ($reason !== '') {
            $historyData['description'] = $reason;
        }

        $this->bookingHistoryRepository->create($historyData);

        $billing = $booking->billings->sortByDesc('id')->first();
        if ($billing) {
            $cancelledRef = $this->findReference('billing_status', 'BLS_CANCELLED');
            $this->billingRepository->update($billing, ['status_id' => $cancelledRef->id]);
        }

        return $booking->fresh($this->detailRelations());
    }

    public function verifyFinalPayment(int $bookingId, int $operatorId): Booking
    {
        $booking = $this->bookingRepository->findOrFail($bookingId, ['*'], $this->detailRelations());
        $statusCode = strtoupper(trim((string) ($booking->status?->code ?? '')));

        if ($statusCode !== 'BS_APPROVED_WAITING_FINAL_PAYMENT') {
            throw new RuntimeException('Verifikasi pelunasan hanya untuk booking dengan status Waiting Final Payment.');
        }

        $billing = $booking->billings->sortByDesc('id')->first();
        if (!$billing) {
            throw new RuntimeException('Billing tidak ditemukan.');
        }

        $billing->loadMissing([
            'installments.installmentType:id,code,description',
            'installments.payments.status:id,code,description',
        ]);

        $totalBilling = round((float) $billing->total_amount, 2);
        $totalSuccessful = 0;
        $totalScheduled = 0;

        foreach ($billing->installments as $inst) {
            $paid = round($this->calculateTotalPayments($inst), 2);
            $totalSuccessful += $paid;
            $effectivePaid = min($paid, (float) $inst->amount);
            $this->approvePendingPayments($inst);
            $this->billingInstallmentRepository->update($inst, [
                'paid_amount' => $effectivePaid,
            ]);
            $this->syncInstallmentStatus($inst, $effectivePaid);
            $totalScheduled += round((float) $inst->amount, 2);
        }

        $totalSuccessful = round($totalSuccessful, 2);
        $totalScheduled = round($totalScheduled, 2);

        if ($totalScheduled <= 0 || $totalSuccessful < $totalScheduled - 0.01) {
            throw new RuntimeException('Pembayaran belum lunas. Total tagihan terjadwal: ' . $this->formatRupiah($totalScheduled) . ', terbayar: ' . $this->formatRupiah($totalSuccessful) . '.');
        }

        $this->billingRepository->update($billing, ['total_paid' => $totalSuccessful]);
        $billing->total_paid = $totalSuccessful;
        $this->syncBillingStatus($billing);

        $confirmedRef = $this->findReference('booking_status', 'BS_CONFIRMED');
        $this->bookingRepository->update($booking, [
            'status_id' => $confirmedRef->id,
            'operator_id' => $operatorId,
        ]);

        $this->bookingHistoryRepository->create([
            'uuid' => Str::uuid()->toString(),
            'booking_id' => $booking->id,
            'status_id' => $confirmedRef->id,
            'operator_id' => $operatorId,
            'description' => 'Pelunasan terverifikasi - booking confirmed',
        ]);

        return $booking->fresh($this->detailRelations());
    }

    public function cancelBooking(int $bookingId, int $operatorId, string $reason = ''): Booking
    {
        $booking = $this->bookingRepository->findOrFail($bookingId, ['*'], $this->detailRelations());
        $statusCode = strtoupper(trim((string) ($booking->status?->code ?? '')));

        if (!in_array($statusCode, ['BS_APPROVED_WAITING_FINAL_PAYMENT'], true)) {
            throw new RuntimeException('Pembatalan booking hanya untuk status Waiting Final Payment.');
        }

        $cancelRef = $this->findReference('booking_status', 'BS_CANCEL');

        $this->bookingRepository->update($booking, [
            'status_id' => $cancelRef->id,
            'operator_id' => $operatorId,
        ]);

        $historyData = [
            'uuid' => Str::uuid()->toString(),
            'booking_id' => $booking->id,
            'status_id' => $cancelRef->id,
            'operator_id' => $operatorId,
        ];
        if ($reason !== '') {
            $historyData['description'] = $reason;
        }
        $this->bookingHistoryRepository->create($historyData);

        $billing = $booking->billings->sortByDesc('id')->first();
        if ($billing) {
            $cancelledRef = $this->findReference('billing_status', 'BLS_CANCELLED');
            $this->billingRepository->update($billing, ['status_id' => $cancelledRef->id]);
        }

        return $booking->fresh($this->detailRelations());
    }

    public function completeBooking(int $bookingId, int $operatorId): Booking
    {
        $booking = $this->bookingRepository->findOrFail($bookingId, ['*'], $this->detailRelations());
        $statusCode = strtoupper(trim((string) ($booking->status?->code ?? '')));

        if ($statusCode !== 'BS_CONFIRMED') {
            throw new RuntimeException('Penyelesaian booking hanya untuk status Confirmed.');
        }

        $completeRef = $this->findReference('booking_status', 'BS_COMPLETE');

        $this->bookingRepository->update($booking, [
            'status_id' => $completeRef->id,
            'operator_id' => $operatorId,
        ]);

        $this->bookingHistoryRepository->create([
            'uuid' => Str::uuid()->toString(),
            'booking_id' => $booking->id,
            'status_id' => $completeRef->id,
            'operator_id' => $operatorId,
            'description' => 'Booking selesai - acara telah dilaksanakan',
        ]);

        return $booking->fresh($this->detailRelations());
    }

    public function approvePendingPayment(int $bookingId, int $paymentId, int $operatorId): Booking
    {
        $booking = $this->bookingRepository->findOrFail($bookingId, ['*'], $this->detailRelations());
        $statusCode = strtoupper(trim((string) ($booking->status?->code ?? '')));

        if (!in_array($statusCode, ['BS_APPROVED_WAITING_DP', 'BS_APPROVED_WAITING_FINAL_PAYMENT'], true)) {
            throw new RuntimeException('Approval pembayaran hanya untuk booking yang menunggu pembayaran.');
        }

        $payment = $this->paymentRepository->findOrFail($paymentId);
        $paymentStatusCode = strtoupper(trim((string) ($payment->status?->code ?? '')));

        if ($paymentStatusCode !== 'PYS_PEDING') {
            throw new RuntimeException('Pembayaran ini tidak dalam status pending.');
        }

        $successRef = $this->findReference('payment_status', 'PYS_SUCCESS');
        $this->paymentRepository->update($payment, [
            'status_id' => $successRef->id,
            'created_by' => $operatorId,
        ]);

        $installment = $this->billingInstallmentRepository->findOrFail($payment->billing_installment_id);
        $this->syncInstallmentAndBillingAfterPaymentChange($installment);

        $this->bookingHistoryRepository->create([
            'uuid' => Str::uuid()->toString(),
            'booking_id' => $booking->id,
            'status_id' => $booking->status_id,
            'operator_id' => $operatorId,
            'description' => 'Bukti pembayaran di-approve (Payment #' . $paymentId . ')',
        ]);

        return $booking->fresh($this->detailRelations());
    }

    public function rejectPendingPayment(int $bookingId, int $paymentId, int $operatorId, string $reason = ''): Booking
    {
        $booking = $this->bookingRepository->findOrFail($bookingId, ['*'], $this->detailRelations());
        $statusCode = strtoupper(trim((string) ($booking->status?->code ?? '')));

        if (!in_array($statusCode, ['BS_APPROVED_WAITING_DP', 'BS_APPROVED_WAITING_FINAL_PAYMENT'], true)) {
            throw new RuntimeException('Reject pembayaran hanya untuk booking yang menunggu pembayaran.');
        }

        $payment = $this->paymentRepository->findOrFail($paymentId);
        $paymentStatusCode = strtoupper(trim((string) ($payment->status?->code ?? '')));

        if ($paymentStatusCode !== 'PYS_PEDING') {
            throw new RuntimeException('Pembayaran ini tidak dalam status pending.');
        }

        $failedRef = $this->findReference('payment_status', 'PYS_FAILED');
        $this->paymentRepository->update($payment, [
            'status_id' => $failedRef->id,
            'created_by' => $operatorId,
            'rejection_reason' => $reason !== '' ? $reason : null,
        ]);

        $description = 'Bukti pembayaran ditolak (Payment #' . $paymentId . ')';
        if ($reason !== '') {
            $description .= '. Alasan: ' . $reason;
        }

        $this->bookingHistoryRepository->create([
            'uuid' => Str::uuid()->toString(),
            'booking_id' => $booking->id,
            'status_id' => $booking->status_id,
            'operator_id' => $operatorId,
            'description' => $description,
        ]);

        return $booking->fresh($this->detailRelations());
    }

    private function syncInstallmentAndBillingAfterPaymentChange($installment): void
    {
        $successRef = $this->findReference('payment_status', 'PYS_SUCCESS');

        $totalSuccessful = (float) $this->paymentRepository
            ->query(true)
            ->where('billing_installment_id', $installment->id)
            ->where('status_id', $successRef->id)
            ->sum('amount');

        $effectivePaid = min($totalSuccessful, (float) $installment->amount);
        $this->billingInstallmentRepository->update($installment, [
            'paid_amount' => $effectivePaid,
        ]);
        $this->syncInstallmentStatus($installment, $effectivePaid);

        $billing = $this->billingRepository->query(true)
            ->whereHas('installments', function (Builder $q) use ($installment): void {
                $q->where('id', $installment->id);
            })->first();

        if ($billing) {
            $totalPaid = $this->recalculateBillingTotalPaid($billing);
            $this->billingRepository->update($billing, ['total_paid' => $totalPaid]);
            $billing->total_paid = $totalPaid;
            $this->syncBillingStatus($billing);
        }
    }

    public function forceMajeureReschedule(int $bookingId, int $operatorId, string $reason, string $newDate): Booking
    {
        $booking = $this->bookingRepository->findOrFail($bookingId, ['*'], $this->detailRelations());
        $statusCode = strtoupper(trim((string) ($booking->status?->code ?? '')));

        if ($statusCode !== 'BS_CONFIRMED') {
            throw new RuntimeException('Force majeure hanya untuk status Confirmed.');
        }

        if ($reason === '') {
            throw new RuntimeException('Alasan force majeure wajib diisi.');
        }

        try {
            $parsedDate = Carbon::parse($newDate)->toDateString();
        } catch (\Throwable) {
            throw new RuntimeException('Format tanggal tidak valid.');
        }

        $fmRef = $this->findReference('booking_status', 'BS_FORCE_MAJEURE');

        $this->bookingRepository->update($booking, [
            'status_id' => $fmRef->id,
            'force_majeure_date' => $parsedDate,
            'force_majeure_reason' => $reason,
            'reschedule_date' => $parsedDate,
            'reschedule_reason' => 'Force Majeure: ' . $reason,
            'operator_id' => $operatorId,
        ]);

        $this->bookingHistoryRepository->create([
            'uuid' => Str::uuid()->toString(),
            'booking_id' => $booking->id,
            'status_id' => $fmRef->id,
            'operator_id' => $operatorId,
            'description' => 'Force Majeure - Reschedule ke ' . $parsedDate . ': ' . $reason,
        ]);

        return $booking->fresh($this->detailRelations());
    }

    public function forceMajeureRefund(int $bookingId, int $operatorId, string $reason): Booking
    {
        $booking = $this->bookingRepository->findOrFail($bookingId, ['*'], $this->detailRelations());
        $statusCode = strtoupper(trim((string) ($booking->status?->code ?? '')));

        if ($statusCode !== 'BS_CONFIRMED') {
            throw new RuntimeException('Force majeure hanya untuk status Confirmed.');
        }

        if ($reason === '') {
            throw new RuntimeException('Alasan force majeure wajib diisi.');
        }

        $fmRef = $this->findReference('booking_status', 'BS_FORCE_MAJEURE');

        $this->bookingRepository->update($booking, [
            'status_id' => $fmRef->id,
            'force_majeure_date' => null,
            'force_majeure_reason' => $reason,
            'operator_id' => $operatorId,
        ]);

        $this->bookingHistoryRepository->create([
            'uuid' => Str::uuid()->toString(),
            'booking_id' => $booking->id,
            'status_id' => $fmRef->id,
            'operator_id' => $operatorId,
            'description' => 'Force Majeure - Akan diproses refund: ' . $reason,
        ]);

        $billing = $booking->billings->sortByDesc('id')->first();
        if ($billing) {
            $billing->loadMissing(['installments.installmentType:id,code,description']);
            $billingRefundRef = $this->findReference('billing_status', 'BLS_REFUND');
            $this->billingRepository->update($billing, ['status_id' => $billingRefundRef->id]);

            $refundInstallmentRef = $this->findReference('intallment_type', 'INS_REFUND');
            $unpaidRef = $this->findReference('billing_status', 'BLS_UNPAID');
            $totalPaid = (float) ($billing->total_paid ?? 0);

            if ($totalPaid > 0) {
                $this->billingInstallmentRepository->create([
                    'uuid' => Str::uuid()->toString(),
                    'billing_id' => $billing->id,
                    'installment_type' => $refundInstallmentRef->id,
                    'status_id' => $unpaidRef->id,
                    'amount' => $totalPaid,
                    'due_date' => now()->addDays(7)->toDateString(),
                    'paid_amount' => 0,
                ]);
            }
        }

        return $booking->fresh($this->detailRelations());
    }

    public function uploadRefundProof(int $bookingId, int $operatorId, array $payload): Booking
    {
        $booking = $this->bookingRepository->findOrFail($bookingId, ['*'], $this->detailRelations());
        $statusCode = strtoupper(trim((string) ($booking->status?->code ?? '')));

        if (!in_array($statusCode, ['BS_FORCE_MAJEURE', 'BS_REFUND'], true)) {
            throw new RuntimeException('Upload bukti refund hanya untuk status Force Majeure (refund) atau Refund.');
        }

        $billing = $booking->billings->sortByDesc('id')->first();
        if (!$billing) {
            throw new RuntimeException('Billing tidak ditemukan.');
        }

        $billing->loadMissing([
            'installments.installmentType:id,code,description',
            'installments.status:id,code,description',
        ]);

        $refundInstallment = $billing->installments->first(function ($inst) {
            return strtoupper(trim((string) ($inst->installmentType?->code ?? ''))) === 'INS_REFUND';
        });

        if (!$refundInstallment) {
            throw new RuntimeException('Installment refund tidak ditemukan.');
        }

        $remainingRefund = max((float) $refundInstallment->amount - (float) $refundInstallment->paid_amount, 0);
        if ($remainingRefund <= 0) {
            throw new RuntimeException('Refund sudah lunas.');
        }

        $paymentMethodRef = $this->findReference('payment_method', $payload['payment_method_code'] ?? 'PYM_BANK_TRANSFER');
        $paymentTypeRef = $this->findReference('payment_type', 'PYT_REFUND');
        $successRef = $this->findReference('payment_status', 'PYS_SUCCESS');

        $paymentData = [
            'uuid' => Str::uuid()->toString(),
            'billing_installment_id' => $refundInstallment->id,
            'payment_type' => $paymentTypeRef->id,
            'status_id' => $successRef->id,
            'payment_method' => $paymentMethodRef->id,
            'amount' => $remainingRefund,
            'paid_at' => now()->toDateTimeString(),
            'created_by' => $operatorId,
        ];

        $transferReceipt = $payload['transfer_receipt'] ?? null;
        if ($transferReceipt instanceof UploadedFile) {
            $attachmentId = $this->storeTransferReceiptAttachment($transferReceipt);
            $paymentData['transfer_receipt_attachment_id'] = $attachmentId;
        }

        $this->paymentRepository->create($paymentData);

        $newPaidAmount = min((float) $refundInstallment->paid_amount + $remainingRefund, (float) $refundInstallment->amount);
        $this->billingInstallmentRepository->update($refundInstallment, [
            'paid_amount' => $newPaidAmount,
        ]);
        $this->syncInstallmentStatus($refundInstallment, $newPaidAmount);

        $newRefundedAmount = (float) ($billing->refunded_amount ?? 0) + $remainingRefund;
        $newTotalPaid = max((float) ($billing->total_paid ?? 0) - $remainingRefund, 0);
        $this->billingRepository->update($billing, [
            'refunded_amount' => $newRefundedAmount,
            'total_paid' => $newTotalPaid,
        ]);
        $billing->refunded_amount = $newRefundedAmount;
        $billing->total_paid = $newTotalPaid;
        $this->syncBillingStatus($billing);

        $refundRef = $this->findReference('booking_status', 'BS_REFUND');
        $this->bookingRepository->update($booking, [
            'status_id' => $refundRef->id,
            'operator_id' => $operatorId,
        ]);

        $this->bookingHistoryRepository->create([
            'uuid' => Str::uuid()->toString(),
            'booking_id' => $booking->id,
            'status_id' => $refundRef->id,
            'operator_id' => $operatorId,
            'description' => 'Bukti refund diupload. Refund selesai.',
        ]);

        return $booking->fresh($this->detailRelations());
    }

    private function createBillingOnApproval(Booking $booking): void
    {
        $package = $booking->package;
        $basePrice = (float) ($package->price ?? 0);
        $packageTypeCode = strtoupper(trim((string) ($package->packageType?->code ?? '')));
        $dpPercentage = $this->getDpPercentage($packageTypeCode);
        $dpDueDays = $this->getDpDueDays();

        $unpaidRef = $this->findReference('billing_status', 'BLS_UNPAID');
        $baseTypeRef = $this->findReference('billing_type', 'BLT_BASE');
        $dpTypeRef = $this->findReference('intallment_type', 'INS_DP');

        $billing = $this->billingRepository->create([
            'uuid' => Str::uuid()->toString(),
            'booking_id' => $booking->id,
            'total_amount' => $basePrice,
            'total_paid' => 0,
            'refunded_amount' => 0,
            'status_id' => $unpaidRef->id,
        ]);

        $this->billingDetailRepository->create([
            'uuid' => Str::uuid()->toString(),
            'billing_id' => $billing->id,
            'billing_type' => $baseTypeRef->id,
            'name' => 'Base Package: ' . ($package->name ?? '-'),
            'description' => $package->description,
            'amount' => $basePrice,
        ]);

        $dpAmount = round($basePrice * ($dpPercentage / 100), 2);
        $dueDate = Carbon::now()->addDays($dpDueDays)->toDateString();

        $this->billingInstallmentRepository->create([
            'uuid' => Str::uuid()->toString(),
            'billing_id' => $billing->id,
            'installment_type' => $dpTypeRef->id,
            'status_id' => $unpaidRef->id,
            'amount' => $dpAmount,
            'due_date' => $dueDate,
            'paid_amount' => 0,
        ]);
    }

    private function autoTransitionBookingStatus(Booking $booking, string $currentStatusCode, $billing): void
    {
        $billing->loadMissing([
            'installments.installmentType:id,code,description',
            'installments.status:id,code,description',
        ]);

        if ($currentStatusCode === 'BS_APPROVED_WAITING_DP') {
            $dpInstallment = $billing->installments->first(function ($inst) {
                return strtoupper(trim((string) ($inst->installmentType?->code ?? ''))) === 'INS_DP';
            });

            if ($dpInstallment && (float) $dpInstallment->paid_amount >= (float) $dpInstallment->amount) {
                $nextRef = $this->findReference('booking_status', 'BS_APPROVED_WAITING_FINAL_PAYMENT');
                $this->bookingRepository->update($booking, ['status_id' => $nextRef->id]);
                $this->bookingHistoryRepository->create([
                    'uuid' => Str::uuid()->toString(),
                    'booking_id' => $booking->id,
                    'status_id' => $nextRef->id,
                    'description' => 'DP lunas secara otomatis setelah pembayaran manual. Menunggu pelunasan.',
                ]);
            }
        }
    }

    /**
     * @return array<int, string>
     */
    private function detailRelations(): array
    {
        return [
            'customer:id,first_name,last_name,phone_number,email',
            'package:id,name,price,address,description,package_type,thumbnail_attachment_id',
            'package.packageType:id,code,description',
            'package.benefits:id,package_id,name,description',
            'package.thumbnailAttachment:id,name,path',
            'status:id,code,description',
            'eventSession:id,code,description',
            'location:id,name,wilayah_id,parent_id,level_id',
            'location.level:id,code,description',
            'location.parent',
            'location.parent.parent',
            'location.parent.parent.parent',
            'operator:id,name',
            'billings:id,booking_id,total_amount,total_paid,refunded_amount,status_id',
            'billings.status:id,code,description',
            'billings.details:id,billing_id,billing_type,name,description,amount',
            'billings.details.billingType:id,code,description',
            'billings.installments:id,billing_id,installment_type,status_id,amount,due_date,paid_amount',
            'billings.installments.installmentType:id,code,description',
            'billings.installments.status:id,code,description',
            'billings.installments.payments:id,billing_installment_id,payment_type,status_id,payment_method,amount,paid_at,transfer_receipt_attachment_id',
            'billings.installments.payments.paymentType:id,code,description',
            'billings.installments.payments.status:id,code,description',
            'billings.installments.payments.paymentMethod:id,code,description',
            'billings.installments.payments.transferReceiptAttachment:id,uuid,name,path',
            'history:id,booking_id,status_id,operator_id,created_at',
            'history.status:id,code,description',
            'history.operator:id,name',
        ];
    }

    private function resolveEditableBillingOrFail(Booking $booking): Billing
    {
        /** @var Billing|null $billing */
        $billing = $booking->billings->sortByDesc('id')->first();

        if (!$billing) {
            throw new RuntimeException('Billing belum tersedia. Approve booking terlebih dahulu.');
        }

        $billing->loadMissing([
            'status:id,code,description',
            'details:id,billing_id,billing_type,name,description,amount',
            'details.billingType:id,code,description',
            'installments:id,billing_id,installment_type,status_id,amount,due_date,paid_amount',
            'installments.installmentType:id,code,description',
            'installments.status:id,code,description',
        ]);

        $statusCode = strtoupper(trim((string) ($billing->status?->code ?? '')));
        if (in_array($statusCode, ['BLS_CANCELLED', 'BLS_REFUND'], true)) {
            throw new RuntimeException('Billing dengan status ini tidak dapat diubah.');
        }

        return $billing;
    }

    private function recalculateBillingAmounts(Billing $billing): Billing
    {
        $totalAmount = (float) $this->billingDetailRepository
            ->query(true)
            ->where('billing_id', $billing->id)
            ->sum('amount');

        return $this->billingRepository->update($billing, [
            'total_amount' => max($totalAmount, 0),
        ]);
    }

    private function syncBillingStatus(Billing $billing): Billing
    {
        $billing->loadMissing(['status:id,code']);

        $totalAmount = (float) ($billing->total_amount ?? 0);
        $totalPaid = (float) ($billing->total_paid ?? 0);

        $nextStatusCode = 'BLS_UNPAID';
        if ($totalAmount > 0 && $totalPaid > 0 && $totalPaid < $totalAmount) {
            $nextStatusCode = 'BLS_PARTIAL';
        }

        if ($totalAmount > 0 && $totalPaid >= $totalAmount) {
            $nextStatusCode = 'BLS_PAID';
        }

        $currentStatusCode = strtoupper(trim((string) ($billing->status?->code ?? '')));
        if ($currentStatusCode === $nextStatusCode) {
            return $billing;
        }

        $statusRef = $this->findReference('billing_status', $nextStatusCode);

        return $this->billingRepository->update($billing, [
            'status_id' => $statusRef->id,
        ]);
    }

    private function syncInstallmentStatus(\App\Models\BillingInstallment $installment, float $paidAmount): void
    {
        $installmentAmount = (float) $installment->amount;
        $nextStatusCode = 'BLS_UNPAID';
        if ($paidAmount > 0 && $paidAmount < $installmentAmount) {
            $nextStatusCode = 'BLS_PARTIAL';
        }
        if ($paidAmount >= $installmentAmount && $installmentAmount > 0) {
            $nextStatusCode = 'BLS_PAID';
        }

        $currentCode = strtoupper(trim((string) ($installment->status?->code ?? '')));
        if ($currentCode === $nextStatusCode) {
            return;
        }

        $statusRef = $this->findReference('billing_status', $nextStatusCode);
        $this->billingInstallmentRepository->update($installment, [
            'status_id' => $statusRef->id,
        ]);
    }

    private function calculateSuccessfulPayments($installment): float
    {
        $successRef = $this->referenceRepository->query(true)
            ->where('group_id', 'payment_status')
            ->where('code', 'PYS_SUCCESS')
            ->first();

        if (!$successRef) {
            return 0;
        }

        return (float) $this->paymentRepository
            ->query(true)
            ->where('billing_installment_id', $installment->id)
            ->where('status_id', $successRef->id)
            ->sum('amount');
    }

    private function calculateTotalPayments($installment): float
    {
        return (float) $this->paymentRepository
            ->query(true)
            ->where('billing_installment_id', $installment->id)
            ->sum('amount');
    }

    private function approvePendingPayments($installment): void
    {
        $successRef = $this->findReference('payment_status', 'PYS_SUCCESS');
        $pendingRef = $this->findReference('payment_status', 'PYS_PEDING');

        $this->paymentRepository
            ->query(true)
            ->where('billing_installment_id', $installment->id)
            ->where('status_id', $pendingRef->id)
            ->update(['status_id' => $successRef->id]);
    }

    private function recalculateBillingTotalPaid(Billing $billing): float
    {
        $billing->loadMissing([
            'installments.installmentType:id,code,description',
            'installments.payments.status:id,code,description',
        ]);

        $successRef = $this->referenceRepository->query(true)
            ->where('group_id', 'payment_status')
            ->where('code', 'PYS_SUCCESS')
            ->first();

        if (!$successRef) {
            return 0;
        }

        return (float) $this->paymentRepository
            ->query(true)
            ->whereIn('billing_installment_id', $billing->installments->pluck('id'))
            ->where('status_id', $successRef->id)
            ->sum('amount');
    }

    private function storeTransferReceiptAttachment(UploadedFile $file): int
    {
        $storagePayload = $this->attachmentSecurityService->storeEncryptedUploadedFile($file, 'transfer-receipts');
        $encryptedPath = trim((string) ($storagePayload['encrypted_path'] ?? ''));
        if ($encryptedPath === '') {
            throw new RuntimeException('Bukti transfer gagal disimpan.');
        }

        $mimeType = strtolower(trim((string) ($file->getMimeType() ?? '')));
        $typeCode = str_contains($mimeType, 'pdf') ? 'TF_DOC' : 'TF_IMG';
        $typeRef = $this->referenceRepository->query(true)
            ->where('group_id', 'type_file')
            ->where('code', $typeCode)
            ->firstOrFail();

        $attachment = $this->attachmentRepository->create([
            'uuid' => Str::uuid()->toString(),
            'name' => $file->getClientOriginalName() ?: 'transfer-receipt',
            'path' => $encryptedPath,
            'type_file' => $typeRef->id,
        ]);

        return (int) $attachment->getKey();
    }

    private function resolveBooking(string $caseId): ?Booking
    {
        $pattern = '/^ETH-(\d{8})-(\d{5})$/i';

        if (preg_match($pattern, strtoupper($caseId), $matches)) {
            $date = Carbon::createFromFormat('Ymd', $matches[1])->toDateString();
            $id = (int) ltrim($matches[2], '0');

            return $this->bookingRepository->query(true)
                ->with($this->detailRelations())
                ->whereDate('created_at', $date)
                ->where('id', $id)
                ->first();
        }

        if (ctype_digit($caseId)) {
            return $this->bookingRepository->findOrFail((int) $caseId, ['*'], $this->detailRelations());
        }

        return $this->bookingRepository->findBy('uuid', $caseId, ['*'], $this->detailRelations());
    }

    private function buildCaseId(Booking $booking): string
    {
        $storedCaseId = trim((string) ($booking->case_id ?? ''));
        if ($storedCaseId !== '') {
            return $storedCaseId;
        }

        $createdAt = $booking->created_at ?? now();
        return sprintf('ETH-%s-%05d', $createdAt->format('Ymd'), (int) $booking->getKey());
    }

    private function buildCustomerName(Booking $booking): string
    {
        $name = trim(implode(' ', array_filter([
            $booking->customer?->first_name,
            $booking->customer?->last_name,
        ])));
        return $name !== '' ? $name : '-';
    }

    private function buildLocationChain(Booking $booking): array
    {
        $chain = [];
        $location = $booking->location;

        if (!$location) {
            return $chain;
        }

        $chain[] = [
            'name' => $location->name,
            'level' => trim((string) ($location->level?->description ?? '')),
        ];

        $current = $location->parent;
        while ($current) {
            $chain[] = [
                'name' => $current->name,
                'level' => trim((string) ($current->level?->description ?? '')),
            ];
            $current = $current->parent;
        }

        return $chain;
    }

    private function buildLocationPricingInfo(Booking $booking): ?array
    {
        $location = $booking->location;
        if (!$location) {
            return null;
        }

        $rule = $this->locationPricingRuleRepository->query(true)
            ->with(['priceType:id,code,description'])
            ->where('location_id', $location->id)
            ->first();

        if (!$rule) {
            $current = $location->parent;
            while ($current) {
                $rule = $this->locationPricingRuleRepository->query(true)
                    ->with(['priceType:id,code,description'])
                    ->where('location_id', $current->id)
                    ->first();
                if ($rule) {
                    break;
                }
                $current = $current->parent;
            }
        }

        if (!$rule) {
            return ['found' => false, 'label' => 'Tidak ditemukan aturan harga untuk lokasi ini'];
        }

        return [
            'found' => true,
            'label' => trim((string) ($rule->priceType?->description ?? '-')),
            'code' => trim((string) ($rule->priceType?->code ?? '')),
        ];
    }

    private function buildGoogleMapsUrl(Booking $booking): string
    {
        $pin = trim((string) ($booking->google_maps_pin ?? ''));
        if ($pin === '') {
            return '';
        }

        if (filter_var($pin, FILTER_VALIDATE_URL)) {
            return $pin;
        }

        if (preg_match('/^(-?\d+\.?\d*),\s*(-?\d+\.?\d*)$/', $pin, $m)) {
            return "https://www.google.com/maps?q={$m[1]},{$m[2]}";
        }

        return "https://www.google.com/maps/search/" . urlencode($pin);
    }

    private function buildTimeline(Booking $booking): array
    {
        $history = $booking->history->sortBy('created_at');

        return $history->map(function ($item) {
            $code = strtoupper(trim((string) ($item->status?->code ?? '')));
            $historyDescription = trim((string) data_get($item, 'description', ''));
            $statusDescription = trim((string) ($item->status?->description ?? ''));

            return [
                'label' => $this->resolveStatusLabel($code),
                'description' => $historyDescription !== ''
                    ? $historyDescription
                    : ($statusDescription !== '' ? $statusDescription : $this->resolvePaymentHint($code)),
                'operator' => trim((string) ($item->operator?->name ?? '-')),
                'date' => $item->created_at?->format('Y-m-d H:i') ?? '-',
                'code' => $code,
            ];
        })->values()->all();
    }

    private function buildBillingPayload(Booking $booking): ?array
    {
        $billing = $booking->billings->sortByDesc('id')->first();
        if (!$billing) {
            return null;
        }

        $details = $billing->details
            ->sortBy([['billing_type', 'asc'], ['id', 'asc']])
            ->values();

        $installments = $billing->installments
            ->sortBy([['due_date', 'asc'], ['id', 'asc']])
            ->values();

        $totalAmount = (float) $billing->total_amount;
        $totalPaid = (float) $billing->total_paid;
        $totalDetailAmount = (float) $details->sum(fn($d) => (float) ($d->amount ?? 0));
        $totalInstallmentAmount = (float) $installments->sum(fn($i) => (float) ($i->amount ?? 0));
        $remainingAmount = max($totalAmount - $totalPaid, 0);
        $unallocatedAmount = max($totalAmount - $totalInstallmentAmount, 0);

        return [
            'id' => (int) $billing->id,
            'status_code' => strtoupper(trim((string) ($billing->status?->code ?? ''))),
            'status' => trim((string) ($billing->status?->description ?? '-')),
            'total_amount' => $totalAmount,
            'total_paid' => $totalPaid,
            'remaining_amount' => $remainingAmount,
            'total_detail_amount' => $totalDetailAmount,
            'total_installment_amount' => $totalInstallmentAmount,
            'unallocated_amount' => $unallocatedAmount,
            'details_count' => $details->count(),
            'installments_count' => $installments->count(),
            'details' => $details->map(fn($d) => [
                'id' => (int) $d->id,
                'name' => $d->name,
                'type_code' => strtoupper(trim((string) ($d->billingType?->code ?? ''))),
                'type' => trim((string) ($d->billingType?->description ?? '-')),
                'amount' => (float) $d->amount,
                'description' => trim((string) ($d->description ?? '')),
            ])->all(),
            'installments' => $installments->map(fn($i) => [
                'id' => (int) $i->id,
                'type_code' => strtoupper(trim((string) ($i->installmentType?->code ?? ''))),
                'type' => trim((string) ($i->installmentType?->description ?? '-')),
                'amount' => (float) $i->amount,
                'paid_amount' => (float) $i->paid_amount,
                'remaining_amount' => max((float) $i->amount - (float) $i->paid_amount, 0),
                'due_date' => $i->due_date?->format('Y-m-d') ?? '-',
                'status_code' => strtoupper(trim((string) ($i->status?->code ?? ''))),
                'status' => trim((string) ($i->status?->description ?? '-')),
                'payments' => $i->payments->map(function ($p) {
                    $hasReceipt = !empty($p->transfer_receipt_attachment_id) && $p->transferReceiptAttachment !== null;
                    $paymentStatusCode = strtoupper(trim((string) ($p->status?->code ?? '')));
                    return [
                        'id' => (int) $p->id,
                        'amount' => (float) $p->amount,
                        'method' => trim((string) ($p->paymentMethod?->description ?? '-')),
                        'status' => trim((string) ($p->status?->description ?? '-')),
                        'status_code' => $paymentStatusCode,
                        'is_pending' => $paymentStatusCode === 'PYS_PEDING',
                        'is_customer_upload' => empty($p->created_by),
                        'paid_at' => $p->paid_at?->format('Y-m-d H:i') ?? '-',
                        'has_receipt' => $hasReceipt,
                        'receipt_name' => $hasReceipt ? trim((string) ($p->transferReceiptAttachment->name ?? '')) : null,
                        'receipt_preview_url' => $hasReceipt ? $this->attachmentSecurityService->generateTemporaryPreviewUrl($p->transferReceiptAttachment) : null,
                    ];
                })->all(),
            ])->all(),
        ];
    }

    private function buildStats(Booking $booking, string $statusCode): array
    {
        $stats = [
            ['label' => 'Booking Status', 'value' => $this->resolveStatusLabel($statusCode), 'hint' => trim((string) ($booking->status?->description ?? '')), 'tone' => $this->resolveStatusBadge($statusCode)['tone']],
            ['label' => 'Payment', 'value' => $this->resolvePaymentBadge($statusCode)['label'], 'hint' => $this->resolvePaymentHint($statusCode), 'tone' => $this->resolvePaymentBadge($statusCode)['tone']],
            ['label' => 'Tanggal Acara', 'value' => $booking->event_date?->format('Y-m-d') ?? '-', 'hint' => trim((string) ($booking->eventSession?->description ?? '')), 'tone' => 'primary'],
            ['label' => 'Jenis Paket', 'value' => trim((string) ($booking->package?->packageType?->description ?? '-')), 'hint' => 'DP ' . $this->getDpPercentage(strtoupper(trim((string) ($booking->package?->packageType?->code ?? '')))) . '%', 'tone' => 'success'],
        ];

        return $stats;
    }

    private function buildBillingTools(Booking $booking, string $statusCode, ?array $billing): array
    {
        $canManage = $billing !== null && in_array($statusCode, [
            'BS_APPROVED_WAITING_DP',
            'BS_APPROVED_WAITING_FINAL_PAYMENT',
            'BS_CONFIRMED',
        ], true);

        $canAddDetail = $canManage;

        $dpVerified = !in_array($statusCode, ['BS_APPROVED_WAITING_DP'], true);
        $canGenerate = $canManage && $dpVerified && ((float) ($billing['unallocated_amount'] ?? 0) > 0);

        $defaultDueDate = now()->toDateString();
        if ($booking->event_date instanceof Carbon && in_array($statusCode, ['BS_APPROVED_WAITING_FINAL_PAYMENT', 'BS_CONFIRMED'], true)) {
            $defaultDueDate = $booking->event_date->copy()->subDay()->toDateString();
        }

        $installmentTypes = [
            ['code' => 'INS_PARTIAL', 'label' => 'Partial'],
            ['code' => 'INS_FINAL', 'label' => 'Final'],
        ];

        if ($statusCode === 'BS_APPROVED_WAITING_DP') {
            $installmentTypes = [];
        }

        $dpInstallments = collect($billing['installments'] ?? [])->filter(function ($i) {
            return strtoupper(trim((string) ($i['type_code'] ?? ''))) === 'INS_DP';
        });

        return [
            'can_manage' => $canManage,
            'can_add_detail' => $canAddDetail,
            'can_generate_installment' => $canGenerate,
            'dp_verified' => $dpVerified,
            'dp_installments' => $dpInstallments->values()->all(),
            'non_dp_installments' => collect($billing['installments'] ?? [])->filter(function ($i) {
                return strtoupper(trim((string) ($i['type_code'] ?? ''))) !== 'INS_DP';
            })->values()->all(),
            'default_due_date' => $defaultDueDate,
            'billing_types' => [
                ['code' => 'BLT_ADDON', 'label' => 'Add-on'],
            ],
            'installment_types' => $installmentTypes,
            'unallocated_amount' => (float) ($billing['unallocated_amount'] ?? 0),
            'unallocated_label' => $this->formatRupiah((float) ($billing['unallocated_amount'] ?? 0)),
        ];
    }

    private function resolveAvailableActions(string $statusCode, Booking $booking): array
    {
        if ($statusCode === 'BS_FORCE_MAJEURE') {
            $isReschedule = !empty($booking->force_majeure_date);
            return $isReschedule ? [] : ['upload_refund_proof'];
        }

        return match ($statusCode) {
            'BS_WAITING_APPROVAL' => ['approve', 'reject'],
            'BS_APPROVED_WAITING_DP' => ['upload_dp', 'verify_dp', 'reject_manual'],
            'BS_APPROVED_WAITING_FINAL_PAYMENT' => ['upload_final', 'verify_final', 'cancel_booking'],
            'BS_CONFIRMED' => ['complete_booking', 'force_majeure'],
            'BS_REFUND' => [],
            default => [],
        };
    }

    private function getDpPercentage(string $packageTypeCode): int
    {
        $settingCode = str_contains($packageTypeCode, 'WEDDING') ? 'PTPP_WED' : 'PTPP_NON_WED';
        $setting = $this->settingRepository->query(true)
            ->where('group_id', 'payment_type_price_percentage')
            ->where('code', $settingCode)
            ->first();

        return (int) ($setting?->value ?? 15);
    }

    private function getDpDueDays(): int
    {
        $setting = $this->settingRepository->query(true)
            ->where('group_id', 'paymet_date_rule')
            ->where('code', 'PDR_DP')
            ->first();

        $rawValue = trim((string) ($setting?->value ?? '3'));
        $parsed = preg_replace('/[^0-9]/', '', $rawValue);
        if (!is_string($parsed) || $parsed === '') {
            return 3;
        }

        $days = (int) $parsed;
        return $days > 0 ? $days : 3;
    }

    private function findReference(string $groupId, string $code)
    {
        return $this->referenceRepository->query(true)
            ->where('group_id', $groupId)
            ->where('code', $code)
            ->firstOrFail();
    }

    private function resolveStatusLabel(string $statusCode): string
    {
        $map = [
            'BS_WAITING_APPROVAL' => 'Waiting Approval',
            'BS_APPROVED_WAITING_DP' => 'Approved - Waiting DP',
            'BS_APPROVED_WAITING_FINAL_PAYMENT' => 'Waiting Final Payment',
            'BS_CONFIRMED' => 'Confirmed',
            'BS_COMPLETE' => 'Complete',
            'BS_CANCEL' => 'Cancelled',
            'BS_EXPIRED' => 'Expired',
            'BS_EXPIRED_DP' => 'Expired DP',
            'BS_RESCHEDULE' => 'Reschedule',
            'BS_FORCE_MAJEURE' => 'Force Majeure',
            'BS_REFUND' => 'Refund',
            'BS_REJECTED' => 'Rejected',
        ];

        return $map[$statusCode] ?? ucwords(strtolower(str_replace(['BS_', '_'], ['', ' '], $statusCode)));
    }

    private function resolveStatusBadge(string $statusCode): array
    {
        $tone = match ($statusCode) {
            'BS_WAITING_APPROVAL' => 'warning',
            'BS_APPROVED_WAITING_DP' => 'info',
            'BS_APPROVED_WAITING_FINAL_PAYMENT' => 'info',
            'BS_CONFIRMED', 'BS_COMPLETE' => 'success',
            'BS_CANCEL', 'BS_EXPIRED', 'BS_EXPIRED_DP', 'BS_REFUND', 'BS_REJECTED' => 'danger',
            'BS_FORCE_MAJEURE', 'BS_RESCHEDULE' => 'warning',
            default => 'light',
        };

        return ['type' => 'badge', 'tone' => $tone, 'label' => $this->resolveStatusLabel($statusCode)];
    }

    private function resolvePaymentBadge(string $statusCode): array
    {
        return match ($statusCode) {
            'BS_WAITING_APPROVAL' => ['type' => 'badge', 'tone' => 'warning', 'label' => 'Belum Ada Tagihan'],
            'BS_APPROVED_WAITING_DP' => ['type' => 'badge', 'tone' => 'info', 'label' => 'Menunggu DP'],
            'BS_APPROVED_WAITING_FINAL_PAYMENT' => ['type' => 'badge', 'tone' => 'info', 'label' => 'Menunggu Pelunasan'],
            'BS_CONFIRMED', 'BS_COMPLETE' => ['type' => 'badge', 'tone' => 'success', 'label' => 'Lunas'],
            'BS_CANCEL', 'BS_EXPIRED', 'BS_EXPIRED_DP', 'BS_REJECTED' => ['type' => 'badge', 'tone' => 'danger', 'label' => 'Tidak Aktif'],
            default => ['type' => 'badge', 'tone' => 'light', 'label' => '-'],
        };
    }

    private function resolvePaymentHint(string $statusCode): string
    {
        return match ($statusCode) {
            'BS_WAITING_APPROVAL' => 'Booking belum di-approve',
            'BS_APPROVED_WAITING_DP' => 'DP belum dibayar',
            'BS_APPROVED_WAITING_FINAL_PAYMENT' => 'DP sudah verified, menunggu pelunasan',
            'BS_CONFIRMED' => 'Pembayaran lunas',
            'BS_COMPLETE' => 'Acara selesai',
            default => '-',
        };
    }

    private function formatRupiah(float $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}
