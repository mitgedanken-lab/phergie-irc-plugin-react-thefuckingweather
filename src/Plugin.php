<?php
/**
 * Phergie (http://phergie.org)
 *
 * @link https://github.com/phergie/phergie-irc-plugin-react-thefuckingweather for the canonical source repository
 * @copyright Copyright (c) 2008-2014 Phergie Development Team (http://phergie.org)
 * @license http://phergie.org/license Simplified BSD License
 * @package Phergie\Irc\Plugin\React\TheFuckingWeather
 */

namespace Phergie\Irc\Plugin\React\TheFuckingWeather;

use Phergie\Irc\Bot\React\AbstractPlugin;
use Phergie\Irc\Bot\React\EventQueueInterface as Queue;
use Phergie\Irc\Plugin\React\Command\CommandEvent as Event;
use PhpUnitsOfMeasure\PhysicalQuantity\Temperature;
use WyriHaximus\Phergie\Plugin\Http\Request as HttpRequest;

/**
 * Plugin for providing weather information from thefuckingweather.com.
 *
 * @category Phergie
 * @package Phergie\Irc\Plugin\React\TheFuckingWeather
 */
class Plugin extends AbstractPlugin
{
    /**
     * Indicates that the plugin monitors events for a "thefuckingweather"
     * event emitted by the Command plugin and a corresponding event for a
     * "help" command emitted by the CommandHelp plugin.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            'command.thefuckingweather' => 'handleCommand',
            'command.thefuckingweather.help' => 'handleHelp',
        );
    }

    /**
     * Returns weather information for a given location.
     *
     * @param \Phergie\Irc\Plugin\React\Command\CommandEvent $event
     * @param \Phergie\Irc\Bot\React\EventQueueInterface $queue
     */
    public function handleCommand(Event $event, Queue $queue)
    {
        $params = $event->getCustomParams();
        if (!$params) {
            return $this->handleHelp($event, $queue);
        }

        $where = reset($params);
        $url = 'http://www.thefuckingweather.com/?where=' . urlencode($where);

        $self = $this;
        $request = new HttpRequest(array(
            'url' => $url,
            'resolveCallback' => function($data) use ($self, $event, $queue) {
                $self->resolve($data, $event, $queue);
            },
            'rejectCallback' => function() use ($self, $event, $queue) {
                $self->reject($event, $queue);
            }
        ));

        $this->getEventEmitter()->emit('http.request', array($request));
    }

    /**
     * Handles a successful request for weather information.
     *
     * @param string $data
     * @param \Phergie\Irc\Plugin\React\Command\CommandEvent $event
     * @param \Phergie\Irc\Bot\React\EventQueueInterface $queue
     */
    public function resolve($data, Event $event, Queue $queue)
    {
        $response = $this->getResponse($data);
        $target = $event->getSource();
        $nick = $event->getNick();
        if ($target != $nick) {
            $response = $nick . ': ' . $response;
        }
        $queue->ircPrivmsg($target, $response);
    }

    /**
     * Parses the response from a successful reuest for weather information and
     * constructs a response to be returned to the user who issued the original
     * command.
     *
     * @param string $data
     * @return string
     */
    protected function getResponse($data)
    {
        $doc = new \DOMDocument;
        $doc->loadHTML($data);
        $xpath = new \DOMXPath($doc);

        $location = trim($xpath->query('//span[@id="locationDisplaySpan"]')->item(0)->nodeValue);
        if (!$location) {
            return 'I CAN\'T FIND THAT SHIT.';
        }

        $result = $xpath->query('//span[@class="temperature"]');
        $element = $result->item(0);
        $tempF = $element->getAttribute('tempf');
        $tempC = $element->nodeValue;
        if ($tempF == $tempC) {
            $temp = new Temperature($tempC, 'F');
            $tempC = round($temp->toUnit('C'), 0);
        }
        $numbers = "$tempF F / $tempC C ?!";

        $remark = $xpath->query('//p[@class="remark"]')->item(0)->nodeValue;

        $flavor = $xpath->query('//p[@class="flavor"]')->item(0)->nodeValue;

        $result = "{$location}: $numbers {$remark}. $flavor";
        return $result;
    }

    /**
     * Handles a failed request for weather information.
     *
     * @param \Phergie\Irc\Plugin\React\Command\CommandEvent $event
     * @param \Phergie\Irc\Bot\React\EventQueueInterface $queue
     */
    public function reject(Event $event, Queue $queue)
    {
        $target = $event->getSource();
        $nick = $event->getNick();
        $response = 'I CAN\'T GET THE FUCKING WEATHER.';
        if ($target != $nick) {
            $response = $nick . ': ' . $response;
        }
        $queue->ircPrivmsg($target, $response);
    }

    /**
     * Returns usage information for the command.
     *
     * @param \Phergie\Irc\Plugin\React\Command\CommandEvent $event
     * @param \Phergie\Irc\Bot\React\EventQueueInterface $queue
     */
    public function handleHelp(Event $event, Queue $queue)
    {
        $method = 'irc' . $event->getCommand();
        $target = $event->getSource();
        $messages = array(
            'Usage: thefuckingweather location',
            'Returns weather information for the specified location from thefuckingweather.com',
        );
        foreach ($messages as $message) {
            $queue->$method($target, $message);
        }
    }
}
