<?php

include("tool.php");


session_unset();
session_destroy();


header("Location: conexioncerrada.php");


?>