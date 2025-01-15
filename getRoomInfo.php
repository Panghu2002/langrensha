<?php
// 显示所有错误
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 引入配置文件
require_once 'config.php';

// 开启会话
session_start();

// 获取数据库连接
$conn = new mysqli($servername, $username, $password, $dbname);

// 检查连接
if ($conn->connect_error) {
    die(json_encode(array('error' => '数据库连接失败: '. $conn->connect_error)));
}

// 获取房间号或房间 ID 信息
$roomId = $_GET['roomId'];

// 检查是否获取到房间号
if (!isset($roomId)) {
    echo json_encode(array('error' => '未获取到房间号'));
    exit;
}

// 查询房间信息
$sqlRoom = "SELECT room_number, name, max_players, players FROM rooms WHERE room_id =?";
$stmtRoom = $conn->prepare($sqlRoom);
$stmtRoom->bind_param("i", $roomId);
$stmtRoom->execute();
$resultRoom = $stmtRoom->get_result();
$roomInfo = $resultRoom->fetch_assoc();

// 查询玩家列表
$sqlPlayers = "SELECT player_name, role, status, vote_count, is_owner FROM room_players WHERE room_id =?";
$stmtPlayers = $conn->prepare($sqlPlayers);
$stmtPlayers->bind_param("i", $roomId);
$stmtPlayers->execute();
$resultPlayers = $stmtPlayers->get_result();
$playerList = array();
while ($row = $resultPlayers->fetch_assoc()) {
    $playerList[] = $row;
}


if ($roomInfo) {
    $response = array(
        'roomInfo' => $roomInfo,
        'playerList' => $playerList
    );
    echo json_encode($response);
} else {
    echo json_encode(array('error' => '未找到房间信息'));
}


// 关闭数据库连接
$stmtRoom->close();
$stmtPlayers->close();
$conn->close();
?>