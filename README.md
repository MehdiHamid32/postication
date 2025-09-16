# Postication

Contributors: mehdihamid
Tags: telegram, notification, posts
Requires at least: 5.0
Tested up to: 6.8.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Send Creating Post Notification To Telegram Channel

== Description ==

Postication is a WordPress plugin that automatically sends notifications to your Telegram channel whenever a post is **created**, **updated**, or **deleted**. It supports post title, excerpt, thumbnail, permalink, hashtags, and inline buttons.

## Features

- Send notification on post creation
- Edit notifications on post update
- Delete Telegram messages on post deletion
- Include post excerpt and hashtags
- Inline button linking to the post
- Simple settings page for Bot Token and Channel Chat ID
- Enable/disable notifications for updates and deletions

## Installation

1. Clone or download this repository.
2. Upload the `postication` folder to the `/wp-content/plugins/` directory.
3. Activate the plugin through the **Plugins** menu in WordPress.
4. Go to **Telegram Bot Settings** in the admin menu.
5. Enter your **Bot Token** and **Channel Chat ID**.
6. Optionally enable/disable notifications for updates and deletions.

## Usage

- When you publish a new post, the plugin will send a notification to your Telegram channel.
- If the post is updated, it can edit the previous message (if enabled).
- If the post is deleted, it can remove the Telegram message (if enabled).

## Settings

- **Bot Token:** Your Telegram bot token (`12345:ABC...` format).
- **Channel Chat ID:** Numeric ID of your Telegram channel.
- **Enable/Disable:** Toggle notifications for create, update, delete events.

## Screenshots

1. Admin settings page.
2. Telegram notification example with inline button.

## Frequently Asked Questions

**How do I get my channel chat ID?**  
Use [@userinfobot](https://t.me/userinfobot) on Telegram to get your chat/channel ID.

**Does it support post updates?**  
Yes, you can enable/disable update notifications in the settings page.

**Will it delete messages when a post is deleted?**  
Yes, if delete notifications are enabled, it will remove the corresponding Telegram messages.