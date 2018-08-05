<?php

$url = "https://trello.com/b/2kTtlpJF.json";

$ch = curl_init();

// set url
curl_setopt($ch, CURLOPT_URL, $url);

//return the transfer as a string
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

// $output contains the output string
$output = curl_exec($ch);
$json = json_decode($output, true);



$title = "Trello";

$html = '<div class="item"><h1 ' . $css . '>'.$title.'</h1><img src="../serviceIcons/trello.png" class="icon"><table><tr><th> Card </th></tr>';
if (empty($json)) {
    $html .= '<tr><td colspan=3>Nothing left</td></tr>';
} else {
    foreach ($json['cards'] as $card) {
        if ($card['idList'] == "5b65f71ad026d736fdeb1f6c") {
            $html .= '<tr><td>' . $card['labels'][0]['name'] . ": " . $card['name'] . '</td></tr>';
        }
    }
}
echo $html.'</table></div>';
