<?php

namespace Tests\Feature;

use App\Models\BookingRequest;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Room;
use App\Services\LeaseWorkflowService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class WorkflowIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_lease_cannot_be_activated_twice_for_one_room(): void
    {
        $first = Lease::factory()->active()->create();
        $second = Lease::factory()->create(['room_id' => $first->room_id, 'status' => 'draft']);

        $this->expectException(ValidationException::class);
        app(LeaseWorkflowService::class)->activate($second);
    }

    public function test_duplicate_reserved_booking_is_rejected_for_selected_room(): void
    {
        $room = Room::factory()->available()->create();
        BookingRequest::factory()->create([
            'property_id' => $room->property_id,
            'room_id' => $room->id,
            'desired_move_in' => now()->addDays(10)->toDateString(),
            'stage' => 'reserved',
        ]);

        $response = $this->post(route('booking.store', $room->property), [
            'name' => 'Calon Tenant', 'phone' => '08123456789',
            'desired_move_in' => now()->addDays(10)->toDateString(),
            'billing_cycle' => 'monthly', 'room_id' => $room->id,
        ]);

        $response->assertSessionHasErrors('room_id');
    }

    public function test_payment_cannot_exceed_invoice_balance(): void
    {
        $invoice = Invoice::factory()->create(['total' => 1000000]);

        $this->expectException(ValidationException::class);
        app(PaymentService::class)->recordPayment($invoice, ['amount' => 1000001, 'method' => 'cash']);
    }
}
