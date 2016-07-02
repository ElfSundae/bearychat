<?php

namespace ElfSundae\BearyChat;

class Message
{
    /**
     * The GearyChat client for sending message.
     *
     * @var \ElfSundae\BearyChat\Client
     */
    protected $client;

    /**
     * The text to be sent with the message.
     *
     * @var string
     */
    protected $text;

    /**
     * The notification for the text.
     *
     * @var string
     */
    protected $notification;

    /**
     * Indicates the text field should be parsed as markdown syntax.
     *
     * @var bool
     */
    protected $markdown = true;

    /**
     * The channel that the message should be sent to.
     *
     * @var string
     */
    protected $channel;

    /**
     * The user that the message should be sent to.
     *
     * @var string
     */
    protected $user;

    /**
     * The attachments to be sent.
     *
     * @var array
     */
    protected $attachments = [];

    /**
     * The default values for each attachment.
     *
     * @var array
     */
    protected $attachmentDefaults = [];

    /**
     * Create a new message.
     *
     * @param  \ElfSundae\BearyChat\Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;

        $this->configureDefaults($client->getMessageDefaults());
    }

    /**
     * Get the text.
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set the text.
     *
     * @param  string  $text
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text ? (string)$text : null;

        return $this;
    }

    /**
     * Get the notification.
     *
     * @return string
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * Set the notification.
     *
     * @param  string  $notification
     * @return $this
     */
    public function setNotification($notification)
    {
        $this->notification = $notification ? (string)$notification : null;

        return $this;
    }

    /**
     * Get the markdown.
     *
     * @return bool
     */
    public function getMarkdown()
    {
        return $this->markdown;
    }

    /**
     * Set the markdown.
     *
     * @param  bool $markdown
     * @return $this
     */
    public function setMarkdown($markdown)
    {
        $this->markdown = (bool)$markdown;

        return $this;
    }

    /**
     * Enable markdown.
     *
     * @return $this
     */
    public function enableMarkdown()
    {
        return $this->setMarkdown(true);
    }

    /**
     * Disable markdown.
     *
     * @return $this
     */
    public function disableMarkdown()
    {
        return $this->setMarkdown(false);
    }

    /**
     * Get the channel.
     *
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * Set the channel.
     *
     * @param  string  $channel
     * @return $this
     */
    public function setChannel($channel)
    {
        $this->channel = $channel ? (string)$channel : null;

        return $this;
    }

    /**
     * Change the channel.
     *
     * @param  string  $channel
     * @return $this
     */
    public function toChannel($channel)
    {
        return $this->setChannel($channel);
    }

    /**
     * Get the user.
     *
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the user.
     *
     * @param  string  $user
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user ? (string)$user : null;

        return $this;
    }

    /**
     * Change the user.
     *
     * @param  string  $user
     * @return $this
     */
    public function toUser($user)
    {
        return $this->setUser($user);
    }

    /**
     * Change the target (user or channel) that the message should be sent to.
     *
     * @param  string  $target
     * @return $this
     */
    public function to($target)
    {
        if (is_null($target)) {
            $this->setChannel(null);
            $this->setUser(null);
        } else {
            $mark = mb_substr($target, 0, 1);
            $to = mb_substr($target, 1);

            if (!empty($to)) {
                if ('#' == $mark) {
                    $this->setChannel($to);
                } else if ('@' == $mark) {
                    $this->setUser($to);
                }
            }
        }

        return $this;
    }

    /**
     * Get the attachments.
     *
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * Set the attachments.
     *
     * @param  mixed  $attachments
     * @return $this
     */
    public function setAttachments(array $attachments)
    {
        $this->removeAttachments();

        foreach ($attachments as $attachment) {
            $this->addAttachment($attachment);
        }

        return $this;
    }

    /**
     * Add an attachment to message.
     *
     * @param  array  $attachment
     * @return $this
     */
    public function addAttachment($attachment)
    {
        $attachment = (array)$attachment + $this->attachmentDefaults;

        $this->attachments[] = $attachment;

        return $this;
    }

    /**
     * Add an attachment to message.
     *
     * @param  array  $attachment
     * @return $this
     */
    public function add($attachment)
    {
        return $this->addAttachment($attachment);
    }

    /**
     * Remove attachment[s] for message.
     *
     * @param  mixed
     * @return $this
     */
    public function removeAttachments()
    {
        if (func_num_args() > 0) {
            $indices = is_array(func_get_arg(0)) ? func_get_arg(0) : func_get_args();

            foreach ($indices as $index) {
                unset($this->attachments[$index]);
            }

            $this->attachments = array_values($this->attachments);
        } else {
            $this->attachments = [];
        }

        return $this;
    }

    public function remove()
    {
        return call_user_func_array([$this, 'removeAttachments'], func_get_args());
    }

    protected function configureDefaults($defaults)
    {
        if (isset($defaults[MessageDefaults::CHANNEL]))
            $this->setChannel($defaults[MessageDefaults::CHANNEL]);
        if (isset($defaults[MessageDefaults::USER]))
            $this->setUser($defaults[MessageDefaults::USER]);
        if (isset($defaults[MessageDefaults::MARKDOWN]))
            $this->setMarkdown($defaults[MessageDefaults::MARKDOWN]);
        if (isset($defaults[MessageDefaults::NOTIFICATION]))
            $this->setNotification($defaults[MessageDefaults::NOTIFICATION]);

        if (isset($defaults[MessageDefaults::ATTACHMENT_COLOR]))
            $this->attachmentDefaults['color'] = $defaults[MessageDefaults::ATTACHMENT_COLOR];
    }

    public function toArray()
    {
        return [
            'text' => $this->getText(),
            'notification' => $this->getNotification(),
            'markdown' => $this->getMarkdown(),
            'channel' => $this->getChannel(),
            'user' => $this->getUser(),
            'attachments' => $this->getAttachments(),
        ];
    }

    public function send()
    {
        return $this->client->sendMessage($this);
    }
}
