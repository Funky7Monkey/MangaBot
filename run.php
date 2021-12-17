#!/usr/bin/php
<?php

use Discord\Discord;
use Discord\WebSockets\Intents;
use Discord\WebSockets\Event;
use Discord\Builders\MessageBuilder;
require_once('mangadex.php');

include __DIR__.'/vendor/autoload.php';

$config = parse_ini_file('config.ini', true);
$settings = $config['SETTINGS'];
$defaults = $config['DEFAULTS'];
print_r($config);
print_r($settings);
print_r($defaults);

$discord = new Discord([

    'token' => $settings['token'],

]);

$discord->on('ready', function ($discord){
    global $defaults;
    echo "Bot is ready" . PHP_EOL;

    $discord->guilds->fetch($defaults['guild'])->done(function ($guild) {
        global $defaults;
        $guild->channels->fetch($defaults['channel'])->done(function ($channel) {
            $channel->sendMessage(MessageBuilder::new()
                ->setContent('hello world'));
        });
    });

    $discord->on(Event::MESSAGE_CREATE, function ($message, $discord) {
        // Handle message
        echo "{$message->channel_id}: {$message->author->username}: {$message->content}" . PHP_EOL;
        if ( $message->content == "ping") {
            $message->channel->sendMessage(MessageBuilder::new()
               ->setContent('pong'));
        }
        if (str_starts_with($message->content, 'https://mangadex.org/title/')) {
            $id = explode('/', $message->content)[4];
            $manga = new Manga($id, '');
            $message->channel->sendMessage(MessageBuilder::new()
               ->setContent($manga->title . "\n" . $manga->author));
        }
    });

});

$discord->run();