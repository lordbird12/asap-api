<?php
$servername = "dev-asha9.com";
$username = "devashac_chidchat";
$password = "mM330335@";
$dbname = "chidchat";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}

/*Get Data From POST Http Request*/
$datas = file_get_contents('php://input');
/*Decode Json From LINE Data Body*/
$deCode = json_decode($datas, true);

file_put_contents('log.txt', file_get_contents('php://input') . PHP_EOL, FILE_APPEND);

$replyToken = $deCode['events'][0]['replyToken'];
$userId = $deCode['events'][0]['source']['userId'];
$text = $deCode['events'][0]['message']['text'];

if ($text == "Chid Chat") {

	$sql = "SELECT * FROM contact_us";
	$result = $conn->query($sql);

	$btns = [];

	if ($result->num_rows > 0) {
		// output data of each row
		while ($row = $result->fetch_assoc()) {
			$buttonArray = [
				"type" => "button",
				"style" => "link",
				"height" => "sm",
				"action" => [
					"type" => "uri",
					"label" => $row['name'],
					"uri" =>  $row['url']
				]
			];

			array_push($btns, $buttonArray);
		}
	}

	$jsonArray = [
		"type" => "bubble",
		"hero" => [
			"type" => "image",
			"url" => "https://t4.ftcdn.net/jpg/04/66/25/33/360_F_466253361_c4fAjCqVZD4L2boH8vfqjUbUYk0wLcP7.jpg",
			"size" => "full",
			"aspectRatio" => "20:13",
			"aspectMode" => "cover",
			"action" => [
				"type" => "uri",
				"uri" => "http://linecorp.com/"
			]
		],
		"body" => [
			"type" => "box",
			"layout" => "vertical",
			"contents" => [
				[
					"type" => "text",
					"text" => "ร้านเฟอร์นีเจอร์ ChidChat",
					"weight" => "bold",
					"size" => "xl"
				],

				[
					"type" => "box",
					"layout" => "vertical",
					"margin" => "lg",
					"spacing" => "sm",
					"contents" => [
						[
							"type" => "box",
							"layout" => "baseline",
							"spacing" => "sm",
							"contents" => [
								[
									"type" => "text",
									"text" => "ที่ตั้ง",
									"color" => "#aaaaaa",
									"size" => "sm",
									"flex" => 1
								],
								[
									"type" => "text",
									"text" => "69/83 หมู่ 4 ลำต้องติ่ง หนองจอก กรุงเทพฯ",
									"wrap" => true,
									"color" => "#666666",
									"size" => "sm",
									"flex" => 5
								]
							]
						],
						[
							"type" => "box",
							"layout" => "baseline",
							"spacing" => "sm",
							"contents" => [
								[
									"type" => "text",
									"text" => "เวลา",
									"color" => "#aaaaaa",
									"size" => "sm",
									"flex" => 1
								],
								[
									"type" => "text",
									"text" => "10:00 - 23:00",
									"wrap" => true,
									"color" => "#666666",
									"size" => "sm",
									"flex" => 5
								]
							]
						]
					]
				]
			]
		],
		"footer" => [
			"type" => "box",
			"layout" => "vertical",
			"spacing" => "sm",
			"contents" => $btns,
			"flex" => 0
		]
	];

	$dataPushMessages['url'] = "https://api.line.me/v2/bot/message/push";
	$dataPushMessages['token'] = "NPDREpXBaD5pXQFqDgiMMPoYlfYStlQuu0Et/aMo/xN0fL9nbX1TR/EqmQcNbM74u9Ac43tntgg6COZ3I7YnrWwXGclXRL9AqCgNMcPVgzJ/PR9Fl1+ZjCL19eeX/zXTXcAQxPH17/9E+HdWYjJY5gdB04t89/1O/w1cDnyilFU=";
	// $messages['to'] = "U6e94990fec45b3842c12d2cfdd4bbbd4"; //U47ff0a6132c78e8a63e0c6dd213cad7f
	$messages['to'] = $userId;
	// $messages['to'] = "Ub20a49f041e9f03e1c0bd30b5fe8086c";
	// $messages['to'] = "U6e94990fec45b3842c12d2cfdd4bbbd4";
	// $messages['to'] = "U939488f3084b153b695179266946358d";

	$data = [
		'to' => $userId,
		'messages' => [
			[
				'type' => 'flex',
				'altText' => 'ร้านเฟอร์นีเจอร์ Chid Chat',
				'contents' => $jsonArray // Your previously defined Flex Message structure
			]
		]
	];


	// $messages['messages'][] = $jsonArray;
	$encodeJson = json_encode($data);


	sentMessageChidchat($encodeJson, $dataPushMessages);
	return;
}


$sql = "SELECT answer FROM line_key_words where keyword like '%" . $text . "%' limit 1";
$result = $conn->query($sql);
$answer = "";
if ($result->num_rows > 0) {
	$row = $result->fetch_assoc();
	$answer = $row['answer'];
} else {
	$answer = "ไม่พบผลลัพธ์การตอบ (ฉันไม่เข้าใจ)";
}
$conn->close();

$messages = [];
$messages['replyToken'] = $replyToken;
$messages['messages'][0] = getFormatTextMessage($answer);

$encodeJson = json_encode($messages);

$LINEDatas['url'] = "https://api.line.me/v2/bot/message/reply";
$LINEDatas['token'] = "NPDREpXBaD5pXQFqDgiMMPoYlfYStlQuu0Et/aMo/xN0fL9nbX1TR/EqmQcNbM74u9Ac43tntgg6COZ3I7YnrWwXGclXRL9AqCgNMcPVgzJ/PR9Fl1+ZjCL19eeX/zXTXcAQxPH17/9E+HdWYjJY5gdB04t89/1O/w1cDnyilFU=";

$results = sentMessage($encodeJson, $LINEDatas);

/*Return HTTP Request 200*/
http_response_code(200);

function getFormatTextMessage($text)
{
	$datas = [];
	$datas['type'] = 'text';
	$datas['text'] = $text;

	return $datas;
}

function sentMessageChidchat($encodeJson, $datas)
{
	$datasReturn = [];
	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_URL => $datas['url'],
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS => $encodeJson,
		CURLOPT_HTTPHEADER => array(
			"authorization: Bearer " . $datas['token'],
			"cache-control: no-cache",
			"content-type: application/json; charset=UTF-8",
		),
	));

	$response = curl_exec($curl);
	// dd($response);
	$err = curl_error($curl);

	curl_close($curl);

	if ($err) {
		$datasReturn['result'] = 'E';
		$datasReturn['message'] = $err;
	} else {
		if ($response == "{}") {
			$datasReturn['result'] = 'S';
			$datasReturn['message'] = 'Success';
		} else {
			$datasReturn['result'] = 'E';
			$datasReturn['message'] = $response;
		}
	}

	return $datasReturn;
}


function sentMessage($encodeJson, $datas)
{
	$datasReturn = [];
	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_URL => $datas['url'],
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS => $encodeJson,
		CURLOPT_HTTPHEADER => array(
			"authorization: Bearer " . $datas['token'],
			"cache-control: no-cache",
			"content-type: application/json; charset=UTF-8",
		),
	));

	$response = curl_exec($curl);
	$err = curl_error($curl);

	curl_close($curl);

	if ($err) {
		$datasReturn['result'] = 'E';
		$datasReturn['message'] = $err;
	} else {
		if ($response == "{}") {
			$datasReturn['result'] = 'S';
			$datasReturn['message'] = 'Success';
		} else {
			$datasReturn['result'] = 'E';
			$datasReturn['message'] = $response;
		}
	}

	return $datasReturn;
}
