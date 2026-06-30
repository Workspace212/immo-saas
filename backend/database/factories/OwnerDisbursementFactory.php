<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Contract;
use App\Models\ContractPaymentSchedule;
use App\Models\FinancialAccount;
use App\Models\Owner;
use App\Models\OwnerDisbursement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<OwnerDisbursement> */
class OwnerDisbursementFactory extends Factory
{
    public function definition(): array
    {
        $due = fake()->randomFloat(2, 1000, 100000);
        $paid = fake()->randomFloat(2, 0, $due);

        return [
            'agency_id' => Agency::factory(),
            'owner_id' => Owner::factory(),
            'contract_id' => fake()->boolean(70) ? Contract::factory() : null,
            'contract_payment_schedule_id' => fake()->boolean(70) ? ContractPaymentSchedule::factory() : null,
            'financial_account_id' => fake()->boolean(70) ? FinancialAccount::factory() : null,
            'created_by' => fake()->boolean(80) ? User::factory() : null,
            'disbursement_number' => fake()->unique()->bothify('DIS-####??'),
            'amount_due' => $due,
            'amount_paid' => $paid,
            'remaining_amount' => $due - $paid,
            'currency' => 'MAD',
            'scheduled_date' => fake()->optional()->dateTimeBetween('now', '+1 month'),
            'paid_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'payment_method' => fake()->optional()->randomElement(['cash', 'bank_transfer', 'check']),
            'status' => fake()->randomElement(['pending', 'partially_paid', 'paid', 'cancelled']),
            'receipt_number' => fake()->optional()->bothify('REC-####??'),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
