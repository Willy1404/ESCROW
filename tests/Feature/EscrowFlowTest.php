<?php
// tests/Feature/EscrowFlowTest.php
namespace Tests\Feature;

use App\Models\EscrowTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EscrowFlowTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $buyer;
    protected $seller;
    protected $bankStaff;
    protected $escrow;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->buyer = User::factory()->create([
            'user_id' => 'B' . $this->faker->randomNumber(5),
            'role' => 'buyer',
        ]);

        $this->seller = User::factory()->create([
            'user_id' => 'S' . $this->faker->randomNumber(5),
            'role' => 'seller',
        ]);

        $this->bankStaff = User::factory()->create([
            'user_id' => 'BS' . $this->faker->randomNumber(5),
            'role' => 'bank_staff',
        ]);

        // Create an escrow transaction
        $this->escrow = EscrowTransaction::create([
            'escrow_id' => 'ESC' . $this->faker->randomNumber(8),
            'buyer_id' => $this->buyer->user_id,
            'seller_id' => $this->seller->user_id,
            'amount' => 1000.00,
            'currency' => 'USD',
            'status' => 'Funds Pending',
            'delivery_deadline' => now()->addDays(14),
            'inspection_period' => 7,
        ]);
    }

    /** @test */
    public function buyer_can_create_and_fund_escrow()
    {
        $this->actingAs($this->buyer);

        // Test escrow creation
        $response = $this->post(route('escrow.store'), [
            'seller_id' => $this->seller->user_id,
            'amount' => 500.00,
            'currency' => 'USD',
            'delivery_deadline' => now()->addDays(10)->format('Y-m-d'),
            'inspection_period' => 5,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('escrow_transactions', [
            'buyer_id' => $this->buyer->user_id,
            'seller_id' => $this->seller->user_id,
            'amount' => 500.00,
            'status' => 'Funds Pending',
        ]);

        // Test fund deposit
        $response = $this->post(route('payments.deposit', $this->escrow->escrow_id), [
            'payment_method' => 'Bank Transfer',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payments', [
            'escrow_id' => $this->escrow->escrow_id,
            'buyer_id' => $this->buyer->user_id,
            'amount' => 1000.00,
            'payment_method' => 'Bank Transfer',
            'status' => 'Completed',
        ]);

        $this->assertDatabaseHas('escrow_transactions', [
            'escrow_id' => $this->escrow->escrow_id,
            'status' => 'Funds Received',
        ]);
    }

    /** @test */
    public function seller_can_update_shipment_details()
    {
        $this->escrow->update(['status' => 'Funds Received']);
        $this->actingAs($this->seller);

        $response = $this->put(route('shipments.update', $this->escrow->escrow_id), [
            'tracking_id' => 'TRACK123456',
            'carrier' => 'FedEx',
            'estimated_arrival' => now()->addDays(5)->format('Y-m-d'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('shipments', [
            'escrow_id' => $this->escrow->escrow_id,
            'tracking_id' => 'TRACK123456',
            'carrier' => 'FedEx',
            'status' => 'In Transit',
        ]);

        $this->assertDatabaseHas('escrow_transactions', [
            'escrow_id' => $this->escrow->escrow_id,
            'status' => 'In Transit',
        ]);
    }

    /** @test */
    public function seller_can_confirm_delivery()
    {
        $this->escrow->update(['status' => 'In Transit']);
        $this->actingAs($this->seller);

        // Create a shipment
        $this->escrow->shipments()->create([
            'tracking_id' => 'TRACK123456',
            'carrier' => 'FedEx',
            'estimated_arrival' => now()->addDays(5),
            'status' => 'In Transit',
        ]);

        $response = $this->post(route('shipments.confirm', $this->escrow->escrow_id));

        $response->assertRedirect();
        $this->assertDatabaseHas('shipments', [
            'escrow_id' => $this->escrow->escrow_id,
            'status' => 'Delivered',
        ]);

        $this->assertDatabaseHas('escrow_transactions', [
            'escrow_id' => $this->escrow->escrow_id,
            'status' => 'Waiting for Buyer Approval',
        ]);
    }

    /** @test */
    public function buyer_can_release_funds()
    {
        $this->escrow->update(['status' => 'Waiting for Buyer Approval']);
        $this->actingAs($this->buyer);

        $response = $this->post(route('payments.release', $this->escrow->escrow_id));

        $response->assertRedirect();
        $this->assertDatabaseHas('escrow_transactions', [
            'escrow_id' => $this->escrow->escrow_id,
            'status' => 'Funds Released',
        ]);
    }

    /** @test */
    public function buyer_can_create_dispute()
    {
        $this->escrow->update(['status' => 'Waiting for Buyer Approval']);
        $this->actingAs($this->buyer);

        $response = $this->post(route('disputes.store', $this->escrow->escrow_id), [
            'reason' => 'The product is defective.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('disputes', [
            'escrow_id' => $this->escrow->escrow_id,
            'buyer_id' => $this->buyer->user_id,
            'seller_id' => $this->seller->user_id,
            'reason' => 'The product is defective.',
            'status' => 'Pending',
        ]);

        $this->assertDatabaseHas('escrow_transactions', [
            'escrow_id' => $this->escrow->escrow_id,
            'status' => 'Escrow On Hold',
        ]);
    }

    /** @test */
    public function bank_staff_can_resolve_dispute()
    {
        $this->escrow->update(['status' => 'Escrow On Hold']);
        $this->actingAs($this->bankStaff);

        // Create a dispute
        $dispute = $this->escrow->disputes()->create([
            'dispute_id' => 'DISP' . $this->faker->randomNumber(8),
            'buyer_id' => $this->buyer->user_id,
            'seller_id' => $this->seller->user_id,
            'reason' => 'The product is defective.',
            'status' => 'Pending',
        ]);

        $response = $this->post(route('disputes.update', $dispute->dispute_id), [
            'resolution' => 'Refund the buyer.',
            'status' => 'Resolved',
            'refund' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('disputes', [
            'dispute_id' => $dispute->dispute_id,
            'status' => 'Resolved',
            'resolution' => 'Refund the buyer.',
            'resolved_by' => $this->bankStaff->user_id,
        ]);

        $this->assertDatabaseHas('escrow_transactions', [
            'escrow_id' => $this->escrow->escrow_id,
            'status' => 'Funds Released',
        ]);
    }
}