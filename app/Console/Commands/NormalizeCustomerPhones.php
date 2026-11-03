<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Rules\NepaliPhone;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class NormalizeCustomerPhones extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customers:normalize-phones {--apply : Apply normalizations to customer phone records instead of running in dry-run mode}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit and normalize customer phone numbers to standard 10-digit Nepali mobile format (defaults to dry-run)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $apply = (bool) $this->option('apply');

        $this->info($apply
            ? 'Running in APPLY mode — non-colliding phone numbers will be updated.'
            : 'Running in DRY-RUN mode (default) — no changes will be written to the database.');

        /** @var Collection<int, Customer> $customers */
        $customers = Customer::whereNotNull('phone')->get();

        if ($customers->isEmpty()) {
            $this->info('No customer records with phone numbers found.');

            return self::SUCCESS;
        }

        // Map normalized phones to customers who produce that normalized phone
        /** @var array<string, array<int, Customer>> $normalizedMap */
        $normalizedMap = [];

        foreach ($customers as $customer) {
            $phone = (string) $customer->phone;
            $normalized = NepaliPhone::normalize($phone) ?? $phone;
            $normalizedMap[$normalized][] = $customer;
        }

        $alreadyClean = 0;
        /** @var array<int, array{id: int, name: string, current: string, normalized: string}> $eligible */
        $eligible = [];
        /** @var array<int, array{id: int, name: string, current: string, normalized: string, reason: string}> $collisions */
        $collisions = [];

        foreach ($customers as $customer) {
            $current = (string) $customer->phone;
            $normalized = NepaliPhone::normalize($current) ?? $current;

            // Check if multiple customers map to the same normalized phone
            $group = $normalizedMap[$normalized] ?? [];
            if (count($group) > 1) {
                $otherIds = collect($group)
                    ->pluck('id')
                    ->reject(fn ($id) => $id === $customer->id)
                    ->implode(', #');

                $collisions[] = [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'current' => $current,
                    'normalized' => $normalized,
                    'reason' => "Collides with customer(s) #{$otherIds}",
                ];

                continue;
            }

            // Already in clean format
            if ($current === $normalized) {
                $alreadyClean++;

                continue;
            }

            // Eligible for normalization without collision
            $eligible[] = [
                'id' => $customer->id,
                'name' => $customer->name,
                'current' => $current,
                'normalized' => $normalized,
            ];
        }

        // Display collisions if any
        if (! empty($collisions)) {
            $this->newLine();
            $this->warn('Found phone number collisions (left untouched — customers are never merged):');
            $this->table(['ID', 'Name', 'Current Phone', 'Target Normalized', 'Reason'], $collisions);
        }

        // Display eligible records if dry run or before applying
        if (! empty($eligible)) {
            $this->newLine();
            $this->info($apply ? 'Records to be normalized:' : 'Eligible records to normalize (dry-run):');
            $this->table(['ID', 'Name', 'Current Phone', 'Target Normalized'], array_slice($eligible, 0, 20));
            if (count($eligible) > 20) {
                $this->comment('... and '.(count($eligible) - 20).' more records.');
            }
        }

        // Apply if flag was passed
        if ($apply && ! empty($eligible)) {
            $updated = 0;
            foreach ($eligible as $row) {
                Customer::where('id', $row['id'])->update(['phone' => $row['normalized']]);
                $updated++;
            }
            $this->newLine();
            $this->info("Successfully updated {$updated} customer record(s).");
        } elseif (! $apply && ! empty($eligible)) {
            $this->newLine();
            $this->comment('Run with --apply to apply these changes to the database.');
        }

        $this->newLine();
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Scanned', $customers->count()],
                ['Already Clean', $alreadyClean],
                [$apply ? 'Successfully Normalized' : 'Eligible for Normalization', count($eligible)],
                ['Collisions (Untouched)', count($collisions)],
            ]
        );

        return self::SUCCESS;
    }
}
