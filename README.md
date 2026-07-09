# 📝 SimpleNote

> 极简的在线文本分享笔记 —— 打开即用，输入自动保存，分享链接即可访问。

基于开源项目 [minimalist-web-notepad](https://github.com/pereorga/minimalist-web-notepad) 的 **Vue 3 重构版**：前端用 Vue 3 + Vite 重写，后端保留极简的单文件 PHP，笔记以扁平文件存储，零数据库依赖。

---

## ✨ 功能特性

- **打开即用**：无需登录，支持随机生成或自定义分享链接
- **自动保存**：输入停顿后约 800ms 防抖自动保存到服务器
- **编辑工具**：复制链接 / 复制内容 / 下载 `.txt` / 打印 / 清空删除
- **实时统计**：字符数 · 行数 · 词数
- **亮暗主题**：跟随系统偏好，可手动切换并记忆到本地
- **响应式布局**：手机、平板、桌面自适应
- **curl API**：支持命令行读写，方便脚本与自动化集成
- **零数据库**：后端扁平文件存储，部署极简

## 🛠 技术栈

| 层 | 技术 |
|---|---|
| 前端 | Vue 3 · Vite 5 |
| 后端 | PHP（单文件 `index.php`） |
| 存储 | 文件系统（扁平文件） |
| 服务器 | nginx + PHP-FPM |

不依赖 vue-router / pinia / axios / markdown 等库，保持轻量（构建后 JS 约 33KB gzip）。

## 📁 目录结构

```
.
├── index.html              # Vite 入口（开发用）
├── vite.config.js          # Vite 配置（base: /spa/）
├── package.json
├── index.php               # 后端：路由 + 笔记存取 + 站点配置($site)
├── src/
│   ├── main.js             # 应用入口
│   ├── config.js           # 站点文案常量（读 window.__NOTE_CONFIG__，缺失回退默认值）
│   ├── styles.css          # 全局样式 / CSS 变量 / 暗色主题
│   ├── App.vue             # 布局 / 主题切换 / Toast / 视图路由(按 URL 路径)
│   └── components/
│       ├── Home.vue        # 首页（隐私提醒 / Hero / 新建分享 / 特性 / API 说明）
│       └── Editor.vue      # 编辑器（自动保存 / 工具栏 / 统计 / Tab 缩进）
└── README.md
```

## 🚀 快速开始（开发）

环境要求：**Node.js 18+** 与 npm。

```bash
npm install
npm run dev        # 启动开发服务器，按提示打开浏览器
```

> 本地开发无 PHP 后端，站点文案会使用 `src/config.js` 里的默认值，可正常预览 UI。
>
> **小提示**：若 `npm install` 没有装上 `vite` 等 devDependencies，检查环境变量 `NODE_ENV` 是否被设为 `production`——是的话改为 `development`，或安装时加 `--include=dev`。

## 📦 构建生产产物

```bash
npm run build      # 产物输出到 dist/
```

## ⚙️ 站点配置

所有展示文案集中在后端 `index.php` 的 `$site` 数组中，前端经 `window.__NOTE_CONFIG__` 读取：

```php
$site = [
    'email'     => 'feedback#example.com',        // 反馈邮箱（# 代替 @ 防爬）
    'icp'       => '',                              // ICP 备案号（中国大陆站点填写，否则留空）
    'icp_url'   => 'https://beian.miit.gov.cn/',   // 备案官网链接
    'siteName'  => 'SimpleNote',                 // 站点名（顶部 logo / 浏览器标题）
    'copyright' => 'Copyright © 2024 SimpleNote',// 页脚版权
];
```

- **改文案只需改 `$site`，无需重新构建前端**（`src/config.js` 中的同名值仅作为本地 dev 的回退默认值）。
- **域名不写死**：前端运行时通过 `window.location.origin` 自动获取当前域名，换域名零改动。
- 浏览器标题会随 `siteName` 联动：首页为 `siteName`，笔记页为 `笔记名 - siteName`。

## 🌐 部署

1. **后端**：将 `index.php` 放到站点根目录，确保笔记存储目录（默认 `_tmp/`）对 PHP 进程可写。
2. **前端**：`npm run build` 后，把 `dist/assets/*` 上传到站点的 `/spa/assets/`。
3. **入口 HTML**：部署一个 PHP 渲染的 HTML 壳（如 `spa-index.html`），注入配置并加载构建产物：
   ```html
   <title><?php /* 笔记名 - siteName，或 siteName */ ?></title>
   <script>window.__NOTE_CONFIG__ = <?php echo json_encode($site, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;</script>
   <script type="module" crossorigin src="/spa/assets/index-[hash].js"></script>
   <link rel="stylesheet" crossorigin href="/spa/assets/index-[hash].css">
   ```
4. **nginx 重写**：把笔记名转发给 `index.php`：
   ```nginx
   location / {
       rewrite ^/([a-zA-Z0-9_-]+)$ /index.php?note=$1 last;
   }
   ```

## 🔌 API（curl）

当请求 `User-Agent` 以 `curl` 开头，或 URL 带 `?raw` 时，后端返回纯文本（便于命令行与脚本）；普通浏览器访问则返回网页应用。

```bash
# 读取（笔记不存在时返回 404）
curl https://你的域名/你的笔记名

# 写入 / 新建
curl -X POST -d 'text=Hello World' https://你的域名/你的笔记名

# 显式获取原始文本
curl https://你的域名/你的笔记名?raw
```

## 🔒 安全提示

- 笔记默认**任何人拿到链接即可查看和编辑**，请勿用于存储敏感信息（首页已有隐私提醒）。
- 建议为 `_tmp/` 设置合适的权限，并定期清理无用笔记。

## 📄 License

[Apache License 2.0](./LICENSE)。

本项目基于 [minimalist-web-notepad](https://github.com/pereorga/minimalist-web-notepad)（© Pere Orga）重构，感谢上游开源项目。
