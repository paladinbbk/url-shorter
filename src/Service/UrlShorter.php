<?php

namespace App\Service;

/**
 * Class UrlShorter
 * @package App\Service
 */
class UrlShorter
{
    const CHARS = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";

    /**
     * Method for generate short url
     *
     * @param int $urlId
     * @return string
     */
    public function encode(int $urlId): string
    {
        $base = strlen(self::CHARS);
        $converted = '';

        while ($urlId > 0) {
            $converted = substr(self::CHARS, bcmod($urlId, $base), 1) . $converted;
            $urlId = $this->bcFloor(bcdiv($urlId, $base));
        }

        return $converted;
    }

    /**
     * Method for decoding short url to url id
     *
     * @param $code
     * @return int
     */
    public function decode(string $code): int
    {
        $base = strlen(self::CHARS);
        $c = '0';
        for ($i = strlen($code); $i; $i--) {
            $c = \bcadd($c, \bcmul(strpos(self::CHARS, substr($code, (-1 * ($i - strlen($code))), 1))
                , \bcpow($base, $i - 1)));
        }

        return $this->bcFloor($c);
    }

    /**
     * @param $x
     * @return string
     */
    private function bcFloor($x)
    {
        return \bcmul($x, '1', 0);
    }
}