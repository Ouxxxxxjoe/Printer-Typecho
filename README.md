# Printer（Typecho 主题）

一款仿打印纸 / 复古设备面板风格的 Typecho 主题，复刻 Nooc 佬原创的主题 https://nooc.me 移植到 Typecho 中，支持黑暗模式、接入了中文网字计划 `result.css` 可以选择更丰富的字体。

---

## 部署 / 安装

1. 将本主题目录上传到你的 Typecho 站点主题目录：
   
   - 默认路径：`/usr/themes/`
   - 示例：把主题放为 `/usr/themes/Printer/`
2. 登录 Typecho 后台 → **控制台** → **外观**，启用主题 **Printer**。
3. 后台 → **外观** → **设置外观** → **Printer**，按需填写配置项（见下文）。

---

## 目录结构（主题文件）

- `index.php`：首页/列表
- `post.php`：文章详情页
- `page.php`：页面详情页
- `archive.php`：归档/分类/标签/搜索结果（复用 `index.php`）
- `comments.php`：评论
- `header.php` / `footer.php`：公共头尾
- `functions.php`：主题配置项与辅助函数
- `404.php`：404 页面
- `style.css`：主题头信息（用于 Typecho 识别主题）
- `css/style.css`：主题样式

---

## 主题设置说明


- **顶部 Logo 图片 URL**：替换左上角圆形图标（可留空）
- **顶部 Logo 文本**：为空时显示站点标题
- **网站描述**：为空时显示站点副标题
- **favicon.ico 图标路径**：可填完整 URL 或站内路径
- **网页底部版权信息**：留空则左侧默认显示 `© 年份 站点名`
- **网站统计代码**：将输出在页面底部（粘贴统计脚本即可）
- **随机阅读分类 slug**：多个用英文逗号分隔；留空表示全站文章随机

### 中文字体（中文网字计划 / result.css）

- **中文网字计划 CSS（result.css）**：填写 `result.css` 链接
- **Font-family**：填写 `result.css` 中声明的 `font-family`
  - 若包含空格，建议加引号：例如 `"LXGW WenKai"`
- **中文字体应用范围**：
  - 仅文章/页面内容（推荐）
  - 整张纸区域（含列表页）
  - 全站（body）
  

### 颜色自定义

- **外链颜色**：文章正文中外链（`http/https`）的颜色（默认 `#ff6b35`）
- **文章详情页分类颜色**：文章详情页顶部分类标签颜色（默认 `#ff6b35`）

---

## 致谢


[NOOC](https://nooc.me/) 主题开发者，本项目复刻的起源。
[中文网字计划](https://github.com/KonghaYao/chinese-free-web-font-storage) 一个免费的中文 web 字体库，支持在线加载及查看字体信息