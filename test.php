<?php

include_once("vendor/autoload.php");
include_once("Database.php");

use Lanous\Database as Database;

Database\Config::setConfig("database","host",'127.0.0.1');
Database\Config::setConfig("database","username",'root');
Database\Config::setConfig("database","password",'');
Database\Config::setConfig("database","db_name",'lanous');
$database = new Database\Connect(MyDatabase::class);
$database->AutoLoad("tables");


$Users = new ProjectName\Tables\Users();
$Wallet = new ProjectName\Tables\Wallet();

$userData = $Wallet->GetUserbyID (1);
echo "
Hello ".$userData->getFirstname()." ".$userData->getLastname()."
IRT Wallet: ".$userData->getIRT()."
";
/*
Hello mohammad azad
IRT Wallet: 50000
*/
$Users->ChangeName(1,"new mohammad","new azad");