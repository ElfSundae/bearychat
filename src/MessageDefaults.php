<?php

namespace ElfSundae\BearyChat;

use ReflectionClass;

final class MessageDefaults
{
    /**
     * The default channel will be sent to.
     */
    const CHANNEL = 'channel';

    /**
     * The default user will be sent to.
     */
    const USER = 'user';

    /**
     * Indicates the text field should be parsed as markdown syntax.
     * Default is true.
     */
    const MARKDOWN = 'markdown';

    /**
     * The default notification will be display.
     */
    const NOTIFICATION = 'notification';

    /**
     * The default color for the attachments left separator.
     */
    const ATTACHMENT_COLOR = 'attachment_color';

    /**
     * Get the all possible keys.
     *
     * @return string[]
     */
    public static function allKeys()
    {
        static $allKeys = null;

        if (is_null($allKeys)) {
            $allKeys = array_values((new ReflectionClass(get_called_class()))->getConstants());
        }

        return $allKeys;
    }
}
