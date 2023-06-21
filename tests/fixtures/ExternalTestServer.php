<?php

trait ExternalTestServer
{
    public static $testServer = 'http://127.0.0.1:18027';

    public static $postUrl = 'http://127.0.0.1:18027/method/post';

    public static $putUrl = 'http://127.0.0.1:18027/method/put';

    public static $patchUrl = 'http://127.0.0.1:18027/method/patch';

    public static $getUrl = 'http://127.0.0.1:18027/method/get';

    public static $deleteUrl = 'http://127.0.0.1:18027/method/delete';

    public static $headUrl = 'http://127.0.0.1:18027/method/head';

    public static $uploadUrl = 'http://127.0.0.1:18027/upload';

    public static function setUpBeforeClass(): void
    {
        $isWindows = stripos(PHP_OS, 'WIN') !== false;

        if ($isWindows) {
            $cmd = '"php.exe" -S 127.0.0.1:18027 ' . __DIR__ . '/test_server.php';
            proc_open(
                sprintf('start /D %s /B "" %s ' . PHP_EOL . PHP_EOL, __DIR__, $cmd),
                [STDIN, STDOUT, STDERR,],
                $pipes
            );
        } else {
            $cmd = 'cd ' . __DIR__ . ' && php -S 127.0.0.1:18027 test_server.php';

            shell_exec(sprintf('%s > /dev/null 2>&1 & echo $! >> %s', $cmd, __DIR__ . DIRECTORY_SEPARATOR . 'PID_FILE'));
        }

        sleep(1);
    }

    public static function tearDownAfterClass(): void
    {
        $isWindows = stripos(PHP_OS, 'WIN') !== false;
        $pidFile = __DIR__ . DIRECTORY_SEPARATOR . 'PID_FILE';

        if (! $isWindows && file_exists($pidFile)) {
            $PIDs = file($pidFile);

            foreach ($PIDs as $PID) {
                shell_exec("kill -9 $PID");
            }

            unlink($pidFile);

            return;
        }

        if ($isWindows) {
            exec('TaskList | find "php"', $processList);

            $testPid = getmypid();

            foreach ($processList as $process) {
                preg_match('/php\.exe\s+(\d+)\s.*/i', $process, $matches);
                $pid = (int) ($matches[1] ?? -1);

                if ($pid === -1 || $pid === $testPid) {
                    continue;
                }

                exec('TaskKill /F /PID ' . $pid);
            }
        }
    }
}
