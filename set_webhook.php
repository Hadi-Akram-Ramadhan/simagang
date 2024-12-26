<?php
$bot_token = '8050546139:AAFLIeyeIYsRgz9dy82AWMrk4qviC2y8ie0';
$webhook_url = 'https://tesprojek.kreasimu.site/get_chat_id.php';

$url = "https://api.telegram.org/bot{$bot_token}/setWebhook?url={$webhook_url}";
$result = file_get_contents($url);
echo $result;
