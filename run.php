#!/usr/bin/php
<?php

use Discord\Discord;
use Discord\WebSockets\Intents;
use Discord\WebSockets\Event;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Embed\Embed;
require_once('mangadex.php');

include __DIR__.'/vendor/autoload.php';

$config = parse_ini_file('config.ini', true);
$settings = $config['SETTINGS'];
$defaults = $config['DEFAULTS'];
print_r($config);

$discord = new Discord([

    'token' => $settings['token'],

]);

function buildEmbed($manga, $discord) {
    $tags = implode(", ", $manga->tags);
    $embed = new Embed($discord);
    $embed->setTitle($manga->title)->setType('rich')
        ->setColor('#000FFF')->setFooter($manga->status)
        ->setURL($manga->url)->setImage($manga->cover_url)
        ->addFieldValues('Author', $manga->author, true)
        ->addFieldValues('Demographic', $manga->demographic, true)
        ->addFieldValues('Rating', $manga->rating, true)
        ->addFieldValues('Tags', $tags);

    return $embed;
}

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
            $manga = new Manga('id', $id);
            
            $embed = buildEmbed($manga, $discord);

            $message->channel->sendMessage(MessageBuilder::new()
                ->addEmbed($embed));
        }
        if (str_starts_with($message->content, 'https://mangadex.org/chapter/')) {
            $id = explode('/', $message->content)[4];
            $manga = new Manga('chapter', $id);
            
            $embed = buildEmbed($manga, $discord);

            $message->channel->sendMessage(MessageBuilder::new()
                ->addEmbed($embed));
        }
    });

});

$discord->run();