<?php
$result = exec('php -l app/Services/Authentication/AuthenticationService.php 2>&1', $output, $returnCode);
echo "Return code: " . $returnCode . "\n";
echo "Output:\n";
foreach ($output as $line) {
    echo $line . "\n";
}
