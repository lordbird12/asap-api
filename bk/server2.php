<?php

// $datas = [];
// $datas["type"] = "flex";
// $datas["altText"] = "This is a Flex Message";
// $datas["contents"]["type"] = "carousel";
// $datas["contents"]["contents"][0]["type"] = "bubble";
// $datas["contents"]["contents"][0]["header"]["type"] = "box";
// $datas["contents"]["contents"][0]["header"]["layout"] = "horizontal";
// $datas["contents"]["contents"][0]["header"]["contents"][0]["type"] = "text";
// $datas["contents"]["contents"][0]["header"]["contents"][0]["text"] = "ช่องทางการติดต่อ";
// $datas["contents"]["contents"][0]["header"]["contents"][0]["weight"] = "bold";
// $datas["contents"]["contents"][0]["header"]["contents"][0]["size"] = "sm";
// $datas["contents"]["contents"][0]["header"]["contents"][0]["color"] = "#AAAAAA";

// $datas["contents"]["contents"][0]["footer"]["type"] = "box";
// $datas["contents"]["contents"][0]["footer"]["layout"] = "vertical";
// $datas["contents"]["contents"][0]["footer"]["contents"][0]["type"] = "button";
// $datas["contents"]["contents"][0]["footer"]["contents"][0]["action"]["type"] = "uri";
// $datas["contents"]["contents"][0]["footer"]["contents"][0]["action"]["label"] = "More";
// $datas["contents"]["contents"][0]["footer"]["contents"][0]["action"]["uri"] = "https://linecorp.com";
// $datas["contents"]["contents"][0]["footer"]["contents"][1]["type"] = "button";
// $datas["contents"]["contents"][0]["footer"]["contents"][1]["action"]["type"] = "uri";
// $datas["contents"]["contents"][0]["footer"]["contents"][1]["action"]["label"] = "More";
// $datas["contents"]["contents"][0]["footer"]["contents"][1]["action"]["uri"] = "https://linecorp.com";
// $datas["contents"]["contents"][0]["footer"]["contents"][2]["type"] = "button";
// $datas["contents"]["contents"][0]["footer"]["contents"][2]["action"]["type"] = "uri";
// $datas["contents"]["contents"][0]["footer"]["contents"][2]["action"]["label"] = "More";
// $datas["contents"]["contents"][0]["footer"]["contents"][2]["action"]["uri"] = "https://linecorp.com";

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
		"contents" => [
			[
				"type" => "button",
				"style" => "link",
				"height" => "sm",
				"action" => [
					"type" => "uri",
					"label" => "CALL",
					"uri" => "https://linecorp.com"
				]
			],
			[
				"type" => "button",
				"style" => "link",
				"height" => "sm",
				"action" => [
					"type" => "uri",
					"label" => "WEBSITE",
					"uri" => "https://linecorp.com"
				]
			],
			[
				"type" => "button",
				"style" => "link",
				"height" => "sm",
				"action" => [
					"type" => "uri",
					"label" => "Facebook",
					"uri" => "https://linecorp.com"
				]
			],[
				"type" => "button",
				"style" => "link",
				"height" => "sm",
				"action" => [
					"type" => "uri",
					"label" => "Instagram",
					"uri" => "https://linecorp.com"
				]
			],[
				"type" => "button",
				"style" => "link",
				"height" => "sm",
				"action" => [
					"type" => "uri",
					"label" => "Tiktok",
					"uri" => "https://linecorp.com"
				]
			],[
				"type" => "button",
				"style" => "link",
				"height" => "sm",
				"action" => [
					"type" => "uri",
					"label" => "WEBSITE",
					"uri" => "https://linecorp.com"
				]
			],
		],
		"flex" => 0
	]
];



$dataPushMessages['url'] = "https://api.line.me/v2/bot/message/push";
$dataPushMessages['token'] = "NPDREpXBaD5pXQFqDgiMMPoYlfYStlQuu0Et/aMo/xN0fL9nbX1TR/EqmQcNbM74u9Ac43tntgg6COZ3I7YnrWwXGclXRL9AqCgNMcPVgzJ/PR9Fl1+ZjCL19eeX/zXTXcAQxPH17/9E+HdWYjJY5gdB04t89/1O/w1cDnyilFU=";
// $messages['to'] = "U6e94990fec45b3842c12d2cfdd4bbbd4"; //U47ff0a6132c78e8a63e0c6dd213cad7f
$messages['to'] = "U18ffe6db67656cfa6a6aa36b65db3fe4";
// $messages['to'] = "Ub20a49f041e9f03e1c0bd30b5fe8086c";
// $messages['to'] = "U6e94990fec45b3842c12d2cfdd4bbbd4";
// $messages['to'] = "U939488f3084b153b695179266946358d";

$data = [
    'to' => "U08ce5362bf6a7268a5d7b5ab9d523cdc",
    'messages' => [
        [
            'type' => 'flex',
            'altText' => 'This is a Flex Message',
            'contents' => $jsonArray // Your previously defined Flex Message structure
        ]
    ]
];


// $messages['messages'][] = $jsonArray;
$encodeJson = json_encode($data);


sentMessage($encodeJson, $dataPushMessages);


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
