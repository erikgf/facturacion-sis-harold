<?php 

 define("F_TOKEN_PROVEEDOR", "FACTURALAYA_FN567YHDJC2NRHNZ0CXD");
 define("F_FECHA_TOPE", "14-01-2024");

 $h =  sprintf(
    "%s://%s%s",
    isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
    $_SERVER['SERVER_NAME'],
    "/"
  );
 echo $h;