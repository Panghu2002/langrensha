<?php
// 显示所有错误
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 引入配置文件
require_once 'config.php';

// 获取数据库连接
$conn = getConnection();

// 检查是否通过 POST 方法提交了表单
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 获取用户输入的房间 ID 和玩家昵称
    $roomId = $_POST['roomId'];
    $playerName = $_SERVER['REMOTE_ADDR'];

    // 输入验证
    if (empty($roomId) || empty($playerName)) {
        echo json_encode(array('error' => '房间 ID 和玩家昵称不能为空'));
        exit;
    }
    
    // 检查房间是否存在
    $sql = "SELECT id FROM rooms WHERE room_number =?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $roomId);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows == 0) {
        echo json_encode(array('error' => '房间不存在'));
        exit;
    }
    $stmt->close();

    // 检查玩家是否已经在房间中
    $sql = "SELECT id FROM room_players WHERE room_id =? AND player_name =?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $roomId, $playerName);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo json_encode(array('error' => '你已经在这个房间中了'));
        exit;
    }
    $stmt->close();

    // 插入玩家信息到 room_players 表
    $sql = "INSERT INTO room_players (room_id, player_name, role, status, vote_count, is_owner) VALUES (?,?,?,?,?,?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssii", $roomId, $playerName, $role, $status, $voteCount, $isowner);
    if ($stmt->execute()) {
        echo json_encode(array(
            'success' => true,
            'message' => '加入房间成功',
            'player_info' => [
                'room_id' => $roomId,
                'player_name' => $playerName,
                'role' => $role,
                'status' => $status,
                'vote_count' => $voteCount,
                'is_owner' => $isowner
            ]
        ));
    } else {
        echo json_encode(array('error' => '加入房间失败，请稍后重试'));
        exit;
    }
    $stmt->close();
} else {
    echo json_encode(array('error' => '无效的请求方法'));
    exit;
}

// 关闭数据库连接
$conn->close();
?>