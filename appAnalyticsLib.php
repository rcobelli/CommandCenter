<?php

/**
 * iTunesConnect
 *
 * PHP class to pull your iOS apps statistics analytics data from iTunesConnect
 *
 * @author Oleg Dyachenko <oleg.imcoder@gmail.com>
 * @version 0.1
 */

class iTunesConnect
{
    const authUrl = 'https://idmsa.apple.com/appleauth/auth/signin';
    const siteUrl = 'https://itunesconnect.apple.com';
    const apiUrl = 'https://analytics.itunes.apple.com/analytics/api/v1';
    const sessionUrl = 'https://olympus.itunes.apple.com/v1/session';
    const widgetKey = 'e0b80c3bf78523bfe80974d320935bfa30add02e1bff88ec2166c6bd5a706c42';
    const authCookies = ['myacinfo', 'itctx'];
    const measures = [
        'installs',
        'sessions'
    ];
    /*
    ,
    'activeDevices',
    'units',
    'rollingActiveDevices',
    'impressionsTotalUnique'
    */
    const dateFormat = 'Y-m-d\TH:i:s\Z';
    // Your app unique ID from iTunes Connect
    private $cookies = [
        'dslang' => 'EN-EN',
        'site'  => 'ENG',
    ];
    public $authSuccess = false;
    public function __construct($userId, $password)
    {
        $this->auth($userId, $password);
        if ($this->authSuccess) {
            $this->getSession();
        }
    }
    private function auth($userId, $password)
    {
        $request = [
            'accountName' => $userId,
            'password' => $password,
            'rememberMe' => false,
        ];
        $headers = [
            'Referer' => self::authUrl.'?widgetKey='.self::widgetKey.'&widgetDomain='.self::siteUrl.':443&font=sf',
            'X-Apple-Widget-Key' => self::widgetKey,
            'X-Requested-With' => 'XMLHttpRequest',
            'X-Requested-By' => 'analytics.itunes.apple.com'
        ];
        return $this->curlRequest(self::authUrl, 'post', $request, $headers, true);
    }
    private function curlRequest($url, $method = 'get', $postData = [], $headers = [], $auth = false)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($method === 'post') {
            curl_setopt($ch, CURLOPT_POST, 1);
            if (!empty($postData)) {
                $postData = json_encode($postData);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
                $headers['Content-Length'] = strlen($postData);
            }
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, self::getHeaders($url, $headers));
        if ($auth) {
            curl_setopt($ch, CURLOPT_HEADERFUNCTION, [$this, 'processHeaders']);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);

        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpStatus === 200) {
            return json_decode(gzdecode($response), true);
        }

        return $response;
    }
    public function processHeaders($curl, $header)
    {
        $search = 'set-cookie:';
        $position = stripos($header, $search);
        if ($position === false) {
            return strlen($header);
        }

        $cookies = explode(';', substr($header, $position + strlen($search)));
        foreach ($cookies as $cookie) {
            $cookie = explode('=', $cookie, 2);
            $cookieKey = trim($cookie[0]);
            if (in_array($cookieKey, self::authCookies)) {
                $this->cookies[$cookieKey] = trim($cookie[1]);
                $this->authSuccess = true;
            }
        }
        return strlen($header);
    }
    public function getSession()
    {
        $url = parse_url(self::siteUrl);
        $headers = [
            'Origin' => $url['scheme'].'://'.$url['host'],
            'Referer' => $url['scheme'].'://'.$url['host'].'/login',
            'Cookie' => self::getCookiesString($this->cookies)
        ];

        $url = self::sessionUrl;
        return $this->curlRequest($url, 'get', [], $headers, true);
    }
    public function getStats(\DateTime $startTime, \DateTime $endTime, $appID, array $measures = [], $frequency = 'WEEK', array $filters = [])
    {
        $url = parse_url(self::apiUrl);
        $headers = [
            'Cookie' => self::getCookiesString($this->cookies),
            'X-Requested-By' => 'analytics.itunes.apple.com',
        ];

        $url = self::apiUrl . '/data/time-series';
        $measures = empty($measures) ? self::measures : [array_intersect(self::measures, $measures)];
        $request = [
            'adamId' => [$appID],
            'frequency' => $frequency,
            'measures' => $measures,
            'group' => null,
            'dimensionFilters' => empty($filters) ? [] : [$filters],
            'startTime' => $startTime->format(self::dateFormat),
            'endTime' => $endTime->format(self::dateFormat)
        ];
        $response = $this->curlRequest($url, 'post', $request, $headers);
        if (!is_array($response) or empty($response)) {
            return $response;
        }

        return self::formatResponse($response);
    }

    private static function getHeaders($url, $headers)
    {
        $url = parse_url($url);
        $default = [
            'Accept' => 'application/json, text/plain, */*',
            'Accept-Encoding' => 'gzip, deflate, br',
            'Accept-Language' => 'en-EN,en;q=0.8,en-US;q=0.6,en;q=0.4,uk;q=0.2',
            'Connection' => 'keep-alive',
            'Content-Type' => 'application/json;charset=UTF-8',
            'Host' => $url['host'],
            'Origin' => $url['scheme'].'://'.$url['host'],
            'Referer' => $url['scheme'].'://'.$url['host'].'/',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.101 Safari/537.36',
        ];
        $headers = array_merge($default, $headers);
        $results = [];
        foreach ($headers as $header => $value) {
            array_push($results, $header.':'.$value);
        }
        return $results;
    }

    private static function getCookiesString(array $cookies)
    {
        $results = [];
        foreach ($cookies as $cookieKey => $cookieValue) {
            array_push($results, $cookieKey . '=' . $cookieValue);
        }
        return implode('; ', $results);
    }

    private static function formatResponse(array $response)
    {
        $stats = [];
        if (!array_key_exists('results', $response) or empty($response['results'])) {
            return $stats;
        }
        foreach ($response['results'] as $group) {
            if (!array_key_exists('data', $group) or empty($group['data'])) {
                continue;
            }
            foreach ($group['data'] as $key => $day) {
                $stats[$key] = array_key_exists($key, $stats) ? array_merge($stats[$key], $day) : $day;
            }
        }
        return $stats;
    }
}
