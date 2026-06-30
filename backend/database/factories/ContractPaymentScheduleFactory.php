<?php

namespace Database\Factories;

use App\Models\Contract;
use App\Models\ContractPaymentSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContractPaymentSchedule>
 */
class ContractPaymentScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amountDue = fake()->randomFloat(2, 1000, 100000);
        $amountPaid = fake()->randomFloat(2, 0, $amountDue);
        $remainingAmount = max(0, $amountDue - $amountPaid);

        return [
            'contract_id' => Contract::factory(),
            'schedule_number' => fake()->unique()->bothify('SCH-####??'),
            'payment_type' => fake()->randomElement([
                'rent',
                'sale_deposit',
                'sale_installment',
                'sale_final_payment',
                'management_fee',
                'other',
            ]),
            'due_date' => fake()->dateTimeBetween('-1 month', '+6 months'),
            'amount_due' => $amountDue,
            'currency' => 'MAD',
            'amount_paid' => $amountPaid,
            'remaining_amount' => $remainingAmount,
            'status' => fake()->randomElement([
                'pending',
                'partially_paid',
                'paid',
                'overdue',
                'cancelled',
            ]),
            'paid_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'days_late' => fake()->numberBetween(0, 30),
            'penalty_amount' => fake()->optional()->randomFloat(2, 100, 5000),
            'penalty_is_manual' => false,
            'owner_amount_due' => fake()->optional()->randomFloat(2, 1000, 100000),
            'owner_amount_paid' => fake()->randomFloat(2, 0, 100000),
            'owner_disbursement_status' => fake()->randomElement([
                'pending',
                'partially_paid',
                'paid',
                'not_applicable',
            ]),
            'receipt_number' => fake()->optional()->bothify('REC-####??'),
            'receipt_generated_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
