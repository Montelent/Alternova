<?php

namespace App\Support;

use HTMLPurifier;
use HTMLPurifier_Config;

class HtmlSanitizer
{
    private static ?HTMLPurifier $purifier = null;

    public static function clean(?string $html): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        if (! class_exists(HTMLPurifier::class) || ! class_exists(HTMLPurifier_Config::class)) {
            // Fail closed if dependencies are unavailable: show text, never raw HTML.
            return htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        return static::purifier()->purify($html);
    }

    private static function purifier(): HTMLPurifier
    {
        if (static::$purifier instanceof HTMLPurifier) {
            return static::$purifier;
        }

        $config = HTMLPurifier_Config::createDefault();
        $config->set('Core.Encoding', 'UTF-8');
        $config->set(
            'HTML.Allowed',
            'p,br,strong,b,em,i,u,s,del,ins,small,sub,sup,ul,ol,li,dl,dt,dd,h2,h3,h4,h5,blockquote,code,pre,hr,div,span,a[href|title|target],img[src|alt|width|height],figure,figcaption,table,thead,tbody,tr,th[colspan|rowspan],td[colspan|rowspan]'
        );
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true]);
        $config->set('HTML.Nofollow', true);
        $config->set('HTML.TargetNoopener', true);
        $config->set('HTML.TargetNoreferrer', true);
        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        $config->set('URI.DisableExternalResources', true);
        $config->set('Attr.EnableID', false);
        $config->set('CSS.AllowedProperties', []);
        $config->set('HTML.SafeIframe', false);
        $config->set('HTML.SafeObject', false);
        $config->set('HTML.SafeEmbed', false);
        $config->set('AutoFormat.RemoveEmpty', true);

        $app = function_exists('app') ? app() : null;
        $cachePath = $app instanceof \Illuminate\Foundation\Application
            ? $app->storagePath('framework/cache/htmlpurifier')
            : sys_get_temp_dir().'/alternova-htmlpurifier';
        if (! is_dir($cachePath)) {
            @mkdir($cachePath, 0755, true);
        }
        $config->set('Cache.SerializerPath', $cachePath);

        static::$purifier = new HTMLPurifier($config);

        return static::$purifier;
    }
}
