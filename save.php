<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SERVER['PHP_AUTH_USER'])) {
    @ini_set('zlib.output_compression',0);
    @ini_set('implicit_flush',1);
    @ob_end_clean();
    set_time_limit(120);
    ob_implicit_flush(1);
    echo str_pad('', 4096); flush();
    sleep(1);
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
            echo 'Deleting '. $page .'.md ... '.str_pad('', 4096); flush();
            unlink($path.'/_pages/'. $page .'.md');
            $action = 'Remove';
            echo "OK<br />".str_pad('', 4096); flush();
        } else {
            $action = 'Update';
        }
    }
    if ($action != 'Remove' && $content != '') {
        echo 'Writing '. $page .'.md ... '.str_pad('', 4096); flush();
        $file = fopen($path.'/_pages/'. $page .'.md', 'w');
        fwrite($file, $content);
        fclose($file);
        echo "OK<br />".str_pad('', 4096); flush();
    }
    echo 'Pulling changes ... '.str_pad('', 4096); flush();
    exec('cd '.$path.'/_pages && git pull', $result, $result_code);
    if (!$result_code) { echo "OK<br />".str_pad('', 4096); flush(); }
    echo 'Committing changes ... '.str_pad('', 4096); flush();
    exec('cd '.$path.'/_pages && git add --all . && git commit -m "'.$action.' '.$page .'.md" --author="'.$user.' <'.$user.'>"', $result, $result_code);
    if (!$result_code) { echo "OK<br />".str_pad('', 4096); flush(); }

    // Only if you have a SSH key without password
    echo 'Pushing changes ... '.str_pad('', 4096); flush();
    exec('cd '.$path.'/_pages && git push', $result, $result_code); // Only if you have a SSH key without password
    if (!$result_code) { echo "OK<br />".str_pad('', 4096); flush(); }
    echo 'Generating RSS ... '.str_pad('', 4096); flush();
    exec('ruby '.$path.'/gitrss.rb '.$path.'/_pages http://doc.yunohost.org/ "YunoHost documentation" > '.$path.'/feed.rss', $result, $result_code);
    if (!$result_code) { echo "OK<br />".str_pad('', 4096); flush(); }
} else {
    header($_SERVER['SERVER_PROTOCOL'].' 401 UNAUTHORIZED');
}

?>
