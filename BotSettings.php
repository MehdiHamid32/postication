<?php
if (!defined('ABSPATH')) exit;

class BotSettings
{
    private string $option_name = 'postication_bot_settings';

    public function __construct()
    {
        add_action('admin_menu', [$this, 'admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function admin_menu(): void
    {
        add_menu_page(
            'تنظیمات ربات تلگرام',
            'تنظیمات ربات تلگرام',
            'manage_options',
            'telegram-bot',
            [$this, 'settings_page'],
            'dashicons-admin-generic',
            80
        );
    }

    public function register_settings(): void
    {
        register_setting('tcb_options_group', $this->option_name, ['sanitize_callback' => [$this, 'sanitize_settings']]);

        add_settings_section('tcb_main', null, null, 'telegram-bot');
        add_settings_field('bot_token', "توکن ربات تلگرام", [$this, 'field_bot_token'], 'telegram-bot', 'tcb_main');
        add_settings_field('channel_chat', 'آیدی عددی کانال', [$this, 'field_channel_chat'], 'telegram-bot', 'tcb_main');

        add_settings_field('notify_create', 'ارسال نوتیفیکیشن هنگام ایجاد', [$this, 'field_notify_create'], 'telegram-bot', 'tcb_main');
        add_settings_field('notify_update', 'ارسال نوتیفیکیشن هنگام آپدیت', [$this, 'field_notify_update'], 'telegram-bot', 'tcb_main');
        add_settings_field('notify_delete', 'ارسال نوتیفیکیشن هنگام حذف', [$this, 'field_notify_delete'], 'telegram-bot', 'tcb_main');
    }

    public function sanitize_settings($input): array
    {
        $output = [];

        $output['bot_token']     = isset($input['bot_token']) ? sanitize_text_field($input['bot_token']) : '';
        $output['channel_chat']  = isset($input['channel_chat']) ? sanitize_text_field($input['channel_chat']) : '';

        $output['notify_create'] = !empty($input['notify_create']) ? 1 : 0;
        $output['notify_update'] = !empty($input['notify_update']) ? 1 : 0;
        $output['notify_delete'] = !empty($input['notify_delete']) ? 1 : 0;

        return $output;
    }

    public function field_notify_create(): void
    {
        $opts = get_option($this->option_name, []);
        $v = !empty($opts['notify_create']) ? 'checked' : '';
        echo "<input type='checkbox' name='{$this->option_name}[notify_create]' value='1' $v /> فعال";
    }

    public function field_notify_update(): void
    {
        $opts = get_option($this->option_name, []);
        $v = !empty($opts['notify_update']) ? 'checked' : '';
        echo "<input type='checkbox' name='{$this->option_name}[notify_update]' value='1' $v /> فعال";
    }

    public function field_notify_delete(): void
    {
        $opts = get_option($this->option_name, []);
        $v = !empty($opts['notify_delete']) ? 'checked' : '';
        echo "<input type='checkbox' name='{$this->option_name}[notify_delete]' value='1' $v /> فعال";
    }

    public function field_bot_token(): void
    {
        $opts = get_option($this->option_name, []);
        $v = isset($opts['bot_token']) ? esc_attr($opts['bot_token']) : '';
        echo "<input type='text' name='{$this->option_name}[bot_token]' value='$v' style='width:100%' />";
        echo "<p class='description'>توکن ربات را اینجا بزنید (ex: 12345:ABC...)</p>";
    }

    public function field_channel_chat(): void
    {
        $opts = get_option($this->option_name, []);
        $v = isset($opts['channel_chat']) ? esc_attr($opts['channel_chat']) : '';
        echo "<input type='text' name='{$this->option_name}[channel_chat]' value='$v' style='width:100%' />";
        echo "<p class='description'>chat_id کانال (برای اعلان‌ها)</p>";
    }

    public function settings_page(): void
    {
?>
        <div class="wrap">
            <h1>تنظیمات ربات تلگرام</h1>
            <form method="post" action="options.php">
                <?php
                settings_errors();
                settings_fields('tcb_options_group');
                do_settings_sections('telegram-bot');
                submit_button();
                ?>
            </form>
        </div>
<?php
    }

    private function get_settings()
    {
        return get_option($this->option_name, []);
    }
}
