<?php


/**
 * データベースに接続する関数
 *
 * @return PDO
 */
function getDB(): PDO
{
    $dsn = 'mysql:dbname=webapp; host=127.0.0.1; charset=utf8';
    $usr = 'root';
    $password = '';
    $db = new PDO($dsn, $usr, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $db;
};
