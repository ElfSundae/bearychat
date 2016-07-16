<?php

namespace ElfSundae\BearyChat;

final class MessageDefaults
{
    /**
     * channel: (string)
     *
     * The default channel will be sent to.
     *
     * 默认的讨论组名称。如果有该字段并且该讨论组对于机器人创建者可见，消息会发送到指定讨论组中。
     */
    const CHANNEL = 'channel';

    /**
     * user: (string)
     *
     * The default user will be sent to.
     *
     * 默认的用户名。在没有指定 channel 的情况下，如果有该字段并且该团队中有对应用户名的成员，
     * 消息将会发送到该成员和 BearyBot 的私聊会话中。
     */
    const USER = 'user';

    /**
     * markdown: (bool, default=true)
     *
     * Indicates the text field should be parsed as markdown syntax.
     *
     * 用于控制 text 是否解析为 markdown，默认为 true。
     */
    const MARKDOWN = 'markdown';

    /**
     * notification: (string)
     *
     * The default notification will be display.
     *
     * 用于控制消息提醒的内容显示。
     */
    const NOTIFICATION = 'notification';

    /**
     * attachment_color: (string)
     *
     * The default color for the attachment's left separator.
     *
     * 用于控制 attachment 在排版时左侧的竖线分隔符颜色。
     */
    const ATTACHMENT_COLOR = 'attachment_color';
}
