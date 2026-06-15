<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;

/**
 * Renders the (decoded JSON) travel advice content and files returned by the
 * Nederland Wereldwijd widget API into safe, Bootstrap-friendly HTML.
 *
 * The API delivers free-form, nested content blocks and a list of files
 * (maps/images). All HTML coming from the API is sanitised to a small,
 * known-safe set of tags before it is rendered.
 */
class TravelAdviceContent
{
    /** @var list<string> Keys that may hold a block title. */
    private const TITLE_KEYS = ['category', 'paragraphtitle', 'title', 'header', 'heading', 'name', 'label'];

    /** @var list<string> Keys that hold raw HTML. */
    private const HTML_KEYS = ['paragraph', 'html'];

    /** @var list<string> Keys that hold nested content. */
    private const BODY_KEYS = ['contentblocks', 'content', 'value', 'body', 'text', 'description', 'items', 'children', 'blocks'];

    /** @var list<string> Allowed HTML tags after sanitising. */
    private const ALLOWED_TAGS = [
        'p', 'br', 'strong', 'em', 'b', 'i', 'u', 'small',
        'ul', 'ol', 'li', 'dl', 'dt', 'dd',
        'h3', 'h4', 'h5', 'h6', 'blockquote', 'a', 'span', 'div',
    ];

    /**
     * Render content blocks as a Bootstrap accordion.
     */
    public static function accordion(mixed $content, string $accordionId): string
    {
        $blocks = self::normalizeBlocks($content);
        if ($blocks === []) {
            return '';
        }

        $items = '';
        $i = 0;

        foreach ($blocks as $block) {
            $title = self::extractTitle($block);
            $body = self::renderContent($block, true);

            if ($title === '' && $body === '') {
                continue;
            }
            if ($title === '') {
                $title = 'Meer informatie';
            }

            $headingId = $accordionId . '-h' . $i;
            $collapseId = $accordionId . '-c' . $i;

            $items .= '<div class="accordion-item">'
                . '<h3 class="accordion-header" id="' . e($headingId) . '">'
                . '<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" '
                . 'data-bs-target="#' . e($collapseId) . '" aria-expanded="false" aria-controls="' . e($collapseId) . '">'
                . e($title) . '</button></h3>'
                . '<div id="' . e($collapseId) . '" class="accordion-collapse collapse" '
                . 'aria-labelledby="' . e($headingId) . '" data-bs-parent="#' . e($accordionId) . '">'
                . '<div class="accordion-body">' . $body . '</div></div></div>';

            $i++;
        }

        if ($items === '') {
            return '';
        }

        return '<div class="accordion mb-4" id="' . e($accordionId) . '">' . $items . '</div>';
    }

    /**
     * Render all files (maps/images/links) of a travel advice.
     *
     * When $skipStandard is true, the "standard" (app-version) map is omitted,
     * matching the behaviour of the embeddable widget.
     */
    public static function files(mixed $files, bool $skipStandard = true): string
    {
        if (!is_array($files) || $files === []) {
            return '';
        }

        $out = '';

        foreach ($files as $file) {
            if (!is_array($file)) {
                continue;
            }

            $mapType = is_string($file['mapType'] ?? null) ? strtolower($file['mapType']) : '';
            if ($skipStandard && $mapType === 'standard') {
                continue;
            }

            $url = isset($file['fileurl']) && is_string($file['fileurl']) ? trim($file['fileurl']) : '';
            if ($url === '' || preg_match('#^https?://#i', $url) !== 1) {
                continue;
            }

            $mime = is_string($file['mimetype'] ?? null) ? strtolower($file['mimetype']) : '';
            $title = is_string($file['filetitle'] ?? null) ? $file['filetitle'] : (string) ($file['filename'] ?? 'Bestand');
            $caption = is_string($file['fileDescription'] ?? null) ? $file['fileDescription'] : '';

            if (str_starts_with($mime, 'image/')) {
                $out .= '<figure class="figure mb-3">'
                    . '<img src="' . e($url) . '" class="figure-img img-fluid rounded" alt="' . e($title) . '" loading="lazy">';
                if ($caption !== '') {
                    $out .= '<figcaption class="figure-caption">' . e($caption) . '</figcaption>';
                }
                $out .= '</figure>';
            } else {
                $out .= '<p><a href="' . e($url) . '" rel="noopener noreferrer" target="_blank">'
                    . e($title) . '</a></p>';
            }
        }

        return $out;
    }

    /**
     * Returns the legend map (mapType = "legend") for a travel advice file
     * list, or null when none is present.
     *
     * @return array{url: string, title: string}|null
     */
    public static function legendMap(mixed $files): ?array
    {
        if (!is_array($files)) {
            return null;
        }

        foreach ($files as $file) {
            if (!is_array($file)) {
                continue;
            }

            $mapType = is_string($file['mapType'] ?? null) ? strtolower($file['mapType']) : '';
            if ($mapType !== 'legend') {
                continue;
            }

            $url = isset($file['fileurl']) && is_string($file['fileurl']) ? trim($file['fileurl']) : '';
            if ($url === '' || preg_match('#^https?://#i', $url) !== 1) {
                continue;
            }

            $title = is_string($file['filetitle'] ?? null) ? $file['filetitle'] : 'Kaart';

            return ['url' => $url, 'title' => $title];
        }

        return null;
    }

    /**
     * Returns the first usable map for a travel advice file list, or null.
     *
     * @return array{url: string, title: string}|null
     */
    public static function firstMap(mixed $files): ?array
    {        if (!is_array($files)) {
            return null;
        }

        $fallback = null;

        foreach ($files as $file) {
            if (!is_array($file)) {
                continue;
            }

            $url = isset($file['fileurl']) && is_string($file['fileurl']) ? trim($file['fileurl']) : '';
            if ($url === '' || preg_match('#^https?://#i', $url) !== 1) {
                continue;
            }

            $mime = is_string($file['mimetype'] ?? null) ? strtolower($file['mimetype']) : '';
            if (!str_starts_with($mime, 'image/')) {
                continue;
            }

            $title = is_string($file['filetitle'] ?? null) ? $file['filetitle'] : 'Kaart';
            $mapType = is_string($file['mapType'] ?? null) ? strtolower($file['mapType']) : '';

            if ($mapType === 'standard') {
                return ['url' => $url, 'title' => $title];
            }

            $fallback ??= ['url' => $url, 'title' => $title];
        }

        return $fallback;
    }

    /**
     * Bootstrap context colour for a travel advice classification.
     */
    public static function classificationColor(?string $classification): string
    {
        return match (strtolower(trim((string) $classification))) {
            'groen', 'green' => 'success',
            'geel', 'yellow', 'oranje', 'orange' => 'warning',
            'rood', 'red' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Format an API date string as d-m-Y (or return it escaped as-is).
     */
    public static function formatDate(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        $ts = strtotime($value);

        return $ts === false ? e($value) : date('d-m-Y', $ts);
    }

    /**
     * Render (nested) content blocks stored as decoded JSON.
     */
    public static function renderContent(mixed $node, bool $skipTitle = false): string
    {
        if ($node === null) {
            return '';
        }
        if (is_string($node)) {
            $text = trim($node);

            return $text === '' ? '' : '<p>' . nl2br(e($text)) . '</p>';
        }
        if (is_scalar($node)) {
            return '<p>' . e((string) $node) . '</p>';
        }
        if (!is_array($node)) {
            return '';
        }

        if (array_is_list($node)) {
            $out = '';
            foreach ($node as $child) {
                $out .= self::renderContent($child);
            }

            return $out;
        }

        $out = '';

        if (!$skipTitle) {
            foreach (self::TITLE_KEYS as $key) {
                if (!empty($node[$key]) && is_string($node[$key])) {
                    $out .= '<h4 class="h6 fw-semibold mt-3">' . e($node[$key]) . '</h4>';
                    break;
                }
            }
        }

        $renderedBody = false;

        foreach (self::HTML_KEYS as $key) {
            if (!empty($node[$key]) && is_string($node[$key])) {
                $out .= self::safeHtml($node[$key]);
                $renderedBody = true;
            }
        }

        foreach (self::BODY_KEYS as $key) {
            if (array_key_exists($key, $node) && $node[$key] !== null && $node[$key] !== '') {
                $out .= self::renderContent($node[$key]);
                $renderedBody = true;
            }
        }

        if (!$renderedBody) {
            foreach ($node as $key => $value) {
                if (in_array($key, self::TITLE_KEYS, true)) {
                    continue;
                }
                $out .= self::renderContent($value);
            }
        }

        return $out;
    }

    /**
     * Sanitise API HTML, keeping only a known-safe set of tags and attributes.
     */
    public static function safeHtml(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $loaded = $dom->loadHTML(
            '<?xml encoding="UTF-8"?><body>' . $html . '</body>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
        );
        libxml_clear_errors();

        if ($loaded === false) {
            return '';
        }

        $body = $dom->getElementsByTagName('body')->item(0);
        if ($body === null) {
            return '';
        }

        self::sanitizeNode($body);

        $out = '';
        foreach (iterator_to_array($body->childNodes) as $child) {
            $out .= $dom->saveHTML($child);
        }

        return $out;
    }

    /**
     * Normalise an arbitrary content value into a list of blocks.
     *
     * @return list<mixed>
     */
    private static function normalizeBlocks(mixed $content): array
    {
        if ($content === null || $content === '' || $content === []) {
            return [];
        }

        if (is_array($content) && array_is_list($content)) {
            return $content;
        }

        return [$content];
    }

    /**
     * Extract a block title using the known title keys.
     */
    private static function extractTitle(mixed $block): string
    {
        if (!is_array($block)) {
            return '';
        }

        foreach (self::TITLE_KEYS as $key) {
            if (!empty($block[$key]) && is_string($block[$key])) {
                return trim($block[$key]);
            }
        }

        return '';
    }

    private static function sanitizeNode(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMElement) {
                self::sanitizeNode($child);

                $tag = strtolower($child->tagName);
                if (!in_array($tag, self::ALLOWED_TAGS, true)) {
                    while ($child->firstChild !== null) {
                        $node->insertBefore($child->firstChild, $child);
                    }
                    $node->removeChild($child);

                    continue;
                }

                foreach (iterator_to_array($child->attributes) as $attr) {
                    $name = strtolower($attr->name);

                    $isSafeHref = $tag === 'a'
                        && $name === 'href'
                        && preg_match('#^(https?:|mailto:|tel:)#i', trim($attr->value)) === 1;

                    if (!$isSafeHref) {
                        $child->removeAttribute($attr->name);
                    }
                }

                if ($tag === 'a' && $child->hasAttribute('href')) {
                    $child->setAttribute('rel', 'noopener noreferrer');
                    $child->setAttribute('target', '_blank');
                }
            } elseif (!($child instanceof DOMText)) {
                $node->removeChild($child);
            }
        }
    }
}
