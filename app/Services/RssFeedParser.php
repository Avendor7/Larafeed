<?php

namespace App\Services;

use Carbon\Carbon;
use Exception;
use SimpleXMLElement;

class RssFeedParser
{
    /**
     * @return array{
     *     title: string|null,
     *     site_url: string|null,
     *     description: string|null,
     *     items: array<int, array{
     *         guid: string|null,
     *         title: string|null,
     *         url: string|null,
     *         summary: string|null,
     *         content: string|null,
     *         published_at: Carbon|null
     *     }>
     * }
     */
    public function parse(string $xml): array
    {
        $xml = $this->normalizeXml($xml);

        if ($xml === '') {
            throw new Exception('Feed XML is empty.');
        }

        $previousSetting = libxml_use_internal_errors(true);
        $parsed = simplexml_load_string($xml, SimpleXMLElement::class, LIBXML_NOCDATA);
        libxml_use_internal_errors($previousSetting);

        if ($parsed === false) {
            throw new Exception('Feed XML could not be parsed.');
        }

        if (isset($parsed->channel)) {
            return $this->parseRss($parsed);
        }

        return $this->parseAtom($parsed);
    }

    /**
     * @return array{
     *     title: string|null,
     *     site_url: string|null,
     *     description: string|null,
     *     items: array<int, array{
     *         guid: string|null,
     *         title: string|null,
     *         url: string|null,
     *         summary: string|null,
     *         content: string|null,
     *         published_at: Carbon|null
     *     }>
     * }
     */
    private function parseRss(SimpleXMLElement $feed): array
    {
        $channel = $feed->channel;

        $items = [];

        foreach ($channel->item ?? [] as $item) {
            $summary = $this->stringValue($item->description ?? null);
            $contentEncoded = $item->children('content', true);
            $content = null;

            if (isset($contentEncoded->encoded)) {
                $content = $this->stringValue($contentEncoded->encoded);
            }

            if ($content === null) {
                $content = $summary;
            }

            if ($summary === null) {
                $summary = $content;
            }

            $items[] = [
                'guid' => $this->stringValue($item->guid ?? null),
                'title' => $this->stringValue($item->title ?? null),
                'url' => $this->stringValue($item->link ?? null),
                'summary' => $summary,
                'content' => $content,
                'published_at' => $this->parseDate($this->stringValue($item->pubDate ?? null)),
            ];
        }

        return [
            'title' => $this->stringValue($channel->title ?? null),
            'site_url' => $this->stringValue($channel->link ?? null),
            'description' => $this->stringValue($channel->description ?? null),
            'items' => $items,
        ];
    }

    /**
     * @return array{
     *     title: string|null,
     *     site_url: string|null,
     *     description: string|null,
     *     items: array<int, array{
     *         guid: string|null,
     *         title: string|null,
     *         url: string|null,
     *         summary: string|null,
     *         content: string|null,
     *         published_at: Carbon|null
     *     }>
     * }
     */
    private function parseAtom(SimpleXMLElement $feed): array
    {
        $namespaces = $feed->getNamespaces(true);
        $atomNamespace = $namespaces[''] ?? $namespaces['atom'] ?? null;
        $atomFeed = $atomNamespace ? $feed->children($atomNamespace) : $feed;

        $items = [];

        foreach ($atomFeed->entry ?? [] as $entry) {
            $summary = $this->stringValue($entry->summary ?? null);
            $content = $this->stringValue($entry->content ?? null);

            if ($content === null) {
                $content = $summary;
            }

            if ($summary === null) {
                $summary = $content;
            }

            $items[] = [
                'guid' => $this->stringValue($entry->id ?? null),
                'title' => $this->stringValue($entry->title ?? null),
                'url' => $this->extractAtomLink($entry),
                'summary' => $summary,
                'content' => $content,
                'published_at' => $this->parseDate(
                    $this->stringValue($entry->updated ?? $entry->published ?? null)
                ),
            ];
        }

        return [
            'title' => $this->stringValue($atomFeed->title ?? null),
            'site_url' => $this->extractAtomLink($atomFeed),
            'description' => $this->stringValue($atomFeed->subtitle ?? null),
            'items' => $items,
        ];
    }

    private function extractAtomLink(SimpleXMLElement $entry): ?string
    {
        foreach ($entry->link ?? [] as $link) {
            $attributes = $link->attributes();
            $rel = isset($attributes['rel']) ? (string) $attributes['rel'] : null;

            if ($rel === null || $rel === 'alternate') {
                return $this->stringValue($attributes['href'] ?? null);
            }
        }

        return null;
    }

    private function stringValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }

    private function parseDate(?string $value): ?Carbon
    {
        if ($value === null) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (Exception) {
            return null;
        }
    }

    private function normalizeXml(string $xml): string
    {
        $xml = trim($xml);

        if ($xml === '') {
            return $xml;
        }

        if (str_starts_with($xml, "\xEF\xBB\xBF")) {
            $xml = substr($xml, 3);
        }

        $encoding = null;

        if (preg_match('/^<\?xml[^>]+encoding=["\']([^"\']+)["\']/i', $xml, $matches)) {
            $encoding = strtoupper($matches[1]);
        }

        if ($encoding !== null && $encoding !== 'UTF-8') {
            $converted = mb_convert_encoding($xml, 'UTF-8', $encoding);

            if ($converted !== false) {
                $xml = $converted;
            }
        }

        return trim($xml);
    }
}
