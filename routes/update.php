<?php

$updateDispatcher->addCommand('/echo', function ($commandObj, $request) use ($botApi) {
    $chatID = $request->message->chat->id;
    $text = trim(substr($request->message->text, $commandObj->length));
    if (!empty($text)) {
        $botApi->sendMessage($chatID, $text);
    }
});

$updateDispatcher->addHandler('callback_query', function ($type, $request) use ($botApi, $logger) {
    $botApi->sendMessage($request->callback_query->chat->id, 'Thanks');
});

