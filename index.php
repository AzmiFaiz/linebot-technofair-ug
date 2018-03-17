<?php
require __DIR__ . '/vendor/autoload.php';

use \LINE\LINEBot;
use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use \LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use \LINE\LINEBot\SignatureValidator as SignatureValidator;
use \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use \LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;
use \LINE\LINEBot\MessageBuilder\ImageMessageBuilder;

// set false for production
$pass_signature = true;

// set LINE channel_access_token and channel_secret
$channel_access_token = "fzRrLdC64VogCXR5Jo6DRxkOcuq6hocG5ZB+YvNejNk4Xj0kTcwOxQkZ/Y6LWLjQfB6PE196LPV08V6j3i92CxKa6+Amu0/Jx6m3uLFgg2vJYXkc9feDbvmq/Ok9iFr5ADhPiE6El5EuQXuXFHLWPQdB04t89/1O/w1cDnyilFU=";
$channel_secret = "4bbd887550a7cde38a4c60978eafadf1";

// inisiasi objek bot
$httpClient = new CurlHTTPClient($channel_access_token);
$bot = new LINEBot($httpClient, ['channelSecret' => $channel_secret]);

$configs =  [
    'settings' => ['displayErrorDetails' => true],
];
$app = new Slim\App($configs);

// buat route untuk url homepage
$app->get('/', function($req, $res)
{   
  echo "Welcome at Slim Framework";
});

// buat route untuk webhook
$app->post('/webhook', function ($request, $response) use ($bot, $pass_signature)
{
    // get request body and line signature header
    $body        = file_get_contents('php://input');
    $signature = isset($_SERVER['HTTP_X_LINE_SIGNATURE']) ? $_SERVER['HTTP_X_LINE_SIGNATURE'] : '';

    // log body and signature
    file_put_contents('php://stderr', 'Body: '.$body);

    if($pass_signature === false)
    {
        // is LINE_SIGNATURE exists in request header?
        if(empty($signature)){
            return $response->withStatus(400, 'Signature not set');
        }

        // is this request comes from LINE?
        if(! SignatureValidator::validateSignature($body, $channel_secret, $signature)){
            return $response->withStatus(400, 'Invalid signature');
        }
    }

    // kode aplikasi nanti disini
    $data = json_decode($body, true);
if(is_array($data['events'])){
    foreach ($data['events'] as $event)
    {
        if ($event['type'] == 'message')
        {
            if($event['message']['type'] == 'text')
            {   
                if(strtolower(trim($event['message']['text'])) == 'sayang mau curhat'){
                    $packageId = 1;
                    $stickerId = 104;
                    $stickerMessageBuilder = new StickerMessageBuilder($packageId, $stickerId);
                    $result = $bot->replyMessage($event['replyToken'], $stickerMessageBuilder);
                }elseif(strtolower(trim($event['message']['text'])) == 'sedih deh hari ini'){
                    $result = $bot->replyText($event['replyToken'], 'Sedih kenapa sayang?');
                }else{
                    $result = $bot->replyText($event['replyToken'], 'Sedih kenapa sayang?');
                }
                // if($event['message']['text'] == 'hei'){
                //     $result = $bot->replyText($event['replyToken'], "Hai bro");
                // }
                // send same message as reply to user
                //  $result = $bot->replyText($event['replyToken'], $event['message']['text']);
                // $result = $bot->replyText($event['replyToken'], "Hai bro".$event);
                
 //bit.ly/stickerlistccc
                // or we can use replyMessage() instead to send reply message
                // $textMessageBuilder = new TextMessageBuilder($event['message']['text']);
                // $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);
 
                return $response->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
            }elseif(
                    $event['message']['type'] == 'image' or
                    $event['message']['type'] == 'video' or
                    $event['message']['type'] == 'audio' or
                    $event['message']['type'] == 'file'
                ){
                    $basePath  = $request->getUri()->getBaseUrl();
                    $contentURL  = $basePath."/content/".$event['message']['id'];
                    $contentType = ucfirst($event['message']['type']);
                    $result = $bot->replyText($event['replyToken'],
                        $contentType. " yang kamu kirim bisa diakses dari link:\n " . $contentURL);

                    return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
                }else{
                            //kalau chatnya selain image video audio file text
                            

                }
            
        }
    }
}   
});

$app->run();
