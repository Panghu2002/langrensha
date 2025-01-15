<?php
// 数据库连接配置
$config = array(
    'servername' => 'localhost',
    'username' => 'lrs',
    'password' => 'lrs',
    'dbname' => 'lrs'
);

// 创建数据库连接
function getConnection() {
    global $config;
    $conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);
    if ($conn->connect_error) {
        die("连接失败: ". $conn->connect_error);
    }
    return $conn;
}
?>