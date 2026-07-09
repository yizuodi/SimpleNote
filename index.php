<?php
error_reporting(E_ALL);

// 笔记扁平文件存储目录（需对 PHP 进程可写）
$save_path = '_tmp';

// 站点展示性文案：单一事实源。前端 SPA 通过 window.__NOTE_CONFIG__ 读取（见部署的 HTML 壳）。
// 改文案只改这里，无需重新构建前端。
$site = [
    'email'     => 'feedback#example.com',          // 反馈邮箱（# 代替 @ 防爬）
    'icp'       => '',                                // ICP 备案号（中国大陆站点填写，否则留空）
    'icp_url'   => 'https://beian.miit.gov.cn/',     // 备案官网链接
    'siteName'  => 'SimpleNote',                   // 站点名（顶部 logo / 浏览器标题）
    'copyright' => 'Copyright © 2024 SimpleNote',  // 页脚版权
];

// Disable caching
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// 没有 note 参数 / 格式非法 / 过长 → 首页
if (!isset($_GET['note']) || !preg_match('/^[a-zA-Z0-9_-]+$/', $_GET['note']) || strlen($_GET['note']) > 64) {
    header('Content-Type: text/html; charset=UTF-8');
    include __DIR__ . '/spa-index.html';
    exit;
}

$path = $save_path . '/' . $_GET['note'];

// 写入（POST text=...；空文本则删除该笔记）
if (isset($_POST['text'])) {
    file_put_contents($path, $_POST['text']);
    if (!strlen($_POST['text'])) {
        @unlink($path);
    }
    exit;
}

// 取原文：?raw 或 curl 命令行（User-Agent 以 curl 开头）
if (isset($_GET['raw']) || strpos($_SERVER['HTTP_USER_AGENT'], 'curl') === 0) {
    if (is_file($path)) {
        header('Content-type: text/plain');
        print file_get_contents($path);
    } else {
        header('HTTP/1.0 404 Not Found');
    }
    exit;
}

// 普通浏览器访问 → 返回 SPA 页面
header('Content-Type: text/html; charset=UTF-8');
include __DIR__ . '/spa-index.html';
