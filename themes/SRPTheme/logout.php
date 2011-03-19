<?php
require(dirname(__FILE__) . '/../../../wp-load.php');

wp_logout();
?>
<html>
<head>
<meta http-equiv="refresh" content="1;url=<?php echo site_url('/'); ?>" />
</head>
<body><p>Logging out...</p></body>
</html>