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
            if (!$quote) {
                return;
            }

            $currencyMap = [
                'EUR' => '€',
                'USD' => '$',
            ];

            $msg = sprintf(
                '%s (%s)',
                $quote->getShortName(),
                $quote->getSymbol()
            );

            $vari = $quote->getRegularMarketChange() < 0 ? '4' : '3';
            $regularMarketMsg = sprintf(
                ' | %s%s%s %s%s (%s%%)%s vol: %s',
                chr(0x02),
                ($currencyMap[$quote->getCurrency()] ?? '') . round($quote->getRegularMarketPrice(), 3),
                chr(0x02),
                (chr(0x03). $vari),
                $this->formatNum(round($quote->getRegularMarketChange(), 3)),
                round($quote->getRegularMarketChangePercent(), 3),
                chr(0x03),
                $this->fancyNumber($quote->getRegularMarketVolume())
            );

            $preMarketMsg = '';
            if ($quote->getPreMarketPrice()) {
                $vari = $quote->getPreMarketPrice() < 0 ? '4' : '3';
                $preMarketMsg = sprintf(
                    ' | Before hours: %s %s%s (%s%%)%s',
                    ($currencyMap[$quote->getCurrency()] ?? '') . round($quote->getPreMarketPrice(), 3),
                    (chr(0x03). $vari),
                    $this->formatNum(round($quote->getPreMarketChange(), 3)),
                    round($quote->getPreMarketChangePercent(), 3),
                    chr(0x03)
                );
            }

            $afterMarketMsg = '';
            if ($quote->getPostMarketPrice()) {
                $vari = $quote->getPostMarketPrice() < 0 ? '4' : '3';
                $afterMarketMsg = sprintf(
                    ' | After hours: %s %s%s (%s%%)%s',
                    ($currencyMap[$quote->getCurrency()] ?? '') . round($quote->getPostMarketPrice(), 3),
                    (chr(0x03). $vari),
                    $this->formatNum(round($quote->getPostMarketChange(), 3)),
                    round($quote->getPostMarketChangePercent(), 3),
                    chr(0x03)
                );
            }

            $mesegsg = $msg . $regularMarketMsg . $preMarketMsg . $afterMarketMsg. PHP_EOL;
        } catch (\Exception $exception) {
            return;
        }

        $channel = $event->getSource();
        $queue->ircPrivmsg($channel, $mesegsg);
    }

    private function fancyNumber($n){
        if ($n < 1000000) {
            // Anything less than a million
            $n_format = number_format($n);
        } else if ($n < 1000000000) {
            // Anything less than a billion
            $n_format = number_format($n / 1000000, 3) . 'M';
        } else {
            // At least a billion
            $n_format = number_format($n / 1000000000, 3) . 'B';
        }
        return $n_format;
    }

    private function formatNum($num){
        if ($num > 0) {
            return '+'.$num;
        }

        return $num;
    }

}
