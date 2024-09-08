<?php
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

function check_yellow($image_url)
{
    $api_url = "https://yellow-api.langs.ink/?url=" . urlencode($image_url);
    $result = file_get_contents($api_url);
    if ($result === FALSE) {
        return null;
    } else {
        return json_decode($result, true);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['image_url'])) {
    $image_url = $_POST['image_url'];
    $yellow_check_result = check_yellow($image_url);
    echo json_encode($yellow_check_result);
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pixiv搜索</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e6f3ff;
            text-align: center;
            padding-top: 20px;
            transition: background-color 0.3s, color 0.3s;
        }

        h1,
        h2 {
            color: #333;
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0 10px;
            margin-bottom: 20px;
        }

        label {
            color: #333;
            font-weight: bold;
            margin-bottom: 10px;
        }

        input[type="text"],
        select {
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ddd;
            margin-bottom: 20px;
            width: 90%;
            box-sizing: border-box;
        }

        input[type="submit"] {
            padding: 12px 25px;
            background-color: #4270fa;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #2e5cc9;
        }

        .gallery {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            padding: 1em;
            max-width: 100%;
        }

        .gallery .image-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            border: 1px solid #ccc;
            border-radius: 10px;
            margin: 10px;
            padding: 10px;
            max-width: 300px;
        }

        .gallery img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
        }

        .gallery .info {
            text-align: left;
            margin-top: 10px;
            width: 100%;
        }

        .gallery .info a,
        .gallery .info button {
            color: #4270fa;
            text-decoration: none;
            background: none;
            border: none;
            cursor: pointer;
            font: inherit;
            padding: 0;
        }

        .gallery .info a:hover,
        .gallery .info button:hover {
            text-decoration: underline;
        }

        .dark-mode {
            background-color: #2c2c2c;
            color: #ffffff;
        }

        .dark-mode input[type="text"],
        .dark-mode select {
            background-color: #4c4c4c;
            color: #fff;
            border: 1px solid #333;
        }

        .dark-mode label,
        .dark-mode h1,
        .dark-mode h2 {
            color: #fff;
        }

        .dark-mode input[type="submit"] {
            background-color: #555;
        }

        .dark-mode input[type="submit"]:hover {
            background-color: #444;
        }

        .dark-mode .gallery .info a,
        .dark-mode .gallery .info button {
            color: #9bbffd;
        }

        .dark-mode .gallery .info a:hover,
        .dark-mode .gallery .info button:hover {
            color: #cce3ff;
        }

        .toggle-dark-mode {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 10px;
            background-color: #4270fa;
            color: #fff;
            border: none;
            border-radius: 50%;
            cursor: pointer;
        }

        .load-more {
            padding: 12px 25px;
            background-color: #4270fa;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
        }

        .load-more:hover {
            background-color: #2e5cc9;
        }

        .yellow-result {
            margin-top: 10px;
            font-weight: bold;
        }

        .image-count {
            position: fixed;
            bottom: 20px;
            left: 20px;
            padding: 10px;
            background-color: #4270fa;
            color: #fff;
            border-radius: 5px;
        }

        .dark-mode .image-count {
            background-color: #555;
            color: #fff;
        }
    </style>
</head>

<body>
    <h1>Pixiv搜索</h1>
    <form id="searchForm" method="GET" action="">
        <label for="keyword1">关键词1:</label>
        <input type="text" id="keyword1" name="keyword1" value="<?php echo isset($_GET['keyword1']) ? htmlspecialchars($_GET['keyword1']) : ''; ?>" required>
        <br>
        <label for="keyword2">关键词2:</label>
        <input type="text" id="keyword2" name="keyword2" value="<?php echo isset($_GET['keyword2']) ? htmlspecialchars($_GET['keyword2']) : ''; ?>">
        <br>
        <label for="r18">过滤R18内容:</label>
        <select id="r18" name="r18">
            <option value="0" <?php echo (isset($_GET['r18']) && $_GET['r18'] == '0') ? 'selected' : ''; ?>>是</option>
            <option value="1" <?php echo (isset($_GET['r18']) && $_GET['r18'] == '1') ? 'selected' : ''; ?>>否</option>
            <option value="2" <?php echo (!isset($_GET['r18']) || $_GET['r18'] == '2') ? 'selected' : ''; ?>>混合</option>
        </select>
        <br>
        <input type="submit" value="搜索">
    </form>

    <div class='gallery' id='gallery'>
        <!-- Gallery will be populated by JavaScript -->
    </div>
    <center>
        <button id="loadMoreBtn" class="load-more" style="display: none;" onclick="loadMore()">加载</button>
    </center>

    <button class="toggle-dark-mode" onclick="toggleDarkMode()">🌙</button>

    <div class="image-count" id="image-count">
        总图片数: 0, 正常: 0, 中色情: 0, 色情: 0
    </div>

    <script>
        function toggleDarkMode() {
            document.body.classList.toggle('dark-mode');
        }

        function loadMore() {
            const gallery = document.getElementById('gallery');
            const keyword1 = "<?php echo isset($_GET['keyword1']) ? htmlspecialchars($_GET['keyword1']) : ''; ?>";
            const keyword2 = "<?php echo isset($_GET['keyword2']) ? htmlspecialchars($_GET['keyword2']) : ''; ?>";
            const r18 = "<?php echo isset($_GET['r18']) ? intval($_GET['r18']) : 2; ?>";
            const num = 20;

            fetch(`load_more.php?keyword1=${encodeURIComponent(keyword1)}&keyword2=${encodeURIComponent(keyword2)}&r18=${r18}&num=${num}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    const existingUrls = new Set(Array.from(gallery.querySelectorAll('img')).map(img => img.src));
                    data.forEach(setu => {
                        if (!existingUrls.has(setu.urls.regular)) {
                            const imageBox = document.createElement('div');
                            imageBox.classList.add('image-box');

                            const title = document.createElement('h2');
                            title.textContent = `${setu.title} by ${setu.author}`;
                            imageBox.appendChild(title);

                            const img = document.createElement('img');
                            img.src = setu.urls.regular;
                            img.alt = setu.title;
                            imageBox.appendChild(img);

                            const yellowResult = document.createElement('div');
                            yellowResult.classList.add('yellow-result');
                            yellowResult.style.display = 'none';
                            imageBox.appendChild(yellowResult);

                            const info = document.createElement('div');
                            info.classList.add('info');

                            const tags = document.createElement('p');
                            tags.textContent = `标签: ${setu.tags.join(", ")}`;
                            info.appendChild(tags);

                            const downloadLink = document.createElement('a');
                            downloadLink.href = setu.urls.regular;
                            downloadLink.download = '';
                            downloadLink.target = '_blank';
                            downloadLink.textContent = '下载';
                            info.appendChild(downloadLink);

                            const separator = document.createTextNode(" | ");
                            info.appendChild(separator);

                            const artworkLink = document.createElement('a');
                            artworkLink.href = `https://www.pixiv.net/artworks/${setu.pid}`;
                            artworkLink.target = '_blank';
                            artworkLink.textContent = '稿件链接';
                            info.appendChild(artworkLink);

                            imageBox.appendChild(info);
                            gallery.appendChild(imageBox);

                            // 自动进行鉴黄
                            fetch('', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded'
                                    },
                                    body: `image_url=${encodeURIComponent(setu.urls.regular)}`
                                })
                                .then(response => response.json())
                                .then(result => updateYellowResult(yellowResult, result))
                                .then(() => updateImageCount())
                                .catch(error => {
                                    yellowResult.textContent = '鉴黄失败';
                                    yellowResult.style.color = 'red';
                                    yellowResult.style.display = 'block';
                                    console.error('Error checking yellow:', error);
                                });
                        }
                    });

                    // 显示加载更多按钮
                    document.getElementById('loadMoreBtn').style.display = 'block';
                })
                .catch(error => {
                    console.error('Error loading more images:', error);
                    alert('加载图片时出错，请稍后再试');
                });
        }

        function updateYellowResult(yellowResultDiv, result) {
            if (result && result.score !== undefined) {
                let color;
                if (result.score > 0.6) {
                    color = 'red';
                } else if (result.score > 0.3) {
                    color = 'yellow';
                } else {
                    color = 'green';
                }
                yellowResultDiv.textContent = `色情指数：${result.score}`;
                yellowResultDiv.style.color = color;
            } else {
                yellowResultDiv.textContent = '鉴黄失败';
                yellowResultDiv.style.color = 'red';
            }
            yellowResultDiv.style.display = 'block';
        }

        function checkAllImages() {
            const imageBoxes = document.querySelectorAll('.image-box');
            imageBoxes.forEach(imageBox => {
                const img = imageBox.querySelector('img');
                const yellowResultDiv = imageBox.querySelector('.yellow-result');
                fetch('', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `image_url=${encodeURIComponent(img.src)}`
                    })
                    .then(response => response.json())
                    .then(result => updateYellowResult(yellowResultDiv, result))
                    .then(() => updateImageCount())
                    .catch(error => {
                        yellowResultDiv.textContent = '鉴黄失败';
                        yellowResultDiv.style.color = 'red';
                        yellowResultDiv.style.display = 'block';
                        console.error('Error checking yellow:', error);
                    });
            });
        }

        function updateImageCount() {
            const imageCountDiv = document.getElementById('image-count');
            const totalImages = document.querySelectorAll('.gallery .image-box').length;
            const normalImages = document.querySelectorAll('.gallery .yellow-result[style*="color: green"]').length;
            const midYellowImages = document.querySelectorAll('.gallery .yellow-result[style*="color: yellow"]').length;
            const yellowImages = document.querySelectorAll('.gallery .yellow-result[style*="color: red"]').length;

            imageCountDiv.textContent = `总图片数: ${totalImages}, 正常: ${normalImages}, 中色情: ${midYellowImages}, 色情: ${yellowImages}`;
        }

        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('searchForm');
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                const keyword1 = document.getElementById('keyword1').value;
                const keyword2 = document.getElementById('keyword2').value;
                const r18 = document.getElementById('r18').value;
                const searchParams = new URLSearchParams(window.location.search);
                searchParams.set('keyword1', keyword1);
                searchParams.set('keyword2', keyword2);
                searchParams.set('r18', r18);
                window.location.search = searchParams.toString();
            });

            if (<?php echo isset($_GET['keyword1']) && isset($_GET['r18']) ? 'true' : 'false'; ?>) {
                loadMore();
            }

            checkAllImages();
            updateImageCount();
        });

        // 同步系统深色模式
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.body.classList.add('dark-mode');
        }
    </script>
</body>

</html>