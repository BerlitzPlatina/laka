<?php

namespace App\Kafka;

use Illuminate\Contracts\Queue\Queue as QueueConstract;
use Illuminate\Queue\Queue;
use Illuminate\Support\Facades\Log;

class KafkaQueue extends Queue implements QueueConstract
{
    protected $consumer, $producer;

    public function __construct($consumer, $producer)
    {
        $this->producer = $producer;
        $this->consumer = $consumer;
    }

    public function size($queue = null)
    {

    }
    public function push($job, $data = "", $queue = null)
    {
        Log::debug('An informational message.');

        $topic = $this->producer->newTopic($queue);
        $topic->produce(RD_KAFKA_PARTITION_UA, 0, serialize($job));
        $this->producer->flush(5000);
    }
    public function pushRaw($payload, $queue = null, array $options = array())
    {

    }
    public function later($delay, $job, $data = "", $queue = null)
    {

    }
    public function pop($queue = null)
    {
        Log::debug('An informational message.');
        $this->consumer->subscribe([$queue]);
        try {
            $message = $this->consumer->consume(130 * 1000);
            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    var_dump($message->payload);
                    $job = unserialize($message->payload);
                    $job->handle();
                    break;
                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    break;

                default:
                    throw new \Exception($message->errstr(), $message->err);
                    break;
            }
        } catch (\Exception $e) {
            Log::alert($e->getMessage());
            var_dump($e->getMessage());
        }
    }
}
