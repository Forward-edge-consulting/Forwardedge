<?php

namespace App\Support;

use DOMDocument;
use DOMNode;

/**
 * Keeps only bold, italic, links and line breaks. Used on save and again on render, so
 * nothing the editor (or a crafted request) sends can inject markup into a post.
 */
class RichText
{
    public static function clean(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }

        // Editors wrap lines in div/p; treat each as a line break.
        $html = preg_replace('#</(div|p)>#i', '<br>', $html);
        $html = preg_replace('#<(div|p)(\s[^>]*)?>#i', '', $html);
        $html = str_replace("\xC2\xA0", ' ', $html);

        $dom = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8"?><body>' . $html . '</body>', LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $body = $dom->getElementsByTagName('body')->item(0);
        $out = $body ? self::walk($body) : '';

        $out = preg_replace('#(<br>\s*){3,}#', '<br><br>', $out);
        $out = preg_replace('#^(\s|<br>)+|(\s|<br>)+$#', '', $out);

        return trim($out);
    }

    /**
     * For page-builder text: paragraphs, two heading levels, lists, quotes, bold, italic, links.
     * Anything else is unwrapped to its text; scripts and embeds are dropped.
     */
    public static function cleanDocument(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }
        $html = str_replace("\xC2\xA0", ' ', $html);

        $dom = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8"?><body>' . $html . '</body>', LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $body = $dom->getElementsByTagName('body')->item(0);
        if (!$body) {
            return '';
        }

        $out = '';
        $loose = '';   // text and inline markup that sits directly in the body becomes a paragraph
        $flush = function () use (&$out, &$loose) {
            $t = trim($loose);
            if ($t !== '' && trim(strip_tags($t)) !== '') {
                $out .= '<p>' . $t . '</p>';
            }
            $loose = '';
        };
        $blocks = ['p' => 'p', 'h1' => 'h2', 'h2' => 'h2', 'h3' => 'h3', 'h4' => 'h3', 'blockquote' => 'blockquote'];

        foreach ($body->childNodes as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE) {
                $tag = strtolower($child->nodeName);
                if (isset($blocks[$tag]) || $tag === 'div') {
                    $flush();
                    $inner = trim(self::walk($child));
                    if ($inner !== '' && trim(strip_tags($inner)) !== '') {
                        $name = $tag === 'div' ? 'p' : $blocks[$tag];
                        $out .= "<{$name}>{$inner}</{$name}>";
                    }
                    continue;
                }
                if ($tag === 'ul' || $tag === 'ol') {
                    $flush();
                    $items = '';
                    foreach ($child->childNodes as $li) {
                        if ($li->nodeType === XML_ELEMENT_NODE && strtolower($li->nodeName) === 'li') {
                            $inner = trim(self::walk($li));
                            if ($inner !== '' && trim(strip_tags($inner)) !== '') {
                                $items .= "<li>{$inner}</li>";
                            }
                        }
                    }
                    if ($items !== '') {
                        $out .= "<{$tag}>{$items}</{$tag}>";
                    }
                    continue;
                }
            }
            $wrapper = $dom->createElement('x');
            $wrapper->appendChild($child->cloneNode(true));
            $loose .= self::walk($wrapper);
        }
        $flush();

        return $out;
    }

    public static function toText(?string $html): string
    {
        return trim(html_entity_decode(strip_tags(str_replace('<br>', ' ', (string) $html)), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    protected static function walk(DOMNode $node): string
    {
        $out = '';

        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                $out .= htmlspecialchars($child->nodeValue, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                continue;
            }
            if ($child->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }

            $tag = strtolower($child->nodeName);

            if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'noscript', 'template'], true)) {
                continue; // drop the element and its content
            }

            $inner = self::walk($child);

            switch ($tag) {
                case 'b':
                case 'strong':
                    $out .= $inner === '' ? '' : "<strong>{$inner}</strong>";
                    break;
                case 'i':
                case 'em':
                    $out .= $inner === '' ? '' : "<em>{$inner}</em>";
                    break;
                case 'br':
                    $out .= '<br>';
                    break;
                case 'a':
                    $href = self::safeUrl($child->getAttribute('href'));
                    if ($href === null || $inner === '') {
                        $out .= $inner;
                        break;
                    }
                    $external = preg_match('#^https?://#i', $href) === 1;
                    $out .= '<a href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '"'
                        . ($external ? ' target="_blank" rel="noopener noreferrer"' : '') . ">{$inner}</a>";
                    break;
                default:
                    $out .= $inner; // unwrap anything else, keep its text
            }
        }

        return $out;
    }

    public static function safeUrl(?string $url): ?string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }
        if (preg_match('#^(https?://|mailto:|tel:)#i', $url) || str_starts_with($url, '/') || str_starts_with($url, '#')) {
            return $url;
        }
        // "forwardedgeconsulting.com" typed without a scheme
        if (preg_match('#^[a-z0-9.-]+\.[a-z]{2,}(/.*)?$#i', $url)) {
            return 'https://' . $url;
        }

        return null;
    }
}
