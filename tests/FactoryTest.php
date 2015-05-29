<?php

namespace WyriHaximus\React\Tests\ChildProcess\Messenger;

use \Phake;
use WyriHaximus\React\ChildProcess\Messenger\Factory;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var React\EventLoop\LoopInterface
     */
    protected $loop;

    /**
     * @var React\ChildProcess\Process
     */
    protected $process;

    public function setUp()
    {
        $this->loop = \React\EventLoop\Factory::create();
        $this->process = Phake::mock('React\ChildProcess\Process');
        $this->process->stdin = Phake::mock('React\Stream\Stream');
        $this->process->stdout = Phake::mock('React\Stream\Stream');
        $this->process->stderr = Phake::mock('React\Stream\Stream');
    }

    public function tearDown()
    {
        unset($this->process, $this->loop);
    }

    public function testParent()
    {
        Phake::when($this->process)->isRunning(null)->thenReturn(true);
        Phake::when($this->process)->getCommand()->thenReturn('abc');

        $messengerPromise = Factory::parent($this->process, $this->loop);
        $this->assertInstanceOf('React\Promise\PromiseInterface', $messengerPromise);
        $cbFired = false;
        $messengerPromise->then(function ($messenger) use (&$cbFired) {
            $this->assertInstanceOf('WyriHaximus\React\ChildProcess\Messenger\Messenger', $messenger);
            $this->assertSame($this->process->stdin, $messenger->getStdin());
            $this->assertSame($this->process->stdout, $messenger->getStdout());
            $this->assertSame($this->process->stderr, $messenger->getStderr());

            $this->assertEquals('abc', $messenger->getCommand());

            $cbFired = true;
        });

        $this->loop->run();

        $this->assertTrue($cbFired);

        Phake::inOrder(
            Phake::verify($this->process)->isRunning(null)
        );
    }
}