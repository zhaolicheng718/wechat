<?php

/*
 * This file is part of the overtrue/wechat.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace EasyWeChat\Applications\OfficialAccount\CustomerService;

use EasyWeChat\Exceptions\InvalidArgumentException;
use EasyWeChat\Exceptions\RuntimeException;
use EasyWeChat\Messages\Message;
use EasyWeChat\Messages\Raw as RawMessage;
use EasyWeChat\Messages\Text;

/**
 * Class MessageBuilder.
 *
 * @author overtrue <i@overtrue.me>
 */
class MessageBuilder
{
    /**
     * Messages to send.
     *
     * @var \EasyWeChat\Messages\Message;
     */
    protected $message;

    /**
     * Messages target user open id.
     *
     * @var string
     */
    protected $to;

    /**
     * Messages sender staff id.
     *
     * @var string
     */
    protected $account;

    /**
     * Customer service instance.
     *
     * @var \EasyWeChat\Applications\OfficialAccount\CustomerService\Client
     */
    protected $customerService;

    /**
     * MessageBuilder constructor.
     *
     * @param \EasyWeChat\Applications\OfficialAccount\CustomerService\Client $customerService
     */
    public function __construct(Client $customerService)
    {
        $this->customerService = $customerService;
    }

    /**
     * Set message to send.
     *
     * @param string|Message $message
     *
     * @return MessageBuilder
     *
     * @throws InvalidArgumentException
     */
    public function message($message)
    {
        if (is_string($message)) {
            $message = new Text(['content' => $message]);
        }

        $this->message = $message;

        return $this;
    }

    /**
     * Set staff account to send message.
     *
     * @param string $account
     *
     * @return MessageBuilder
     */
    public function by($account)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Set target user open id.
     *
     * @param string $openId
     *
     * @return MessageBuilder
     */
    public function to($openId)
    {
        $this->to = $openId;

        return $this;
    }

    /**
     * Send the message.
     *
     * @return \Psr\Http\Message\ResponseInterface|\EasyWeChat\Support\Collection|array|object|string
     *
     * @throws RuntimeException
     */
    public function send()
    {
        if (empty($this->message)) {
            throw new RuntimeException('No message to send.');
        }

        $transformer = new Transformer();

        if ($this->message instanceof RawMessage) {
            $message = $this->message->get('content');
        } else {
            $content = $transformer->transform($this->message);
            $message = [
                'touser' => $this->to,
            ];

            if ($this->account) {
                $message['customservice'] = ['kf_account' => $this->account];
            }

            $message = array_merge($message, $content);
        }

        return $this->customerService->send($message);
    }

    /**
     * Return property.
     *
     * @param $property
     *
     * @return mixed
     */
    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }
}