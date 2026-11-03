<?php

namespace App\Support;

class HtmlSanitizer
{
    /**
     * Allowed HTML tags (lowercase).
     *
     * @var array<string>
     */
    protected const ALLOWED_TAGS = [
        'p', 'br', 'strong', 'em', 'b', 'i', 'u', 'ul', 'ol', 'li',
        'h2', 'h3', 'h4', 'blockquote', 'code', 'pre', 'hr', 'a', 'img',
    ];

    /**
     * Tags whose element AND contents must be completely stripped.
     *
     * @var array<string>
     */
    protected const STRIP_WITH_CONTENTS = [
        'script', 'style', 'iframe', 'object', 'embed', 'form', 'svg',
        'math', 'base', 'meta', 'link', 'noscript', 'template', 'canvas',
        'video', 'audio', 'applet', 'frame', 'frameset', 'button', 'input',
        'textarea', 'select', 'option', 'optgroup',
    ];

    /**
     * Sanitize user-provided HTML or format plain-text as readable paragraphs.
     */
    public static function clean(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        // If the string contains no HTML tags, format plain text with line breaks
        if (! self::containsHtmlTags($html)) {
            return nl2br(e($html));
        }

        return self::sanitizeHtml($html);
    }

    /**
     * Alias for clean.
     */
    public static function sanitize(?string $html): string
    {
        return self::clean($html);
    }

    /**
     * Detect if a string contains any HTML tags.
     */
    public static function containsHtmlTags(string $input): bool
    {
        return (bool) preg_match('/<\s*\/?\s*[a-zA-Z]/', $input);
    }

    /**
     * Sanitize HTML markup via DOMDocument.
     */
    protected static function sanitizeHtml(string $html): string
    {
        // Guard against invalid UTF-8
        if (! mb_check_encoding($html, 'UTF-8')) {
            $converted = mb_convert_encoding($html, 'UTF-8', 'UTF-8');
            if (! mb_check_encoding($converted, 'UTF-8')) {
                return nl2br(e($html));
            }

            $html = $converted;
        }

        // Pre-strip dangerous block and foreign content elements to prevent unclosed tags
        // (e.g. unclosed <svg>, <script>, <style>, <iframe>) from absorbing subsequent safe content
        foreach (self::STRIP_WITH_CONTENTS as $tag) {
            $html = (string) preg_replace('#<'.$tag.'\b[^>]*>.*?</'.$tag.'>#is', '', $html);
            $html = (string) preg_replace('#<'.$tag.'\b[^>]*>#is', '', $html);
        }

        try {
            $dom = new \DOMDocument('1.0', 'UTF-8');
            $dom->preserveWhiteSpace = true;
            $dom->formatOutput = false;

            $previousErrors = libxml_use_internal_errors(true);

            // Wrap fragment in full valid HTML document with explicit UTF-8 meta
            $wrapped = '<?xml encoding="utf-8" ?><!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body>'
                .$html
                .'</body></html>';

            $loaded = $dom->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING);

            libxml_clear_errors();
            libxml_use_internal_errors($previousErrors);

            if (! $loaded) {
                return nl2br(e($html));
            }

            $xpath = new \DOMXPath($dom);
            /** @var \DOMElement|null $body */
            $body = $xpath->query('//body')->item(0);

            if (! $body) {
                return nl2br(e($html));
            }

            // Collect all elements inside body
            $nodeList = $xpath->query('.//*', $body);
            if ($nodeList) {
                $elements = [];
                foreach ($nodeList as $node) {
                    if ($node instanceof \DOMElement) {
                        $elements[] = $node;
                    }
                }

                // Process bottom-up (children before parents) so removals/unwrapping don't disrupt traversal
                $elements = array_reverse($elements);

                foreach ($elements as $element) {
                    if (! $element->parentNode) {
                        continue;
                    }

                    $tagName = strtolower($element->nodeName);

                    // 1. Dangerous elements to strip with contents
                    if (in_array($tagName, self::STRIP_WITH_CONTENTS, true)) {
                        $element->parentNode->removeChild($element);

                        continue;
                    }

                    // 2. Elements not in allowlist: unwrap their children and remove the container tag
                    if (! in_array($tagName, self::ALLOWED_TAGS, true)) {
                        while ($element->firstChild) {
                            $element->parentNode->insertBefore($element->firstChild, $element);
                        }
                        $element->parentNode->removeChild($element);

                        continue;
                    }

                    // 3. For allowed tags, clean attributes
                    self::cleanAttributes($element, $tagName);
                }
            }

            // Extract inner HTML of body
            $output = '';
            foreach ($body->childNodes as $childNode) {
                $saved = $dom->saveHTML($childNode);
                if ($saved !== false) {
                    $output .= $saved;
                }
            }

            return trim($output);
        } catch (\Throwable) {
            return nl2br(e($html));
        }
    }

    /**
     * Clean and validate attributes on an allowed element.
     */
    protected static function cleanAttributes(\DOMElement $element, string $tagName): void
    {
        // Find attributes to remove (only a[href] and img[src, alt] are allowed)
        if ($element->hasAttributes()) {
            $toRemove = [];
            foreach ($element->attributes as $attr) {
                $attrName = strtolower($attr->nodeName);

                if ($tagName === 'a' && $attrName === 'href') {
                    continue;
                }

                if ($tagName === 'img' && ($attrName === 'src' || $attrName === 'alt')) {
                    continue;
                }

                $toRemove[] = $attr->nodeName;
            }

            foreach ($toRemove as $attrName) {
                $element->removeAttribute($attrName);
            }
        }

        // Validate <a> href
        if ($tagName === 'a') {
            $rawHref = $element->getAttribute('href');
            $cleanHref = self::validateHref($rawHref);

            if ($cleanHref === null) {
                $element->removeAttribute('href');
            } else {
                $element->setAttribute('href', $cleanHref);

                if (self::isExternalUrl($cleanHref)) {
                    $element->setAttribute('rel', 'noopener noreferrer nofollow');
                    $element->setAttribute('target', '_blank');
                }
            }
        }

        // Validate <img> src
        if ($tagName === 'img') {
            $rawSrc = $element->getAttribute('src');
            $cleanSrc = self::validateImgSrc($rawSrc);

            if ($cleanSrc === null) {
                // If the image has no valid source, remove the image element entirely
                if ($element->parentNode) {
                    $element->parentNode->removeChild($element);
                }
            } else {
                $element->setAttribute('src', $cleanSrc);
            }
        }
    }

    /**
     * Validate an <a> href attribute.
     */
    public static function validateHref(?string $href): ?string
    {
        if ($href === null || trim($href) === '') {
            return null;
        }

        $decoded = self::decodeEntities($href);

        // Normalize control chars and whitespace for scheme inspection
        $forSchemeCheck = (string) preg_replace('/[\x00-\x20\x7f\s]+/u', '', $decoded);

        // Explicitly reject dangerous schemes
        if (preg_match('/^(javascript|data|vbscript):/i', $forSchemeCheck)) {
            return null;
        }

        $trimmed = trim($decoded);

        // Relative path starting with / (excluding protocol-relative //)
        if (str_starts_with($trimmed, '/') && ! str_starts_with($trimmed, '//')) {
            return $trimmed;
        }

        // Anchor link starting with #
        if (str_starts_with($trimmed, '#')) {
            return $trimmed;
        }

        // Must match http, https, mailto, or tel
        if (preg_match('/^(https?|mailto|tel):/i', $trimmed, $matches)) {
            $scheme = strtolower($matches[1]);
            if (in_array($scheme, ['http', 'https', 'mailto', 'tel'], true)) {
                return $trimmed;
            }
        }

        return null;
    }

    /**
     * Validate an <img> src attribute.
     */
    public static function validateImgSrc(?string $src): ?string
    {
        if ($src === null || trim($src) === '') {
            return null;
        }

        $decoded = self::decodeEntities($src);

        $forSchemeCheck = (string) preg_replace('/[\x00-\x20\x7f\s]+/u', '', $decoded);

        if (preg_match('/^(javascript|data|vbscript):/i', $forSchemeCheck)) {
            return null;
        }

        $trimmed = trim($decoded);

        // Must be http(s) or a relative /storage or /images path
        if (preg_match('/^https?:\/\//i', $trimmed)) {
            return $trimmed;
        }

        if (
            str_starts_with($trimmed, '/storage/') ||
            $trimmed === '/storage' ||
            str_starts_with($trimmed, '/images/') ||
            $trimmed === '/images'
        ) {
            return $trimmed;
        }

        return null;
    }

    /**
     * Determine if a URL is external.
     */
    protected static function isExternalUrl(string $url): bool
    {
        if (! preg_match('/^https?:\/\//i', $url)) {
            return false;
        }

        $host = parse_url($url, PHP_URL_HOST);
        if (! $host) {
            return false;
        }

        if (function_exists('config')) {
            try {
                $appUrl = (string) config('app.url');
                $appHost = parse_url($appUrl, PHP_URL_HOST);
                if ($appHost && strcasecmp($host, $appHost) === 0) {
                    return false;
                }
            } catch (\Throwable) {
                // Ignore if container is not bound
            }
        }

        if (function_exists('request')) {
            try {
                $reqHost = request()->getHost();
                if ($reqHost && strcasecmp($host, $reqHost) === 0) {
                    return false;
                }
            } catch (\Throwable) {
                // Ignore if container is not bound
            }
        }

        return true;
    }

    /**
     * Fully decode HTML entities recursively.
     */
    protected static function decodeEntities(string $value): string
    {
        $decoded = $value;
        $maxIterations = 5;

        while ($maxIterations-- > 0) {
            $prev = $decoded;
            $decoded = html_entity_decode($decoded, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if ($decoded === $prev) {
                break;
            }
        }

        return $decoded;
    }
}
