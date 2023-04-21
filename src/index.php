<?php

function getDirContents($dir, &$results = [])
{
    $files = scandir($dir);

    foreach ($files as $key => $value) {
        $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
        if (!is_dir($path)) {
            $results[] = $path;
        } else if ($value != "." && $value != "..") {
            getDirContents($path, $results);
        }
    }

    return $results;
}

$characterDir = realpath(__DIR__ . '/character');
$backgroundDir = realpath(__DIR__ . '/background');

$characterFiles = getDirContents($characterDir);
$backgroundFiles = getDirContents($backgroundDir);

foreach ($backgroundFiles as $backgroundFile) {
    if (preg_match('/(.*\/)*(.+)\/(.+)\.jpg/', $backgroundFile, $matches)) {
        $backgroundType = $matches[2];
        $backgroundName = $matches[3];
    } else {
        continue;
    }

    foreach ($characterFiles as $characterFile) {
        if (preg_match('/(.*\/)*(.+)\/(.+)\.png/', $characterFile, $matches)) {
            $characterName = $matches[2];
            $emotion = $matches[3];
        } else {
            continue;
        }

        $backgroundImagePath = $backgroundFile;
        $characterImagePath = $characterFile;

        $outputDir = "../docs/{$backgroundType}/{$backgroundName}/{$characterName}";
        $outputImagePath = "{$outputDir}/{$emotion}.png";

        // 出力ディレクトリが存在しない場合は作成します
        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        // 画像を読み込みます
        $backgroundImage = imagecreatefromjpeg($backgroundImagePath);
        $characterImage = imagecreatefrompng($characterImagePath);

        // 透過処理を有効にするために設定します
        imagealphablending($characterImage, true);
        imagesavealpha($characterImage, true);

        // キャラクター立ち絵のサイズを取得します
        $characterWidth = imagesx($characterImage);
        $characterHeight = imagesy($characterImage);

        // キャラクター立ち絵を背景画像に合成します
        $dstX = 100; // キャラクター立ち絵の X 座標
        $dstY = 100; // キャラクター立ち絵の Y 座標

        // キャラクターの位置調整
        $dstY = intval(imagesy($backgroundImage) - ($characterHeight / 3 * 2));

        imagecopy($backgroundImage, $characterImage, $dstX, $dstY, 0, 0, $characterWidth, $characterHeight);

        // 合成した画像をファイルとして出力します
        imagepng($backgroundImage, $outputImagePath);

        // メモリを解放します
        imagedestroy($backgroundImage);
        imagedestroy($characterImage);
    }
}
