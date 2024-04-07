<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;

class VerifyLineSignature
{
    public function handle(Request $request, Closure $next)
    {
        $signature = $request->header('x-line-signature');
        $body = $request->getContent();

        $httpClient = new CurlHTTPClient(config('line-bot.channel_token'));
        $bot = new LINEBot($httpClient, ['channelSecret' => config('line-bot.channel_secret')]);

        if (!$bot->validateSignature($body, $signature)) {
            abort(400, 'Invalid LINE signature');
        }

        return $next($request);
    }
}
