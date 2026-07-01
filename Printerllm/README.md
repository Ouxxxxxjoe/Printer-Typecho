# Printerllm —— Printer 主题配套的 llms.txt 生成插件

为 [Printer](https://github.com/Ouxxxxxjoe/Printer-Typecho) 主题配套的 Typecho 插件，在**站点根目录**生成符合 [llmstxt.org](https://llmstxt.org) 规范的静态文件，供 AI 代理与爬虫读取：

- `/llms.txt` —— **主索引文件**。站点摘要 + 文章 / 页面 / 分类的链接索引（精简，便于 AI 快速了解站点）。遵循 llmstxt.org 规范，始终生成。
- `/llms-full.txt` —— 最近文章的**全文拼接**版（可选）。信息完整，便于 AI 一次性读取整站正文。

> `llms-full.txt` 不在 llmstxt.org 核心规范内，而是社区通行约定（思路类似 FastHTML 的 `llms-ctx-full.txt`）。本插件按此约定生成。

## 环境要求

- **Typecho 1.2+**（推荐 1.3；同时兼容旧版 1.1 的下划线类名钩子）
- **PHP 7.4 / 8.x**
- **Printer 主题**（启用后输出富信息：摘要、读取说明、规范 URL 等；未启用时降级为最小化自包含索引）
- 站点根目录需对 Web 进程**可写**（用于生成静态文件）

## 特性

- **深度复用 Printer 主题**：直接读取主题的「AI 摘要 / 推荐读取说明 / 适合回答的问题 / 引用说明 / 文章输出模式 / 包含分类与页面」等设置，并复用其文章 / 分类 / 链接解析函数，保证 URL 与主题完全一致。
- **物理静态文件**：写入站点根目录，由 Web 服务器直接返回，速度快、可被 CDN 缓存，且不像主题动态输出那样需要 no-cache。
- **自动更新**：发布 / 重新发布、在列表里改变文章状态（发布/隐藏/草稿/私密）、删除文章 / 页面时，分别通过 `finishPublish` / `finishMark` / `finishDelete` 钩子自动重新生成。
- **手动重生成**：插件设置页提供「立即重新生成」按钮；改了主题设置后点一下即可同步。
- **规范友好**：`llms.txt` 严格遵循 H1 → 引用块摘要 → 说明段落 → H2 链接列表（含 `## Optional`）的顺序。
- **健壮降级**：Printer 主题未启用时自动切到最小化自包含实现；任何生成失败都**不会**阻塞文章发布 / 改状态 / 删除流程。

## 安装

1. 将 `Printerllm` 目录上传到 Typecho 的插件目录：
   - 默认路径：`/usr/plugins/`
   - 最终结构：`/usr/plugins/Printerllm/Plugin.php` 与 `Action.php`
2. 登录 Typecho 后台 → **控制台** → **插件**。
3. 找到 **Printerllm**，点击 **启用**。
4. 启用即会在根目录生成一次 `llms.txt`（若填了全文版文章数，同时生成 `llms-full.txt`）。

## 配置（控制台 → 插件 → Printerllm → 设置）

- **是否生成**：关闭后停止自动更新，但保留已生成的文件。
- **llms.txt 文章数**：主索引文件列出多少篇最近文章，默认 20，始终生成。
- **llms-full.txt 收录文章数（可选）**：拼接多少篇文章全文；**留空则不生成** `llms-full.txt`，填写则建议 30–200。
- **运行状态**：实时显示根目录写权限、两个文件的体积与最后修改时间、上次生成结果（含触发来源：`activate` / `publish` / `mark` / `delete` / `manual`），并提供「立即重新生成」按钮。

## 触发时机

1. **启用插件** —— 立即生成一次。
2. **发布 / 重新发布文章或页面** —— `finishPublish` 钩子，自动重新生成。
3. **列表里改变文章状态**（标记为发布/隐藏/草稿/私密，含草稿→发布） —— `finishMark` 钩子。
4. **删除文章 / 页面** —— `finishDelete` 钩子。
5. **手动按钮** —— 设置页点「立即重新生成」（入口 `/action/Printerllm`）。
   - 万一个别服务器环境下按钮不生效，**禁用再启用插件**同样会强制重生成。

> 所有钩子都包了容错：生成失败绝不阻塞你的发布 / 改状态 / 删除流程。

## 输出示例

`/llms.txt`（下图展示同时生成 `llms-full.txt` 的情况；关闭全文版时，`> 全文版见...` 与 `[Full text (llms-full.txt)]` 两行会自动消失，不产生死链）：

```markdown
# 你的站点名

> 你在主题里填的 AI 摘要（留空则用站点描述）。
> 本文件遵循 llmstxt.org 规范，最后更新于 2026-06-14。
> 全文版见同目录 llms-full.txt。

读取建议：
- 先读本索引了解站点范围，再按需读取具体文章 URL。
- 引用时请保留文章标题与原始链接。

## Recommended

- [Home](https://example.com/)
- [Full text (llms-full.txt)](https://example.com/llms-full.txt): 最近文章全文拼接

## Categories
- [随笔](https://example.com/category/essay/) (12 篇): ...

## Recent posts
- [某篇文章标题](https://example.com/archives/123/) — 2026-06-10: 摘要文字…

## Pages
- [关于](https://example.com/about.html): ...

## Optional
- [RSS](https://example.com/feed/)
- [Sitemap](https://example.com/sitemap.xml)
```

`/llms-full.txt`：每篇文章一段，含原文链接、日期、分类与全文正文，用 `---` 分隔。

## 排错

- **根目录写权限不可用**：设置页「根目录写权限」会标红。把 Typecho 根目录（含 `index.php` 的目录）开放给 Web 进程写权限（如 `chmod` / 属主改为 www-data），或联系主机商。无写权限时静态文件无法生成——这是「物理文件」方案的硬性前提。
- **文件生成了但访问 404**：
  - 站点装在子目录（如 `/blog/`）时，文件在 `/blog/llms.txt` 而非 `/llms.txt`，这是安装位置决定的。
  - 若启用了严格的静态规则把 `.txt` 也拦截了，请放行根目录 `.txt`。
  - 接了 CDN / 页面缓存时，发布后可能要等缓存过期或手动刷新。
- **「重新生成」按钮 404**：Typecho 的 `/action/<插件名>` 路由要求插件注册到 actionTable，本插件在启用时已自动注册。**覆盖升级插件文件后，记得禁用 → 重新启用一次**让注册（以及新钩子）生效。
- **浏览器打开 llms.txt 中文乱码**：服务器没给 `.txt` 声明字符集，中文 Windows 浏览器按 GBK 解码。在站点 nginx 配置的 `server` 块加一行 `charset utf-8;` 即可。
- **llms-full.txt 太大**：调小「收录文章数」，或留空不生成。

## 与 Printer 主题的关系

- 本插件**复用主题的设置项与函数**，主题必须处于启用状态才能输出富信息（摘要、读取说明、规范 URL 等）。
- 主题自带的 `LLM.php` 是一个**页面模板**（需手动建独立页面访问），本插件则提供**根路径的真正 `/llms.txt`**。两者互补：AI 按约定直接发现根路径入口用本插件即可——`llms.txt` 本身就是 AI 索引，无需再指向 `llms.html`。
- 若你希望 `llms.txt` 的 `## Optional` 段额外列出主题的 LLM 页面，可在主题「LLM 页面地址」里填上；**留空则不列出**（推荐，避免与 `llms.txt` 冗余）。

## 与访问统计的配合

Printer 主题开启访问统计时会对动态 HTML 发 no-cache 头；本插件生成的是**纯静态文件**，不受影响，可正常被 CDN / 浏览器缓存——这也是把 LLM 出口从主题拆到插件的性能收益之一。

## 卸载

停用插件即可（会自动注销 `/action/Printerllm` 入口）。已生成的 `llms.txt` / `llms-full.txt` 不会被自动删除，如不再需要请手动从根目录移除。

---

Version 1.0.0 · 作者 zhinan · https://zhinan.blog/
