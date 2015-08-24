<?php

if (file_exists('/tmp/301_cache.json')) {
    $cache = json_decode(file_get_contents('/tmp/301_cache.json'));
} else {
    $cache = new StdClass;
}

$host = $_SERVER['SERVER_NAME'];

if (!property_exists($cache, $host)) {
    $txt_records = dns_get_record($host, DNS_TXT);
    foreach ($txt_records as $txt_record) {
        if (!preg_match('#^301 (.*)$#', $txt_record['txt'], $matches)) {
            continue;
        }

        if (!filter_var($matches[1], FILTER_VALIDATE_URL)) {
            die("Invalid URL {$matches[1]}");
        }

        $cache->{$host} = $matches[1];
    }

    if (!property_exists($cache, $host)) {
        readfile('mainpage.html');
        exit;
        die ("Please add TXT record '301 http://your-target-url' in domain name '{$host}'");
    }
}

$url = $cache->{$host};
if (preg_match('#\*$#', $url)) {
    $url = rtrim(rtrim($url, '*'), '/') . $_SERVER['REQUEST_URI'];
}

header('X-Target: ' . $cache->{$host});
header('Location: ' . $url, true, 301);
