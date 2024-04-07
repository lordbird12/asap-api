<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;

class LineController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $httpClient = new CurlHTTPClient(config('line-bot.channel_token'));
        $bot = new LINEBot($httpClient, ['channelSecret' => config('line-bot.channel_secret')]);

        $signature = $request->header('x-line-signature');
        if (!$bot->validateSignature($request->getContent(), $signature)) {
            return response('Invalid signature', 400);
        }

        $events = $bot->parseEventRequest($request->getContent(), $signature);

        foreach ($events as $event) {
            if ($event['type'] == 'message' && $event['message']['type'] == 'text') {
                $this->handleTextMessage($bot, $event['replyToken'], $event['message']['text']);
            }
        }

        return response('OK', 200);
    }

    private function handleTextMessage($bot, $replyToken, $text)
    {
        $response = $bot->replyText($replyToken, 'You said: ' . $text);
    }
}
