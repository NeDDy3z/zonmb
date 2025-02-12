<?php

namespace React\Socket;

use Evenement\EventEmitter;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use Exception;





final class Server extends EventEmitter implements ServerInterface
{
private $server;



































public function __construct($uri, $loop = null, array $context = array())
{
if ($loop !== null && !$loop instanceof LoopInterface) { 
throw new \InvalidArgumentException('Argument #2 ($loop) expected null|React\EventLoop\LoopInterface');
}

$loop = $loop ?: Loop::get();


if ($context && (!isset($context['tcp']) && !isset($context['tls']) && !isset($context['unix']))) {
$context = array('tcp' => $context);
}


$context += array(
'tcp' => array(),
'tls' => array(),
'unix' => array()
);

$scheme = 'tcp';
$pos = \strpos($uri, '://');
if ($pos !== false) {
$scheme = \substr($uri, 0, $pos);
}

if ($scheme === 'unix') {
$server = new UnixServer($uri, $loop, $context['unix']);
} else {
$server = new TcpServer(str_replace('tls://', '', $uri), $loop, $context['tcp']);

if ($scheme === 'tls') {
$server = new SecureServer($server, $loop, $context['tls']);
}
}

$this->server = $server;

$that = $this;
$server->on('connection', function (ConnectionInterface $conn) use ($that) {
$that->emit('connection', array($conn));
});
$server->on('error', function (Exception $error) use ($that) {
$that->emit('error', array($error));
});
}

public function getAddress()
{
return $this->server->getAddress();
}

public function pause()
{
$this->server->pause();
}

public function resume()
{
$this->server->resume();
}

public function close()
{
$this->server->close();
}
}
