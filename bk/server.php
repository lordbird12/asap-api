<?php

$jsonArray = '{
	"type": "bubble",
	"header": {
	  "type": "box",
	  "layout": "vertical",
	  "contents": [
		{
		  "type": "box",
		  "layout": "vertical",
		  "contents": [
			{
			  "type": "text",
			  "text": "Car Service Booking",
			  "size": "xl",
			  "wrap": true,
			  "weight": "bold"
			}
		  ],
		  "spacing": "sm"
		},
		{
			"type": "box",
			"layout": "vertical",
			"contents": [
				{
					"type": "text",
					"text": "ID: WN234",
					"size": "md"
				  },
				{
					"type": "text",
					"text": "กำลังดำเนินการ",
					"size": "xxl",
					"wrap": true,
					"weight": "bold",
					"color":"#FF595A"
				}
			],
			"paddingTop": "10px"
		  }
	  ]
	},
	"body": {
	  "type": "box",
	  "layout": "horizontal",
	  "contents": [
		{
		  "type": "image",
		  "url": "https://www.shareicon.net/data/128x128/2016/09/02/824400_airport_512x512.png",
		  "flex": 0,
		  "size": "sm"
		},
		{
		  "type": "box",
		  "layout": "vertical",
		  "contents": [
			{
				"type": "text",
				"text": "2กล-5948",
				"color": "#000000",
				"size": "xl",
				"weight": "bold"
			  },
			  {
				"type": "text",
				"text": "Toyota Accord • Silver",
				"color": "#cccccc",
				"size": "xl"
			  }
		  ]
		}
	  ],
	  "spacing": "md",
	  "margin": "md"
	},
	"footer": {
	  "type": "box",
	  "layout": "vertical",
	  "contents": [
		{
			"type": "button",
			"action": {
			  "type": "uri",
			  "label": "ยกเลิกการจอง",
			  "uri": "https://www.skairline.com"
			},
			"color": "#0070A8"
		  }
	  ],
	  "paddingAll": "20px"
	},
	"styles": {
	  "header": {
		"backgroundColor": "#ffffff"
	  },
	  "body": {
		"backgroundColor": "#F4F4F4"
	  },
	  "footer": {
		"backgroundColor": "#ffffff"
	  }
	}
  }';

$array = json_decode($jsonArray, true);

$dataPushMessages['url'] = "https://api.line.me/v2/bot/message/push";
$dataPushMessages['token'] = "F8qIRhPLGgPpiRHB8IThbSNOzE757bbN3DHmNwAV2UBQOxcg8DVKNeNsUswpnVBPHkal6/YjIQjEjyzo8tM23mfJOevX0rPvXkfzxrm8q6zV6WpjJRtD645WrCLju9Xn8IRx7hVkjpr5ZU5DstHTGwdB04t89/1O/w1cDnyilFU=";
// $messages['to'] = "U6e94990fec45b3842c12d2cfdd4bbbd4"; //U47ff0a6132c78e8a63e0c6dd213cad7f
$messages['to'] = "U2047b3aa7b14b519fb8f92202082e808";


// $dataPushMessages['url'] = "https://api.line.me/v2/bot/message/push";
// $dataPushMessages['token'] = "NPDREpXBaD5pXQFqDgiMMPoYlfYStlQuu0Et/aMo/xN0fL9nbX1TR/EqmQcNbM74u9Ac43tntgg6COZ3I7YnrWwXGclXRL9AqCgNMcPVgzJ/PR9Fl1+ZjCL19eeX/zXTXcAQxPH17/9E+HdWYjJY5gdB04t89/1O/w1cDnyilFU=";
// // $messages['to'] = "U6e94990fec45b3842c12d2cfdd4bbbd4"; //U47ff0a6132c78e8a63e0c6dd213cad7f
// $messages['to'] = "U18ffe6db67656cfa6a6aa36b65db3fe4";
// // $messages['to'] = "Ub20a49f041e9f03e1c0bd30b5fe8086c";
// // $messages['to'] = "U6e94990fec45b3842c12d2cfdd4bbbd4";
// $messages['to'] = "U939488f3084b153b695179266946358d";

$data = [
	'to' => "U759dea320a96bc4bddb03bcb008b6096",
	'messages' => [
		[
			'type' => 'flex',
			'altText' => 'This is a Flex Message',
			'contents' => $array // Your previously defined Flex Message structure
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
