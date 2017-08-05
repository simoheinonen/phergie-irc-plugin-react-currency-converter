<?php

namespace SimoHeinonen\Phergie\Plugin\CurrencyConverter;

use Phergie\Irc\Bot\React\AbstractPlugin;
use Phergie\Irc\Bot\React\EventQueue;
use Phergie\Irc\Event\Event;
use Phergie\Irc\Event\UserEvent;
use Scheb\YahooFinanceApi\ApiClient;

class Plugin extends AbstractPlugin
{
    private $yahooFinance;

    public function __construct(ApiClient $yahooFinance)
    {
        $this->yahooFinance = $yahooFinance;
    }

    public function getSubscribedEvents()
    {
        return ['command.currency' => 'convertCurrency'];
    }

    public function convertCurrency(Event $event, EventQueue $queue)
    {
        $params = $event->getCustomParams();

        if (!isset($params[0], $params[1])) {
            return;
        }

        try {
            $exchangeRate = $this->yahooFinance->getExchangeRate($params[0], $params[1]);
            $msg = $exchangeRate->getName() . ': ' . $exchangeRate->getRate() .PHP_EOL;
        } catch (\Exception $exception) {
            $msg = 'Error';
        }

        $channel = $event->getSource();
        $queue->ircPrivmsg($channel, $msg);
    }
}

