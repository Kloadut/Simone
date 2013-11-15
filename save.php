<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = $_SERVER['PHP_AUTH_USER'];
    $path = dirname(__FILE__);
    $action = 'Add';
    if (file_exists($path.'/_pages/'. $_POST["page"] .'.md')) {
        if ($_POST["content"] == '') {
            unlink($path.'/_pages/'. $_POST["page"] .'.md');
            $action = 'Remove';
        } else {
            $action = 'Update';
        }
    }
    if ($action != 'Remove') {
        $file = fopen($path.'/_pages/'. $_POST["page"] .'.md', 'w');
        fwrite($file, $_POST["content"]);
        fclose($file);
    }
    exec('cd '.$path.'/_pages && git add --all . && git commit -m "'.$action.' '.$_POST["page"] .'.md" --author="'.$user.' <'.$user.'>"');
    // Only if you have a SSH key without password
    exec('cd '.$path.'/_pages && git push');
    exec('ruby '.$path.'/gitrss.rb '.$path.'/_pages http://doc.yunohost.org/ "YunoHost documentation" > '.$path.'/feed.rss');
    http_response_code(200);
} else {
    http_response_code(405);
}

?>
