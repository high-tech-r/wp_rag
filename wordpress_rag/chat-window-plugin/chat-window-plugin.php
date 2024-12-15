<?php
/*
Plugin Name: Chat Window Plugin
Description: Adds a chat window with RAG integration.
Version: 1.0
Author: Reiji Katsumata
*/

function enqueue_chat_plugin_script() {
    wp_enqueue_script('chat-window', plugin_dir_url(__FILE__) . 'chat-window.js', array(), null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_chat_plugin_script');

