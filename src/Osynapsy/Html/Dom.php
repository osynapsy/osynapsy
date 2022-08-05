<?php
namespace Osynapsy\Html;

class Dom
{
    protected static $repo = [
        'headers' => [],
        'elements' => [],
        'footers' => []
    ];

    public static function addHeader($header)
    {
        if (!self::headerExists($header)) {
            self::$repo['headers'][] = $header;
        }
    }

    public static function headerExists($header)
    {
        return in_array($header, self::$repo['headers']);
    }

    public static function getHeaders()
    {
        return self::$repo['headers'];
    }

    public static function resetHeaders()
    {
        self::$repo['headers'] = [];
    }

    public static function exists($elementId)
    {
        return array_key_exists($elementId, self::$repo['elements']);
    }

    public static function addElement(Tag $element)
    {
        $id = $element->id;
        if (!empty($id)) {
            self::$repo['elements'][$id] = $element;
        }
    }

    public static function getById($elementId)
    {
        return self::$repo['elements'][$elementId] ?? null;
    }

    public static function addFooter($footer)
    {
        if (!self::footerExists($footer)) {
            self::$repo['footers'][] = $footer;
        }
    }

    public static function footerExists($footer)
    {
        return in_array($footer, self::$repo['footers']);
    }

    public static function resetFooters()
    {
        self::$repo['footers'] = [];
    }

    public static function getFooters()
    {
        return self::$repo['footers'];
    }
}
