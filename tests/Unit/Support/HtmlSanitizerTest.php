<?php

namespace Tests\Unit\Support;

use App\Support\HtmlSanitizer;
use Tests\TestCase;

class HtmlSanitizerTest extends TestCase
{
    public function test_script_tags_and_contents_are_completely_removed(): void
    {
        $payload = '<script>alert(1)</script>';
        $cleaned = HtmlSanitizer::clean($payload);

        $this->assertStringNotContainsString('script', strtolower($cleaned));
        $this->assertStringNotContainsString('alert', $cleaned);
    }

    public function test_img_onerror_and_invalid_src_are_removed(): void
    {
        $payload = '<img src=x onerror=alert(1)>';
        $cleaned = HtmlSanitizer::clean($payload);

        $this->assertStringNotContainsString('onerror', strtolower($cleaned));
        $this->assertStringNotContainsString('alert', $cleaned);
    }

    public function test_javascript_href_is_rejected(): void
    {
        $payload = '<a href="javascript:alert(1)">x</a>';
        $cleaned = HtmlSanitizer::clean($payload);

        $this->assertStringNotContainsString('javascript', strtolower($cleaned));
        $this->assertStringNotContainsString('alert', $cleaned);
    }

    public function test_obfuscated_javascript_href_with_html_entities_and_whitespace_is_rejected(): void
    {
        $payload = '<a href=" jav&#x61;script:alert(1)">x</a>';
        $cleaned = HtmlSanitizer::clean($payload);

        $this->assertStringNotContainsString('javascript', strtolower($cleaned));
        $this->assertStringNotContainsString('alert', $cleaned);
    }

    public function test_data_uri_href_is_rejected(): void
    {
        $payload = '<a href="data:text/html;base64,PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg==">x</a>';
        $cleaned = HtmlSanitizer::clean($payload);

        $this->assertStringNotContainsString('data:', strtolower($cleaned));
        $this->assertStringNotContainsString('base64', $cleaned);
    }

    public function test_svg_onload_is_completely_removed(): void
    {
        $payload = '<svg onload=alert(1)>';
        $cleaned = HtmlSanitizer::clean($payload);

        $this->assertStringNotContainsString('svg', strtolower($cleaned));
        $this->assertStringNotContainsString('onload', strtolower($cleaned));
        $this->assertStringNotContainsString('alert', $cleaned);
    }

    public function test_iframe_is_completely_removed(): void
    {
        $payload = '<iframe src="https://evil.com/phish"></iframe>';
        $cleaned = HtmlSanitizer::clean($payload);

        $this->assertStringNotContainsString('iframe', strtolower($cleaned));
        $this->assertStringNotContainsString('evil.com', $cleaned);
    }

    public function test_onclick_on_allowed_tag_is_removed(): void
    {
        $payload = '<p onclick="alert(1)">x</p>';
        $cleaned = HtmlSanitizer::clean($payload);

        $this->assertStringNotContainsString('onclick', strtolower($cleaned));
        $this->assertStringNotContainsString('alert', $cleaned);
        $this->assertEquals('<p>x</p>', $cleaned);
    }

    public function test_style_tag_and_contents_are_completely_removed(): void
    {
        $payload = '<style>@import url(evil.css); body { display:none; }</style>';
        $cleaned = HtmlSanitizer::clean($payload);

        $this->assertStringNotContainsString('style', strtolower($cleaned));
        $this->assertStringNotContainsString('@import', $cleaned);
    }

    public function test_div_with_inline_style_and_javascript_is_disarmed(): void
    {
        $payload = '<div style="background:url(javascript:alert(1))">content</div>';
        $cleaned = HtmlSanitizer::clean($payload);

        $this->assertStringNotContainsString('div', strtolower($cleaned));
        $this->assertStringNotContainsString('style', strtolower($cleaned));
        $this->assertStringNotContainsString('javascript', strtolower($cleaned));
        $this->assertStringNotContainsString('alert', $cleaned);
        $this->assertEquals('content', $cleaned);
    }

    public function test_nested_and_unclosed_tags_are_handled_safely(): void
    {
        $payload = '<p>Unclosed paragraph <strong>bold text';
        $cleaned = HtmlSanitizer::clean($payload);

        $this->assertStringContainsString('Unclosed paragraph', $cleaned);
        $this->assertStringContainsString('bold text', $cleaned);
    }

    public function test_uppercase_tags_are_normalized_and_sanitized(): void
    {
        $payload = '<P><STRONG>BOLD</STRONG> and <A HREF="https://fonex.np">LINK</A></P>';
        $cleaned = HtmlSanitizer::clean($payload);

        $this->assertStringContainsString('BOLD', $cleaned);
        $this->assertStringContainsString('LINK', $cleaned);
        $this->assertStringContainsString('href="https://fonex.np"', $cleaned);
    }

    public function test_normal_safe_paragraph_with_safe_link_survives_intact(): void
    {
        $payload = '<p>Welcome to FoneX Store. Check our <a href="https://example.com/deals">deals</a> today.</p>';
        $cleaned = HtmlSanitizer::clean($payload);

        $this->assertStringContainsString('<p>Welcome to FoneX Store. Check our', $cleaned);
        $this->assertStringContainsString('href="https://example.com/deals"', $cleaned);
        $this->assertStringContainsString('rel="noopener noreferrer nofollow"', $cleaned);
        $this->assertStringContainsString('target="_blank"', $cleaned);
    }

    public function test_valid_image_with_allowed_src_and_alt_survives(): void
    {
        $payload = '<p><img src="/storage/products/iphone15.jpg" alt="iPhone 15"></p>';
        $cleaned = HtmlSanitizer::clean($payload);

        $this->assertStringContainsString('src="/storage/products/iphone15.jpg"', $cleaned);
        $this->assertStringContainsString('alt="iPhone 15"', $cleaned);
    }

    public function test_plain_text_renders_as_readable_paragraphs_with_line_breaks(): void
    {
        $plain = "First line of product description.\nSecond line with details.";
        $cleaned = HtmlSanitizer::clean($plain);

        $this->assertEquals(nl2br(e($plain)), $cleaned);
        $this->assertStringContainsString('<br />', $cleaned);
    }

    public function test_plain_text_with_math_comparison_symbols_is_escaped(): void
    {
        $plain = 'Camera rating: 8 > 7 and price < 50000 NPR';
        $cleaned = HtmlSanitizer::clean($plain);

        $this->assertStringContainsString('&gt;', $cleaned);
        $this->assertStringContainsString('&lt;', $cleaned);
    }
}
