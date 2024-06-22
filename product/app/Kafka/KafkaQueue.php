<?php

namespace App\Kafka;

use Illuminate\Contracts\Queue\Queue as QueueConstract;
use Illuminate\Queue\Queue;

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
        var_dump(111);
        $this->consumer->subscribe([$queue]);
        try {
            $message = $this->consumer->consume(130 * 1000);
            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    var_dump($message->payload, 'payload');
                    $job = unserialize($message->payload);
                    $job->handle();
                    break;
                case RD_KAFKA_RESP_ERR_TIMED_OUT:
                    var_dump('Timeout');
                    break;

                default:
                    throw new \Exception($message->errstr(), $message->err);
            }
        } catch (\Exception $e) {
            var_dump($e->getMessage());
        }
    }
}
