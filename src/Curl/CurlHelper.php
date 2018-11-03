<?php
/**
 * @copyright ©2018 Lu Wei
 * @author Lu Wei
 * @link http://www.luweiss.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/10/28 2:44
 */


namespace luweiss\Curl;


class CurlHelper
{
    /**
     * @param array $params
     * @param bool $urlEncode
     * @return string
     * @throws CurlException
     */
    public static function paramsToQueryString($params = [], $urlEncode = true)
    {
        if (!is_array($params) && !($params instanceof \ArrayObject)) {
            throw new CurlException('参数$params必须是数组。');
        }
        $queryStringArray = [];
        foreach ($params as $k => $v) {
            if (is_array($v) || ($v instanceof \ArrayObject)) {
                foreach ($v as $subK => $subV) {
                    $queryStringArray[] = $k . '[' . $subK . ']=' . ($urlEncode ? urlencode($subV) : $subV);
                }
            } else {
                $queryStringArray[] = $k . '=' . ($urlEncode ? urlencode($v) : $v);
            }
        }
        return implode('&', $queryStringArray);
    }

    public static function appendBaseUrl($baseUrl, $url)
    {
        if (!$baseUrl) {
            return $url;
        }
        if (!$url) {
            throw new CurlException('参数$url不能为空。');
        }
        $baseUrl = trim($baseUrl, '/');
        if (mb_stripos($url, '/') === 0) {
            $url = mb_substr($url, 1);
        }
        if (mb_stripos($url, 'http://') === 0
            || mb_stripos($url, 'https://') === 0
            || mb_stripos($url, 'ftp://') === 0
        ) {
            return $url;
        }
        return $baseUrl . '/' . $url;
    }

    public static function appendQueryString($url, $queryString)
    {
        if ($queryString === null || $queryString === '') {
            return $url;
        }
        $url = trim($url, '&');
        $url = trim($url, '?');
        if (mb_stripos($url, '?') !== false) {
            return $url . '&' . $queryString;
        } else {
            return $url . '?' . $queryString;
        }
    }
}
