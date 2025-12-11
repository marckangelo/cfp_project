<?php
/*
- Marck Angelo GELI (40265711)
- Arshdeep SINGH (40286514)
- Muhammad Adnan SHAHZAD (40282531)
- Muhammad RAZA (40284058)
*/

/*
Contributor to this file:
- Muhammad RAZA (40284058)
*/

// Destroy all SESSION data when logging out

session_start();
session_unset();
session_destroy();
header('Location: index.php');
exit;