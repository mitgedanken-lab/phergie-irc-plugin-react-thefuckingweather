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

use Phake;
use Phergie\Irc\Plugin\React\Command\CommandEvent;
use Phergie\Irc\Bot\React\EventQueueInterface;

/**
 * Tests for the Plugin class.
 *
 * @category Phergie
 * @package Phergie\Irc\Plugin\React\TheFuckingWeather
 */
class PluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Instance of the class under test
     *
     * @var \Phergie\Irc\Plugin\React\TheFuckingWeather\Plugin
     */
    protected $plugin;

    /**
     * Mock event emitter
     *
     * @var \Evenement\EventEmitterInterface
     */
    protected $eventEmitter;

    /**
     * Instantiates the class under test.
     */
    protected function setUp()
    {
        $this->eventEmitter = $this->getMockEventEmitter();
        $this->plugin = new Plugin;
        $this->plugin->setEventEmitter($this->eventEmitter);
    }

    /**
     * Tests that handleCommand() returns usage information when no parameters
     * are specified.
     */
    public function testHandleCommandWithoutParams()
    {
        $event = $this->getMockCommandEvent();
        $queue = $this->getMockEventQueue();

        $this->plugin->handleCommand($event, $queue);

        Phake::verify($this->eventEmitter, Phake::times(0))
            ->emit(Phake::anyParameters());
        Phake::verify($queue, Phake::atLeast(1))
            ->ircPrivmsg('#channel', $this->isType('string'));
    }

    /**
     * Tests that handleCommand() invokes an HTTP request when parameters are
     * specified.
     */
    public function testHandleCommandWithParams()
    {
        $where = '70529';
        $event = $this->getMockCommandEvent();
        Phake::when($event)->getCustomParams()->thenReturn(array($where));
        $queue = $this->getMockEventQueue();

        $this->plugin->handleCommand($event, $queue);

        Phake::verify($this->eventEmitter)->emit('http.request', Phake::capture($params));
        $this->assertInternalType('array', $params);
        $this->assertNotEmpty($params);
        $request = reset($params);
        $this->assertInstanceOf('\WyriHaximus\Phergie\Plugin\Http\Request', $request);
        $this->assertSame('http://www.thefuckingweather.com/?where=' . $where, $request->getUrl());
    }

    /**
     * Data provider for testResolve().
     *
     * @return array
     */
    public function dataProviderResolve()
    {
        $data = array();

        $message = 'Duson, LA: 74 F / 23 C ?! IT\'S FUCKING NICE. I made today breakfast in bed.';
        $data[] = array('success', '#channel', 'nick: ' . $message);
        $data[] = array('success', 'nick', $message);

        $message = 'I CAN\'T FIND THAT SHIT.';
        $data[] = array('failure', '#channel', 'nick: ' . $message);
        $data[] = array('failure', 'nick', $message);

        return $data;
    }

    /**
     * Tests resolve().
     *
     * @param string $response
     * @param string $target
     * @param string $message
     * @dataProvider dataProviderResolve
     */
    public function testResolve($response, $target, $message)
    {
        $where = '70529';
        $event = $this->getMockCommandEvent();
        Phake::when($event)->getSource()->thenReturn($target);
        Phake::when($event)->getCustomParams()->thenReturn(array($where));
        $queue = $this->getMockEventQueue();

        $this->plugin->handleCommand($event, $queue);

        Phake::verify($this->eventEmitter)->emit('http.request', Phake::capture($params));
        $this->assertInternalType('array', $params);
        $this->assertNotEmpty($params);
        $request = reset($params);
        $this->assertInstanceOf('\WyriHaximus\Phergie\Plugin\Http\Request', $request);
        $response = file_get_contents(__DIR__ . '/_files/' . $response . '.html');
        $request->callResolve($response, array(), 200);

        Phake::verify($queue)->ircPrivmsg($target, $message);
    }

    /**
     * Data provider for testReject().
     *
     * @return array
     */
    public function dataProviderReject()
    {
        $data = array();
        $message = 'I CAN\'T GET THE FUCKING WEATHER.';
        $data[] = array('#channel', 'nick: ' . $message);
        $data[] = array('nick', $message);
        return $data;
    }

    /**
     * Tests reject().
     *
     * @param string $target
     * @param string $message
     * @dataProvider dataProviderReject
     */
    public function testReject($target, $message)
    {
        $where = '70529';
        $event = $this->getMockCommandEvent();
        Phake::when($event)->getSource()->thenReturn($target);
        Phake::when($event)->getCustomParams()->thenReturn(array($where));
        $queue = $this->getMockEventQueue();

        $this->plugin->handleCommand($event, $queue);

        Phake::verify($this->eventEmitter)->emit('http.request', Phake::capture($params));
        $this->assertInternalType('array', $params);
        $this->assertNotEmpty($params);
        $request = reset($params);
        $this->assertInstanceOf('\WyriHaximus\Phergie\Plugin\Http\Request', $request);
        $request->callReject('error');

        Phake::verify($queue)->ircPrivmsg($target, $message);
    }

    /**
     * Tests that handleHelp() returns usage information.
     */
    public function testHandleHelp()
    {
        $event = $this->getMockCommandEvent();
        $queue = $this->getMockEventQueue();

        $this->plugin->handleHelp($event, $queue);

        Phake::verify($this->eventEmitter, Phake::times(0))
            ->emit(Phake::anyParameters());
        Phake::verify($queue, Phake::atLeast(1))
            ->ircPrivmsg('#channel', $this->isType('string'));
    }

    /**
     * Tests that getSubscribedEvents() returns an array.
     */
    public function testGetSubscribedEvents()
    {
        $plugin = new Plugin;
        $this->assertInternalType('array', $plugin->getSubscribedEvents());
    }

    /**
     * Returns a mock event emitter.
     *
     * @return \Evenement\EventEmitterInterface
     */
    protected function getMockEventEmitter()
    {
        return Phake::mock('\Evenement\EventEmitterInterface');
    }

    /**
     * Returns a mock command event.
     *
     * @return \Phergie\Irc\Plugin\React\Command\CommandEvent
     */
    protected function getMockCommandEvent()
    {
        $event = Phake::mock('\Phergie\Irc\Plugin\React\Command\CommandEvent');
        Phake::when($event)->getSource()->thenReturn('#channel');
        Phake::when($event)->getCommand()->thenReturn('PRIVMSG');
        Phake::when($event)->getNick()->thenReturn('nick');
        Phake::when($event)->getCustomParams()->thenReturn(array());
        return $event;
    }

    /**
     * Returns a mock event queue.
     *
     * @return \Phergie\Irc\Bot\React\EventQueueInterface
     */
    protected function getMockEventQueue()
    {
        return Phake::mock('\Phergie\Irc\Bot\React\EventQueueInterface');
    }
}
