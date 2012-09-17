<?php

//session está registrada então está tudo pronto para fazer o logout
//destroi a sessão
session_start();
session_unset();
session_destroy();

echo "<META HTTP-EQUIV='REFRESH' CONTENT=\"0; URL='index.php'\">";


?>