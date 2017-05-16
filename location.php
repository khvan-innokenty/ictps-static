<?php

$ip = filter_var( $_SERVER['REMOTE_ADDR'], FILTER_SANITIZE_STRING);
$location = getLocation( $ip );
echo $location;


/* ======================================================================================= */


/** Определить город по IP по всему миру (на анг. языке)
 * @param $ip
 * @return mixed
 */
function getGlobalLocation( $ip ) {
    $options['http']['timeout'] = 1;
    $context = stream_context_create($options);
    try {
        $result = @file_get_contents("http://ip-api.com/json/$ip", 0, $context);
    }
    catch (Exception $e) {
        $result = false;
    }
    return $result === false ? null : json_decode($result, true);
}


/** Определить город по IP по России (на русском языке)
 * @param $ip
 * @return mixed
 */
function getRussiaLocation( $ip ) {
    $options = array(
        'http' => array(
            'timeout' => 1,
            'method'  => 'GET',
            'header'  => array(
                'Content-type: application/json',
                'Authorization: Token fd3b196616a42864c5504ebef165f605c4597b49'
            )
        )
    );

    $context = stream_context_create($options);
    $result = @file_get_contents("https://dadata.ru/api/v2/detectAddressByIp?ip=".$ip, false, $context);
    return $result === false ? null : json_decode($result, true);
}


/**
 * Определить город по IP
 * @param $ip
 * @return string
 */
function getLocation( $ip ) {
    $local = getRussiaLocation( $ip );

    $result = "";
    $country = "";

    // Нашли в России IP-адрес
    if ( $local !== null && $local['location'] !== null ) {
        $city_type = $local['location']['data']['city_type']; // считываем тип поселения
        $city = $local['location']['data']['city']; // считываем название поселения
        // Если определили название
        if ( $city !== null ) {
            $result  = $city_type !== null ? $city_type." " : ""; // если тип известен - записываем
            $result .= $city; // добавляем название
            $country = "RU";
        }
    }

    // если ничего не нашли
    if ( $result === "" ) {
        $global = getGlobalLocation( $ip );
        if ( $global !== null && $global['status'] === 'success' ) {
            $country = $global['countryCode'];
            $result = $global['city'].", ".$global['country'];
        }
    }

    return $result . ($country ? ' (' . $country . ')' : "");
}