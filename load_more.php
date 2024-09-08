<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 调试信息
file_put_contents('debug.log', "开始执行 load_more.php\n", FILE_APPEND);

function fetch_images($keyword1, $keyword2, $r18, $num, $proxy)
{
    $data = [
        "tag" => [
            [$keyword1],
            $keyword2 ? explode(" | ", $keyword2) : []
        ],
        "size" => ["regular"],
        "num" => $num,
        "r18" => $r18,
        "proxy" => $proxy,
    ];

    $url = "https://api.lolicon.app/setu/v2";
    $options = [
        "http" => [
            "header" => "Content-Type: application/json\r\n",
            "method" => "POST",
            "content" => json_encode($data)
        ]
    ];
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    // 调试信息
    file_put_contents('debug.log', "API 请求返回结果: " . $result . "\n", FILE_APPEND);

    if ($result === FALSE) {
        return [];
    } else {
        $response = json_decode($result, true);
        if (isset($response['error']) && $response['error'] !== '') {
            return [];
        } else {
            return $response['data'];
        }
    }
}

if (isset($_GET['keyword1']) && isset($_GET['r18']) && isset($_GET['num'])) {
    $keyword1 = htmlspecialchars($_GET['keyword1']);
    $keyword2 = isset($_GET['keyword2']) ? htmlspecialchars($_GET['keyword2']) : '';
    $r18 = intval($_GET['r18']);
    $num = intval($_GET['num']);
    $proxy = 'pixiv.hlyun.org';

    $images = fetch_images($keyword1, $keyword2, $r18, $num, $proxy);

    // 调试信息
    file_put_contents('debug.log', "返回的图片数据: " . json_encode($images) . "\n", FILE_APPEND);

    header('Content-Type: application/json');
    echo json_encode($images);
} else {
    echo json_encode(['error' => 'Missing parameters']);
}
