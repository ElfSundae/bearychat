<?php

namespace ElfSundae\BearyChat;

use JsonSerializable;

class Message implements JsonSerializable
{
    /**
     * The BearyChat client for sending message.
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
    protected $markdown;

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
     * @param  \ElfSundae\BearyChat\Client|null $client
     */
    public function __construct(Client $client = null)
    {
        if ($this->client = $client) {
            $this->configureDefaults($client->getMessageDefaults(), true);
        }
    }

    /**
     * Get the BearyChat client for sending message.
     *
     * @return \ElfSundae\BearyChat\Client
     */
    public function getClient()
    {
        return $this->client;
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
        $this->text = $text ? (string) $text : null;

        return $this;
    }

    /**
     * Set the text.
     *
     * @param  string  $text
     * @return $this
     */
    public function text($text)
    {
        return $this->setText($text);
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
        $this->notification = $notification ? (string) $notification : null;

        return $this;
    }

    /**
     * Set the notification.
     *
     * @param  string  $notification
     * @return $this
     */
    public function notification($notification)
    {
        return $this->setNotification($notification);
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
        $this->markdown = (bool) $markdown;

        return $this;
    }

    /**
     * Set the markdown.
     *
     * @param  bool $markdown
     * @return $this
     */
    public function markdown($markdown = true)
    {
        return $this->setMarkdown($markdown);
    }

    /**
     * Get the channel which the message should be sent to.
     *
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * Set the channel which the message should be sent to.
     *
     * @param  string  $channel
     * @return $this
     */
    public function setChannel($channel)
    {
        $this->removeTarget();

        if ($channel) {
            $this->channel = (string) $channel;
        }

        return $this;
    }

    /**
     * Set the channel which the message should be sent to.
     *
     * @param  string  $channel
     * @return $this
     */
    public function channel($channel)
    {
        return $this->setChannel($channel);
    }

    /**
     * Get the user which the message should be sent to.
     *
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the user which the message should be sent to.
     *
     * @param  string  $user
     * @return $this
     */
    public function setUser($user)
    {
        $this->removeTarget();

        if ($user) {
            $this->user = (string) $user;
        }

        return $this;
    }

    /**
     * Set the user which the message should be sent to.
     *
     * @param  string  $user
     * @return $this
     */
    public function user($user)
    {
        return $this->setUser($user);
    }

    /**
     * Get the target that the message should be sent to:
     * `#channel` or `@user` or null.
     *
     * @return string
     */
    public function getTarget()
    {
        if ($this->channel) {
            return '#'.$this->channel;
        }

        if ($this->user) {
            return '@'.$this->user;
        }
    }

    /**
     * Set the target (user or channel) that the message should be sent to.
     *
     * @param  string  $target  @user, #channel, channel, null
     * @return $this
     */
    public function setTarget($target)
    {
        $this->removeTarget();

        if ($target = (string) $target) {
            $mark = mb_substr($target, 0, 1);
            $to = mb_substr($target, 1);

            if ($mark === '@') {
                $this->setUser($to);
            } elseif ($mark === '#') {
                $this->setChannel($to);
            } else {
                $this->setChannel($target);
            }
        }

        return $this;
    }

    /**
     * Remove the target, then this message will be sent to
     * the webhook defined target.
     *
     * @return $this
     */
    public function removeTarget()
    {
        $this->channel = $this->user = null;

        return $this;
    }

    /**
     * Set the target.
     *
     * @param  string  $target
     * @return $this
     */
    public function target($target)
    {
        return $this->setTarget($target);
    }

    /**
     * Set the target.
     *
     * @param  string  $target
     * @return $this
     */
    public function to($target)
    {
        return $this->setTarget($target);
    }

    /**
     * Get the attachments defaults.
     *
     * @return array
     */
    public function getAttachmentDefaults()
    {
        return $this->attachmentDefaults;
    }

    /**
     * Set the attachments defaults.
     *
     * @param  array  $defaults
     * @return $this
     */
    public function setAttachmentDefaults($defaults)
    {
        $this->attachmentDefaults = (array) $defaults;

        return $this;
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * Set the attachments for the message.
     *
     * @param  mixed  $attachments
     * @return $this
     */
    public function setAttachments($attachments)
    {
        $this->removeAttachments();

        if (is_array($attachments)) {
            foreach ($attachments as $attachment) {
                $this->addAttachment($attachment);
            }
        }

        return $this;
    }

    /**
     * Set the attachments for the message.
     *
     * @param  mixed  $attachments
     * @return $this
     */
    public function attachments($attachments)
    {
        return $this->setAttachments($attachments);
    }

    /**
     * Add an attachment to the message.
     *
     * The parameter can be an payload array that contains all of attachment's fields.
     * The parameters can also be attachment's fields that in order of
     * text, title, images and color. Except the text, other parameters
     * can be ignored.
     *
     * @param  mixed  $attachment
     * @return $this
     */
    public function addAttachment($attachment)
    {
        if (! is_array($attachment)) {
            $attachment = call_user_func_array([$this, 'getAttachmentPayload'], func_get_args());
        } elseif (
            ! empty($attachment['images']) &&
            $images = $this->getImagesPayload($attachment['images'])
        ) {
            $attachment['images'] = $images;
        }

        if (! empty($attachment)) {
            $attachment += $this->attachmentDefaults;

            $this->attachments[] = $attachment;
        }

        return $this;
    }

    /**
     * Get payload for an attachment.
     *
     * @param  mixed  $text
     * @param  mixed  $title
     * @param  mixed  $images
     * @param  mixed  $color
     * @return array
     */
    protected function getAttachmentPayload($text = null, $title = null, $images = null, $color = null)
    {
        $attachment = [];

        if ($text) {
            $attachment['text'] = $this->stringValue($text);
        }

        if ($title) {
            $attachment['title'] = $this->stringValue($title);
        }

        if ($images = $this->getImagesPayload($images)) {
            $attachment['images'] = $images;
        }

        if ($color) {
            $attachment['color'] = (string) $color;
        }

        return $attachment;
    }

    /**
     * Get payload for images.
     *
     * @param  mixed  $value
     * @return array
     */
    protected function getImagesPayload($value)
    {
        $images = [];

        foreach ((array) $value as $img) {
            if (! empty($img['url'])) {
                $img = $img['url'];
            }

            if (is_string($img) && ! empty($img)) {
                $images[] = ['url' => $img];
            }
        }

        return $images;
    }

    /**
     * Convert any type to string.
     *
     * @param  mixed  $value
     * @param  int  $jsonOptions
     * @return string
     */
    protected function stringValue($value, $jsonOptions = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    {
        if (is_string($value)) {
            return $value;
        }

        if (method_exists($value, '__toString')) {
            return (string) $value;
        }

        if (method_exists($value, 'toArray')) {
            $value = $value->toArray();
        }

        return json_encode($value, $jsonOptions);
    }

    /**
     * Add an attachment to the message.
     * It alias to `addAttachment`.
     *
     * @return $this
     */
    public function add()
    {
        return call_user_func_array([$this, 'addAttachment'], func_get_args());
    }

    /**
     * Add an image attachment to the message.
     *
     * @param  string|string[]  $image
     * @param  string  $desc
     * @param  string  $title
     * @return $this
     */
    public function addImage($image, $desc = null, $title = null)
    {
        return $this->addAttachment($desc, $title, $image);
    }

    /**
     * Remove attachment[s] for the message.
     *
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

    /**
     * Remove attachment[s] for the message.
     * It alias to `removeAttachments`.
     *
     * @return $this
     */
    public function remove()
    {
        return call_user_func_array([$this, 'removeAttachments'], func_get_args());
    }

    /**
     * Configure message defaults.
     *
     * @param  array  $defaults
     * @param  bool  $force
     * @return $this
     */
    public function configureDefaults(array $defaults, $force = false)
    {
        if (! $force && ! empty($this->toArray())) {
            return $this;
        }

        $attachmentDefaults = $this->attachmentDefaults;

        foreach (MessageDefaults::allKeys() as $key) {
            if (! isset($defaults[$key]) || is_null($value = $defaults[$key])) {
                continue;
            }

            if (strpos($key, 'attachment_') !== false) {
                if ($key = substr($key, strlen('attachment_'))) {
                    $attachmentDefaults[$key] = $value;
                }
            } else {
                if (! is_null($this->getTarget()) &&
                    ($key == MessageDefaults::USER || $key == MessageDefaults::CHANNEL)
                ) {
                    continue;
                }

                if ($suffix = $this->studlyCase($key)) {
                    $getMethod = 'get'.$suffix;
                    $setMethod = 'set'.$suffix;
                    if (
                        method_exists($this, $getMethod) &&
                        is_null($this->{$getMethod}()) &&
                        method_exists($this, $setMethod)
                    ) {
                        $this->{$setMethod}($value);
                    }
                }
            }
        }

        if ($attachmentDefaults != $this->attachmentDefaults) {
            $this->attachmentDefaults = $attachmentDefaults;
            $this->setAttachments($this->attachments);
        }

        return $this;
    }

    /**
     * Convert a string to studly caps case.
     *
     * @param  string  $string
     * @return string
     */
    protected function studlyCase($string)
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $string)));
    }

    /**
     * Conveniently set message content.
     *
     * The parameters may be:
     * `($text, $markdown, $notification)`
     * or `($text, $attachment_text, $attachment_title, $attachment_images, $attachment_color)`.
     *
     * @return $this
     */
    public function content()
    {
        $arguments = func_get_args();
        $count = count($arguments);

        if ($count > 0) {
            $this->setText($arguments[0]);
        }

        if ($count > 1) {
            if (is_bool($arguments[1])) {
                $this->setMarkdown($arguments[1]);

                if ($count > 2) {
                    $this->setNotification($arguments[2]);
                }
            } else {
                call_user_func_array([$this, 'addAttachment'], array_slice($arguments, 1));
            }
        }

        return $this;
    }

    /**
     * Convert the message to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return array_filter(
            [
                'text' => $this->getText(),
                'notification' => $this->getNotification(),
                'markdown' => $this->getMarkdown(),
                'channel' => $this->getChannel(),
                'user' => $this->getUser(),
                'attachments' => $this->getAttachments(),
            ],
            function ($value, $key) {
                return ! (
                    is_null($value) ||
                    ($key === 'markdown' && $value === true) ||
                    (is_array($value) && empty($value))
                );
            },
            ARRAY_FILTER_USE_BOTH
        );
    }

    /**
     * Convert the message to JSON string.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Serializes the object to a value that can be serialized natively by json_encode().
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Send the message.
     *
     * The parameters accepts the same format of `content` method.
     *
     * @return bool
     */
    public function send()
    {
        if (! $this->client) {
            return false;
        }

        if (
            1 == func_num_args() &&
            (is_array(func_get_arg(0)) || is_object(func_get_arg(0)))
        ) {
            return $this->client->sendMessage(func_get_arg(0));
        }

        if (func_num_args() > 0) {
            call_user_func_array([$this, 'content'], func_get_args());
        }

        return $this->client->sendMessage($this);
    }

    /**
     * Send the message to the given target.
     *
     * @param  mixed  $target
     * @return bool
     */
    public function sendTo($target)
    {
        $this->to($target);

        return call_user_func_array([$this, 'send'], array_slice(func_get_args(), 1));
    }

    /**
     * Convert the message to its string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }
}
