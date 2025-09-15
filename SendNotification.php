<?php

class SendNotification
{
    private string $option_name = 'postication_bot_settings';
    private string $table_name = 'postication_telegram_messages';

    public function __construct()
    {
        add_action('save_post', [$this, 'scheduleNotification'], 10, 3);
        add_action('delete_post', [$this, 'deletedPost'], 10, 3);
        add_action('send_telegram_notification', [$this, 'sendNotification'], 10, 2);
    }

    public function scheduleNotification($postId, WP_Post $post, $update): void
    {
        if (wp_is_post_autosave($postId) || wp_is_post_revision($postId)) {
            return;
        }

        if ($post->post_type !== 'post' || $post->post_status !== 'publish') {
            return;
        }

        wp_schedule_single_event(time() + 1, 'send_telegram_notification', [$postId, $update]);
    }

    public function sendNotification(int $postId, bool $update): void
    {
        $post = get_post($postId);
        if (!$post) return;

        $opts = get_option($this->option_name, []);
        $token = !empty($opts['bot_token']) ? esc_attr($opts['bot_token']) : null;
        $channelChat = !empty($opts['channel_chat']) ? esc_attr($opts['channel_chat']) : null;

        if (!$token || !$channelChat) return;

        $is_update = ($post->post_date !== $post->post_modified);

        if ($is_update) {
            if (empty($opts['notify_update'])) return;
            $this->updatedPost($postId, $post, $token, $channelChat);
        } else {
            if (empty($opts['notify_create'])) return;
            $this->createdPost($postId, $post, $token, $channelChat);
        }
    }

    public function createdPost(int $postId, WP_Post $post, string $token, string|int $channelChat): void
    {
        $thumbnail_url = get_the_post_thumbnail_url($postId, 'full');

        if (empty($post->post_excerpt)) {
            $excerpt = wp_trim_words($post->post_content, 30, '...');
        } else {
            $excerpt = $post->post_excerpt;
        }

        $postLink = get_permalink($postId);
        $tags = wp_get_post_tags($postId, ['fields' => 'names']);

        $hashtags = '. ';

        if ($tags) {
            foreach ($tags as $tag) {
                $tag_clean = preg_replace('/\s+/', '', $tag);
                $hashtags .= '#' . $tag_clean . ' ';
            }
        }

        $text = "📚 پست جدید منتشر شد." . PHP_EOL . PHP_EOL;
        $text .= "<b>" . $post->post_title . "</b>" . PHP_EOL . PHP_EOL;
        $text .= $excerpt;
        $text .= "<a href='" . $postLink . "'>ادامه مطلب</a>" . PHP_EOL . PHP_EOL;
        $text .= $hashtags;

        $keyboard = json_encode([
            'inline_keyboard' => [
                [['text' => "🔗 مشاهده مطلب", 'url' => $postLink]]
            ]
        ]);

        if ($thumbnail_url) {
            $response = wp_remote_post("https://api.telegram.org/bot{$token}/sendPhoto", [
                'body' => [
                    'chat_id' => $channelChat,
                    'photo' => $thumbnail_url,
                    'caption' => $text,
                    'parse_mode' => 'HTML',
                    'reply_markup' => $keyboard
                ]
            ]);
        } else {
            $response = wp_remote_post("https://api.telegram.org/bot{$token}/sendMessage", [
                'body' => [
                    'chat_id' => $channelChat,
                    'text' => $text,
                    'parse_mode' => 'HTML',
                    'reply_markup' => $keyboard
                ]
            ]);
        }

        if (!is_wp_error($response)) {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if (!empty($data['ok']) && !empty($data['result']['message_id'])) {
                $message_id = $data['result']['message_id'];
                global $wpdb;
                $wpdb->insert(
                    $wpdb->prefix . $this->table_name,
                    [
                        'post_id' => $postId,
                        'chat_id' => $channelChat,
                        'message_id' => $message_id,
                        'sent_at' => current_time('mysql')
                    ]
                );
            }
        }
    }

    public function updatedPost(int $postId, WP_Post $post, string $token, string|int $channelChat): void
    {
        global $wpdb;
        $table = $wpdb->prefix . $this->table_name;

        $message = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE post_id = %d AND chat_id = %s ORDER BY id DESC LIMIT 1",
                $postId,
                $channelChat
            )
        );

        if ($message) {
            $message_id = $message->message_id;

            if (empty($post->post_excerpt)) {
                $excerpt = wp_trim_words($post->post_content, 30, '...');
            } else {
                $excerpt = $post->post_excerpt;
            }

            $postLink = get_permalink($postId);
            $tags = wp_get_post_tags($postId, ['fields' => 'names']);

            $hashtags = '. ';

            if ($tags) {
                foreach ($tags as $tag) {
                    $tag_clean = preg_replace('/\s+/', '', $tag);
                    $hashtags .= '#' . $tag_clean . ' ';
                }
            }

            $thumbnail_url = get_the_post_thumbnail_url($postId, 'full');

            $keyboard = json_encode([
                'inline_keyboard' => [
                    [['text' => "🔗 مشاهده مطلب", 'url' => $postLink]]
                ]
            ]);

            $text = "📚 پست جدید منتشر شد." . PHP_EOL . PHP_EOL;
            $text .= "<b>" . $post->post_title . "</b>" . PHP_EOL . PHP_EOL;
            $text .= $excerpt;
            $text .= "<a href='" . $postLink . "'>ادامه مطلب</a>" . PHP_EOL . PHP_EOL;
            $text .= $hashtags;

            if ($thumbnail_url) {
                wp_remote_post("https://api.telegram.org/bot{$token}/editMessageCaption", [
                    'body' => [
                        'chat_id' => $channelChat,
                        'message_id' => $message_id,
                        'caption' => $text,
                        'parse_mode' => 'HTML',
                        'reply_markup' => $keyboard
                    ]
                ]);
            } else {
                wp_remote_post("https://api.telegram.org/bot{$token}/editMessageText", [
                    'body' => [
                        'chat_id' => $channelChat,
                        'message_id' => $message_id,
                        'text' => $text,
                        'parse_mode' => 'HTML',
                        'reply_markup' => $keyboard
                    ]
                ]);
            }
        }
    }

    public function deletedPost(int $post_id, WP_Post $post): void
    {
        $opts = get_option($this->option_name, []);

        $token = !empty($opts['bot_token']) ? esc_attr($opts['bot_token']) : null;
        $channelChat = !empty($opts['channel_chat']) ? esc_attr($opts['channel_chat']) : null;

        if (!$token || !$channelChat) return;

        global $wpdb;
        $table = $wpdb->prefix . $this->table_name;

        $tgPosts = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE post_id = %d ORDER BY id DESC",
                $post_id
            )
        );

        if ($tgPosts) {
            foreach ($tgPosts as $tgPost) {
                $message_id = $tgPost->message_id;
                $chat_id = $tgPost->chat_id;

                if (!empty($opts['notify_delete'])) {
                    wp_remote_post("https://api.telegram.org/bot{$token}/deleteMessage", [
                        'body' => [
                            'chat_id' => $chat_id,
                            'message_id' => $message_id
                        ]
                    ]);
                };

                $wpdb->delete($table, ['id' => $tgPost->id]);
            }
        }
    }
}
