
<?php
echo "INSTALL OK <br>";
echo "Session ID: " . session_id() . "<br>";
echo "State generado: " . $state . "<br>";

$_SESSION['shopify_state'] = $state;