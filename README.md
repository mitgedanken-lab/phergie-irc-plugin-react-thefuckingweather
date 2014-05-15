# phergie/phergie-irc-plugin-react-thefuckingweather

[Phergie](http://github.com/phergie/phergie-irc-bot-react/) plugin for providing weather information from thefuckingweather.com.

[![Build Status](https://secure.travis-ci.org/phergie/phergie-irc-plugin-react-thefuckingweather.png?branch=master)](http://travis-ci.org/phergie/phergie-irc-plugin-react-thefuckingweather)

## Install

The recommended method of installation is [through composer](http://getcomposer.org).

```JSON
{
    "require": {
        "phergie/phergie-irc-plugin-react-thefuckingweather": "dev-master"
    }
}
```

See Phergie documentation for more information on
[installing and enabling plugins](https://github.com/phergie/phergie-irc-bot-react/wiki/Usage#plugins).

## Configuration

This plugin has no configuration.

## Tests

To run the unit test suite:

```
curl -s https://getcomposer.org/installer | php
php composer.phar install
cd tests
../vendor/bin/phpunit
```

## License

Released under the BSD License. See `LICENSE`.
