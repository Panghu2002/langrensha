<?php
// 引入 config.php 文件
require_once 'config.php';


// 开始会话
session_start();


// 获取数据库连接
$conn = getConnection();


// 构建 SQL 查询语句
$sql = "SELECT * FROM rooms ORDER BY RAND() LIMIT 4";


// 执行查询
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();


$rooms = [];
while ($row = $result->fetch_assoc()) {
    $row['players'] = json_decode($row['players'], true);
    $rooms[] = $row;
}


if (count($rooms) === 0) {
    echo json_encode(array('rooms' => [], 'error' => '没有找到房间'));
} else {
    echo json_encode(array('rooms' => $rooms));
}


// 关闭连接
$stmt->close();
$conn->close();
?>