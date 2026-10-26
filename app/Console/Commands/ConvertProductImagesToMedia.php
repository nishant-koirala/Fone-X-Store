<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ConvertProductImagesToMedia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:convert-images {--force : Re-convert even if product already has media}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing product images to Spatie Media Library with thumb conversions';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $force = (bool) $this->option('force');
        $products = Product::whereNotNull('image')->get();

        if ($products->isEmpty()) {
            $this->info('No products found with legacy image paths.');

            return self::SUCCESS;
        }

        $this->info("Found {$products->count()} products to process.");
        $converted = 0;
        $skipped = 0;

        foreach ($products as $product) {
            if (! $force && $product->hasMedia('images')) {
                $skipped++;

                continue;
            }

            $disk = Storage::disk('public');
            $imagePath = $product->image;

            if (! $disk->exists($imagePath)) {
                $this->warn("Image file not found for Product #{$product->id}: [{$imagePath}]");
                $skipped++;

                continue;
            }

            try {
                if ($force && $product->hasMedia('images')) {
                    $product->clearMediaCollection('images');
                }

                $fullPath = $disk->path($imagePath);
                $product->addMedia($fullPath)
                    ->preservingOriginal()
                    ->toMediaCollection('images');

                $converted++;
            } catch (\Throwable $e) {
                $this->error("Failed to convert image for Product #{$product->id}: {$e->getMessage()}");
            }
        }

        $this->info("Completed image conversion: {$converted} converted, {$skipped} skipped.");

        return self::SUCCESS;
    }
}
