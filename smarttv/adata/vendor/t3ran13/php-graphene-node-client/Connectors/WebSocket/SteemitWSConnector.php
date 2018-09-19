<?php

namespace GrapheneNodeClient\Connectors\WebSocket;


class SteemitWSConnector extends WSConnectorAbstract
{
    /**
     * @var string
     */
    protected $platform = self::PLATFORM_STEEMIT;

    /**
     * wss or ws server
     *
     * if you set several nodes urls, if with first node will be trouble
     * it will connect after $maxNumberOfTriesToCallApi tries to next node
     *
     * @var string
     */
    protected static $nodeURL = [
        'wss://whaleshares.io/ws'
//        'wss://steemd.minnowsupportproject.org'  //not full answers
    ];
}