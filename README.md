# This project is abandoned

This repo is being kept for posterity and will be archived in a readonly state. 
If you're interested it can be forked under a new Composer namespace/GitHub organization.

# phergie/phergie-irc-plugin-react-thefuckingweather

[Phergie](http://github.com/phergie/phergie-irc-bot-react/) plugin for providing weather information from thefuckingweather.com.

[![Build Status](https://secure.travis-ci.org/phergie/phergie-irc-plugin-react-thefuckingweather.png?branch=master)](http://travis-ci.org/phergie/phergie-irc-plugin-react-thefuckingweather)

## Install

The recommended method of installation is [through composer](http://getcomposer.org).

```
composer require phergie/phergie-irc-plugin-react-thefuckingweather
```

See Phergie documentation for more information on
[installing and enabling plugins](https://github.com/phergie/phergie-irc-bot-react/wiki/Usage#plugins).

## Configuration

This plugin has no configuration, but does require the Http plugin.

```php
return array(
    'plugins' => array(
        // dependency
        new \WyriHaximus\Phergie\Plugin\Http\Plugin,

        new \Phergie\Irc\Plugin\React\TheFuckingWeather,
    )
);
```

## Tests

To run the unit test suite:

```
curl -s https://getcomposer.org/installer | php
php composer.phar install
./vendor/bin/phpunit
```

## License

Released under the BSD License. See `LICENSE`.
