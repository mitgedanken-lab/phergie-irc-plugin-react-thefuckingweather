<?php
/**
 * Phergie (http://phergie.org)
 *
 * @link https://github.com/phergie/phergie-irc-plugin-react-thefuckingweather for the canonical source repository
 * @copyright Copyright (c) 2008-2014 Phergie Development Team (http://phergie.org)
 * @license http://phergie.org/license New BSD License
 * @package Phergie\Irc\Plugin\React\TheFuckingWeather
 */

namespace Phergie\Irc\Plugin\React\TheFuckingWeather;

use Phergie\Irc\Bot\React\AbstractPlugin;
use Phergie\Irc\Bot\React\EventQueueInterface as Queue;
use Phergie\Irc\Plugin\React\Command\CommandEvent as Event;
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
            // 'command.thefuckingweather.help' => 'handleHelp',
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
        preg_match_all(
            '#<div><span class="small">(.*?)<\/span><\/div>#im',
            $data,
            $matches
        );
        $location = $matches[1][0];

        if (empty($location)) {
            return 'No fucking clue where that is.';
        }

        preg_match_all(
            '#<div class="large" >(.*?)<br \/>#im',
            $data,
            $matches
        );
        $numbers = (int) $matches[1][0];
        $numbers .= ' F / ' . round(($numbers - 32) / 1.8, 0) . ' C?!';

        preg_match_all(
            '#<br \/>(.*?)<\/div><div  id="remark"><br \/>#im',
            $data,
            $matches
        );
        $description = $matches[1][0];

        preg_match_all(
            '#<div  id="remark"><br \/>\n<span>(.*?)<\/span><\/div>#im',
            $data,
            $matches
        );
        $remark = $matches[1][0];

        $result = "{$location}: {$numbers} {$description} ({$remark})";
        $result = str_replace('<', ' <', $result);
        $result = strip_tags($result);
        return html_entity_decode($result);
    }
}
