# Currency converter plugin for [Phergie](http://github.com/phergie/phergie-irc-bot-react/)

## Installation

```
composer require simoheinonen/phergie-irc-plugin-react-currency-converter:dev-master
```

## Configuration

```php
return [
    'plugins' => [
        new \Phergie\Irc\Plugin\React\Command\Plugin(['prefix' => '!']), // dependency
        new \SimoHeinonen\Phergie\Plugin\CurrencyConverter\Plugin(\Scheb\YahooFinanceApi\ApiClientFactory::createApiClient()),
    ]
];
```
