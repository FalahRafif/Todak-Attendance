<?php

namespace App\Http\Requests\Public\Booking;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'booking_name' => ['required', 'string', 'max:255'],
            'booking_whatsapp' => ['required', 'string', 'max:30'],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'booking_package_type' => [
                'required',
                'integer',
                Rule::exists('references', 'id')->where(function ($query): void {
                    $query
                        ->where('group_id', 'package_type')
                        ->where('delete_status', false);
                }),
            ],
            'booking_session' => [
                'required',
                'integer',
                Rule::exists('references', 'id')->where(function ($query): void {
                    $query
                        ->where('group_id', 'event_session')
                        ->where('delete_status', false);
                }),
            ],
            'booking_package' => [
                'required',
                'integer',
                Rule::exists('packages', 'id')->where(function ($query): void {
                    $query->where('delete_status', false);
                }),
            ],
            'booking_location' => [
                'required',
                'integer',
                Rule::exists('locations', 'id')->where(function ($query): void {
                    $query->where('delete_status', false);
                }),
            ],
            'booking_location_province' => [
                'required',
                'integer',
                Rule::exists('locations', 'id')->where(function ($query): void {
                    $query->where('delete_status', false);
                }),
            ],
            'booking_location_city' => [
                'required',
                'integer',
                Rule::exists('locations', 'id')->where(function ($query): void {
                    $query->where('delete_status', false);
                }),
            ],
            'booking_location_district' => [
                'required',
                'integer',
                Rule::exists('locations', 'id')->where(function ($query): void {
                    $query->where('delete_status', false);
                }),
            ],
            'booking_location_village' => [
                'required',
                'integer',
                Rule::exists('locations', 'id')->where(function ($query): void {
                    $query->where('delete_status', false);
                }),
            ],
            'booking_pin_address' => ['required', 'string', 'max:500'],
            'booking_pin_lat' => ['required', 'numeric', 'between:-90,90'],
            'booking_pin_lng' => ['required', 'numeric', 'between:-180,180'],
            'booking_detail' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'booking_date.after_or_equal' => 'Tanggal acara minimal hari ini atau setelahnya.',
            'booking_pin_lat.required' => 'Silakan pilih pin lokasi pada peta.',
            'booking_pin_lng.required' => 'Silakan pilih pin lokasi pada peta.',
            'booking_pin_lat.between' => 'Latitude harus berada di rentang -90 sampai 90.',
            'booking_pin_lng.between' => 'Longitude harus berada di rentang -180 sampai 180.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        $pinAddress = trim((string) $this->input('booking_pin_address'));
        $pinLatitude = trim((string) $this->input('booking_pin_lat'));
        $pinLongitude = trim((string) $this->input('booking_pin_lng'));
        $eventDetail = trim((string) $this->input('booking_detail'));
        $pinDetail = sprintf('Patokan pin lokasi: %s', $pinAddress);
        $combinedEventDetail = $eventDetail !== ''
            ? $eventDetail."\n\n".$pinDetail
            : $pinDetail;

        return [
            'name' => trim((string) $this->input('booking_name')),
            'phone_number' => trim((string) $this->input('booking_whatsapp')),
            'event_date' => trim((string) $this->input('booking_date')),
            'package_type_id' => (int) $this->input('booking_package_type'),
            'event_session_id' => (int) $this->input('booking_session'),
            'package_id' => (int) $this->input('booking_package'),
            'location_id' => (int) $this->input('booking_location'),
            'location_province_id' => (int) $this->input('booking_location_province'),
            'location_city_id' => (int) $this->input('booking_location_city'),
            'location_district_id' => (int) $this->input('booking_location_district'),
            'location_village_id' => (int) $this->input('booking_location_village'),
            'google_maps_pin' => sprintf(
                'https://www.google.com/maps?q=%s,%s',
                $pinLatitude,
                $pinLongitude
            ),
            'event_detail' => $combinedEventDetail,
        ];
    }
}
