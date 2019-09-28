<?php
    session_start();
    
    include 'httpStats.php'; //Zahrnuje connect.php

    $input = $_POST['oldPass'];
    
    if (password_verify($input, $_SESSION['user']['hash']))
    {
        echo "ok";
    }
    else
    {
        echo "er";
    }