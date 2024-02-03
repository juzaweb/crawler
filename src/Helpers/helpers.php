<?php

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

function replaceTranslate($searchs, $replaces, $text, &$count): string|null
{
    return preg_replace_callback(
        $searchs,
        function ($matches) use ($replaces) {
            $replace = $replaces[getReplaceSearchKey($matches[0])];
            if (ctype_upper($replace[0])){
                return $replace;
            }

            if (ctype_upper($matches[0])){
                return mb_strtoupper($replace);
            }

            if (ctype_upper(mb_substr($matches[0], 0, 1))){
                return ucfirst($replace);
            }

            return mb_strtolower($replace);
        },
        $text,
        -1,
        $count
    );
}

function getReplaceSearchKey(string $search): string
{
    return mb_strtolower($search);
}

function makeCrawlerRequest(?string $proxy = null): PendingRequest
{
    if ($proxy) {
        return Http::withOptions(['proxy' => $proxy])->timeout(20)->connectTimeout(10);
    }

    return Http::timeout(20)->connectTimeout(10);
}
