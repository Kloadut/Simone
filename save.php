<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SERVER['PHP_AUTH_USER'])) {
    $user = escapeshellarg($_SERVER['PHP_AUTH_USER']);
    $page = $_POST["page"];
    if (!preg_match('/^[A-Za-z0-9_]+$/', $page)) {
        die;
    }
    $content = $_POST["content"];
    $path = dirname(__FILE__);
    $action = 'Add';
    if (file_exists($path.'/_pages/'. $page .'.md')) {
        if ($content == '') {
            unlink($path.'/_pages/'. $page .'.md');
            $action = 'Remove';
        } else {
            $action = 'Update';
        }
    }
    if ($action != 'Remove' && $content != '') {
        $file = fopen($path.'/_pages/'. $page .'.md', 'w');
        fwrite($file, $content);
        fclose($file);
    }

    if (file_exists($path.'/commit.lock')) {
        sleep(10);
    }

    exec('touch '.$path.'/commit.lock');
    exec('cd '.$path.'/_pages && '.
         'git pull && '.
         'git add --all . && '.
         'git commit -m "'.$action.' '.$page .'.md" --author="'.$user.' <'.$user.'>" && '.
         'git push');
    exec('rm '.$path.'/commit.lock');

    exec('ruby '.$path.'/gitrss.rb '.$path.'/_pages http://doc.yunohost.org/ "YunoHost documentation" > '.$path.'/feed.rss', $result, $result_code);
} else {
    header($_SERVER['SERVER_PROTOCOL'].' 401 UNAUTHORIZED');
}

?>
