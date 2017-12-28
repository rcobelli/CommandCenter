<?php

const DEBUG = false;

function doCall($endPoint, $parameters = array(), $method = 'GET') {
    // check if curl is available
    if (!function_exists('curl_init')) {
        throw new WunderlistException('This method requires cURL (http://php.net/curl), it seems like the extension isn\'t installed.');
    }
    // define url
    $url = 'https://a.wunderlist.com/api/v1' . '/' . $endPoint;
    // init curl
    $curl = curl_init();
    // set options
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 10);
    // init headers
    $headers = array();
    $headers[] = 'X-Client-ID: 1025cfb87d1091f7ac43';
	$headers[] = 'X-Access-Token: 57c172d75731cd508e3059f0c28eb68f6e58d6058e73549910c48dcb305a';
	$headers[] = 'Content-Type: application/json';
    // define headers with the request
    if (!empty($headers)) {
        // add headers
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    }
    // parameters are set
    if (!empty($parameters)) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($parameters) );
    }
    // method is POST, used for login or inserts
    if ($method == 'POST') {
        // define post method
        curl_setopt($curl, CURLOPT_POST, true);
    // method is DELETE
    } elseif ($method == 'DELETE') {
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }
    // execute
    $response = curl_exec($curl);
    // debug is on
    if (DEBUG) {
        echo $url . '<br/>';
		echo var_dump($curl) . '<br/>';
        print_r($response);
        echo '<br/><br/>';
    }
    // get HTTP response code
    $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    // close
    curl_close($curl);
    // response is empty or false
    if (empty($response)) {
        exit('Error: ' . $response);
    }
    // init result
    $result = false;
    // successfull response
    if (($httpCode == 200) || ($httpCode == 201)) {
        $result = json_decode($response, true);
    }
    // return
    return $result;
}

$lists = doCall('lists');

$dueToday = array();

foreach ($lists as $list) {
	$tasks = doCall('tasks?list_id=' . $list['id'], null, "GET");
	foreach ($tasks as $task) {
		if (!is_null($task['due_date']) && date('U', strtotime($task['due_date'])) <= date("U")) {
			$task['listTitle'] = $list['title'];
			array_push($dueToday, $task);
		}
	}
}

$html = '<div class="item"><h1>Wunderlist</h1><img src="../serviceIcons/wunderlist.png" class="icon"><table><tr><th>List</th><th>Task</th></tr>';
foreach( $dueToday as $tasks){
		$html .= '<tr><td>'.$tasks['listTitle'].'</td><td>'.$tasks['title'].'</td></tr>';
}
echo $html.'</table></div>';
