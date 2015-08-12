<?php

//if uninstall not called from WordPress exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_metadata( 'comment', '', 'mt8_secret_comments', '', true );

