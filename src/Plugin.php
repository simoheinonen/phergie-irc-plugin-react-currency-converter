<?php

namespace SimoHeinonen\Phergie\Plugin\CurrencyConverter;

use Phergie\Irc\Bot\React\AbstractPlugin;
use Phergie\Irc\Bot\React\EventQueue;
use Phergie\Irc\Event\Event;
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
        return ['command.stock' => 'stock'];
    }

    public function stock(Event $event, EventQueue $queue)
    {
        $params = $event->getCustomParams();

        if (!isset($params[0])) {
            return;
        }

        try {
            $quote = $this->yahooFinance->getQuote($params[0]);
            $msg = $quote->getSymbol() . ': ' . $quote->getRegularMarketPrice() .PHP_EOL;
        } catch (\Exception $exception) {
            return;
        }

        $channel = $event->getSource();
        $queue->ircPrivmsg($channel, $msg);
    }
}
