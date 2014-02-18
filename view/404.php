<?php
ob_end_clean();
header('HTTP/1.0 404 Not Found');
echo "<h1>Error 404 Not Found</h1>";
echo "The page that you have requested could not be found.";
exit;