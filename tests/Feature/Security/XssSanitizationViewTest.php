<?php

namespace Tests\Feature\Security;

use App\Models\Category;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductCondition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class XssSanitizationViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_post_view_renders_with_no_malicious_xss_attributes_or_tags(): void
    {
        $maliciousContent = <<<'HTML'
            <script>alert(1)</script>
            <img src=x onerror=alert(1)>
            <a href="javascript:alert(1)">click me</a>
            <a href=" jav&#x61;script:alert(1)">encoded link</a>
            <a href="data:text/html;base64,PHNjcmlwdD4=">data uri</a>
            <svg onload=alert(1)>
            <iframe src="https://evil.com"></iframe>
            <p onclick="alert(1)">clickable paragraph</p>
            <style>@import url(evil.css);</style>
            <div style="background:url(javascript:alert(1))">styled div</div>
            <p>Safe paragraph with <a href="https://example.com/phone">safe external link</a></p>
        HTML;

        $post = Post::factory()->create([
            'title' => 'Security Audit Post',
            'content' => $maliciousContent,
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get(route('blog.show', $post->slug));

        $response->assertOk();
        $html = $response->getContent();

        $this->assertStringNotContainsString('alert(1)', $html);
        $this->assertStringNotContainsString('onerror=', $html);
        $this->assertStringNotContainsString('onclick=', $html);
        $this->assertStringNotContainsString('onload=', $html);
        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringNotContainsString('<svg onload', $html);
        $this->assertStringNotContainsString('<iframe', $html);
        $this->assertStringNotContainsString('evil.css', $html);
        $this->assertStringNotContainsString('style="background:url', $html);

        // Safe paragraph and link must survive
        $this->assertStringContainsString('Safe paragraph with', $html);
        $this->assertStringContainsString('href="https://example.com/phone"', $html);
        $this->assertStringContainsString('rel="noopener noreferrer nofollow"', $html);
    }

    public function test_product_view_renders_with_no_malicious_xss_attributes_or_tags(): void
    {
        $category = Category::factory()->create();

        $maliciousDescription = <<<'HTML'
            <script>alert(1)</script>
            <img src=x onerror=alert(1)>
            <a href="javascript:alert(1)">spec sheet</a>
            <p onclick="alert('pwned')">Super fast CPU</p>
            <div style="background:url(javascript:alert(1))">custom section</div>
            <p>Official specs available at <a href="https://apple.com">Apple Official</a>.</p>
        HTML;

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Secure Phone 15 Pro',
            'description' => $maliciousDescription,
            'is_visible' => true,
        ]);

        ProductCondition::factory()->create([
            'product_id' => $product->id,
            'grade' => 'A',
            'price' => 120000,
            'quantity_in_stock' => 5,
        ]);

        $response = $this->get(route('products.show', $product->slug));

        $response->assertOk();
        $html = $response->getContent();

        $this->assertStringNotContainsString('alert(1)', $html);
        $this->assertStringNotContainsString("alert('pwned')", $html);
        $this->assertStringNotContainsString('onerror=', $html);
        $this->assertStringNotContainsString('onclick=', $html);
        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringNotContainsString('style="background:url', $html);

        // Safe paragraph and link survive
        $this->assertStringContainsString('Official specs available at', $html);
        $this->assertStringContainsString('href="https://apple.com"', $html);
    }
}
