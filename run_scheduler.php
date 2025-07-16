<?php

while (true) {
    echo "[" . date('Y-m-d H:i:s') . "] Running schedule...\n";
    shell_exec('php artisan schedule:run');
    sleep(60); // wait 60 seconds
}
