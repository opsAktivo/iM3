<?php

namespace App\Console\Commands;

use App\Jobs\ProcessHl7Message;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Ratchet\Server\IoServer;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class MllpReceiver extends Command
{
    protected $signature = 'mllp:receive';
    protected $description = 'Start an MLLP TCP Receiver to listen for incoming messages';

    public function handle()
    {
        $host = '0.0.0.0';
        $port = 6661;

        $this->info("MLLP Receiver started on {$host}:{$port}");

        $server = IoServer::factory(new MllpHandler($this), $port, $host);
        $server->run();
    }
}

class MllpHandler implements MessageComponentInterface
{
    protected $command;

    public function __construct($command)
    {
        $this->command = $command;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        Log::info("Connection established with: " . $conn->resourceId);
        $this->command->info("Connection established with: " . $conn->resourceId);
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        // MLLP frame format
        $header = "\x0B";
        $footer = "\x1C\x0D";

        if(substr($msg, 0, 1) == $header && substr($msg, -2) == $footer) {
            $messageBody = substr($msg, 1, -2);
            $decodedMessage = mb_convert_encoding($messageBody, 'UTF-8', 'UTF-8');
            Log::info('Received message: ' . PHP_EOL . $decodedMessage);

            $this->printMessage($decodedMessage);

            $id = DB::table('vital_signs_im3_raw')->insert([
                'raw_message' => $decodedMessage,
                'received_at' => now(),
                'parsed' => false,
            ]);

            ProcessHl7Message::dispatch($id);

            $messageControlId = $this->extractMessageControlId($decodedMessage);
            $ackMessage = $this->generateAckMessage($messageControlId);

            $from->send("\x0B" . $ackMessage . "\x1C\x0D");

        } else {
            Log::warning('Invalid MLLP message format received');
            $from->send("\x0B" . "NAK" . "\x1C\x0D");

        }
    }

    private function printMessage(string $message)
    {
        $lines = explode("\r", $message);
        foreach ($lines as $line) {
            echo $line . PHP_EOL;
        }
    }

    private function extractMessageControlId($message) {
        $lines = explode("\n", $message);
        foreach ($lines as $line) {
            if (strpos($line, 'MSH') === 0) {
                $fields = explode('|', $line);
                return $fields[9] ?? '';
            }
        }
        return '';
    }

    private function generateAckMessage($messageControlId) {
        $currentDateTime = now()->format('YmdHis');
        $ackMessage = "MSH|^~\\&|||Vital Signs Monitor||{$currentDateTime}||ACK|{$messageControlId}||2.3.1\n";
        $ackMessage .= "MSA|AA|{$messageControlId}";

        return $ackMessage;
    }

    public function onClose(ConnectionInterface $conn)
    {
        Log::info("Connection closed with: " . $conn->resourceId);
        $this->command->info("Connection closed with: " . $conn->resourceId);
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        Log::error("Error occurred: " . $e->getMessage());
        $this->command->error("Error occurred: " . $e->getMessage());
        $conn->close();
    }
}
