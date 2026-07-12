# Printer 主题 Markdown 增强设计：渲染样式补齐 + TOC 目录

**日期**：2026-07-12
**状态**：已确认，待实施
**范围**：渲染样式补齐 + 前端 JS 生成 TOC 目录/标题锚点
**技术边界**：Typecho 默认 HyperDown 解析器，无插件

---

## 背景与问题

用户反馈"Markdown 编辑过程中不方便"。经审计，当前主题的 Markdown 渲染存在两块短板：

### 问题 1：渲染样式缺失（HyperDown 已解析但主题未给样式）

当前 `.post-content` 已覆盖：`a` / `blockquote` / `pre` / `code` / `table` / `img` / `p`。

但以下元素**完全缺失样式**，写出后呈浏览器默认样式（裸奔）：

| 元素 | HyperDown 是否解析 | 主题现状 |
|---|---|---|
| h2-h6 标题层级 | ✅ 解析 | ❌ 裸奔（无字号阶梯/间距/装饰）|
| ul / ol 列表 | ✅ 解析 | ❌ 裸奔（浏览器默认圆点+缩进）|
| hr 分割线 `---` | ✅ 解析 | ❌ 缺失 |
| del 删除线 `~~xx~~` | ✅ 解析 | ❌ 缺失 |

### 问题 2：扩展语法（TOC 目录/标题锚点）

Typecho 默认 HyperDown 解析器**不生成**标题 id、不生成 TOC、不支持 `[toc]` 语法。写长文章时读者无法快速跳转、无法分享某章节链接。

**技术边界**：
- 脚注 `[^1]`：HyperDown 支持，但本次不在范围内（朴素效果，可后续单独美化）
- 任务列表 `- [x]` / 高亮 `==xx==`：HyperDown **不支持**，需装插件/换解析器，**主题无能为力，不在本次范围**
- TOC 目录 / 标题锚点：解析器不支持新语法，但**主题可用 JS 从已有 h2-h6 生成**，本次实现

## 设计原则

1. **只补渲染样式，不改解析**——所有补齐的元素都是 HyperDown 已经能解析的，主题只管前端样式
2. **呼应打印纸主题**——标题装饰、分隔线样式与"纸张/打印机"基因呼应，不做通用博客样式
3. **TOC 零布局侵入**——视口外侧固定，正文宽度不受影响，中小屏优雅降级
4. **延续纸张动效语言**——TOC 出现/高亮过渡复用已建立的 `paper-*` 缓动变量

## 硬约束（贯穿全程）

> 本次增强**只补充渲染样式和前端 JS 增强**，不改 PHP 模板结构。
>
> `post.php` / `index.php` / `header.php` 等模板文件一行不碰（TOC 由 JS 动态注入 DOM，不改模板）。

只允许改动：`css/style.css`（渲染样式 + TOC 样式）+ `footer.php`（TOC 的 JS 逻辑）。

---

## 第 1 块 · 渲染样式补齐

### 1.1 标题层级 h2-h6

**现状**：正文里写 `## 标题`，渲染为浏览器默认粗体，无字号阶梯，与正文混淆。

**设计**：建立字号阶梯 + 出版式间距节奏 + h2/h3 短下划线装饰。

```css
/* 标题层级：字号阶梯（h1 不在此定义，文章大标题已用 .paper-title）*/
.post-content h2 {
  font-size: clamp(20px, 3vw, 24px);
  line-height: 1.3;
  margin: 1.8em 0 0.6em;
  font-weight: 700;
}
.post-content h3 {
  font-size: clamp(17px, 2.5vw, 20px);
  line-height: 1.35;
  margin: 1.6em 0 0.5em;
  font-weight: 700;
}
.post-content h4 {
  font-size: 16px;
  line-height: 1.4;
  margin: 1.4em 0 0.4em;
  font-weight: 600;
}
.post-content h5 {
  font-size: 15px;
  margin: 1.2em 0 0.4em;
  font-weight: 600;
}
.post-content h6 {
  font-size: 14px;
  margin: 1.1em 0 0.3em;
  font-weight: 600;
  color: var(--muted);
}
```

**间距设计**：`margin-top`（1.8em）大于 `margin-bottom`（0.6em），标题与上方内容留呼吸空间，与下方紧跟段落贴得更近——出版排版标准节奏。

**短下划线装饰（h2/h3）**：用 `::after` 伪元素生成一条 32px 短色块，像打印纸的章节分隔标记：

```css
.post-content h2::after,
.post-content h3::after {
  content: "";
  display: block;
  width: 32px;
  height: 3px;
  margin-top: 8px;
  background: var(--accent);
  border-radius: var(--radius-full);
}
```

h4-h6 不加装饰，仅靠字号和粗细区分，避免视觉过载。

### 1.2 列表 ul / ol

**现状**：浏览器默认实心圆点 + 默认缩进，与宋体正文风格不搭。

```css
.post-content ul,
.post-content ol {
  margin: 0 0 1em;
  padding-left: 1.6em;
}
.post-content li {
  margin: 0.25em 0;
}
.post-content li::marker {
  color: var(--accent);
}
```

- 统一缩进 1.6em
- 项目符号/数字 marker 染成 accent 绿，呼应主题
- 嵌套列表靠 padding-left 自然缩进
- `li` 行间距 0.25em，避免拥挤

### 1.3 分隔线 hr（圆点撕纸线）

**现状**：缺失，`---` 渲染为浏览器默认灰色实线。

**设计**：居中三个圆点，像打孔纸的撕纸线，呼应打印纸基因：

```css
.post-content hr {
  border: none;
  margin: 2em 0;
  text-align: center;
  line-height: 0;
}
.post-content hr::after {
  content: "● ● ●";
  letter-spacing: 0.8em;
  color: var(--line);
  font-size: 0.6em;
}
```

### 1.4 删除线 del

**现状**：缺失，`~~文本~~` 无删除线。

```css
.post-content del {
  color: var(--muted);
  text-decoration: line-through;
}
```

颜色用 muted 让删除内容视觉弱化。

### 1.5 暗黑模式适配

上述所有元素在 `html.dark` 下的色彩调整：
- h2/h3 短下划线装饰：accent 在暗黑模式自动为新值（`#2fdc7b`），无需额外规则
- hr 圆点：`var(--line)` 暗黑自动跟随
- del：`var(--muted)` 暗黑自动跟随
- marker：`var(--accent)` 暗黑自动跟随

**结论**：因全部使用 CSS 变量，暗黑模式无需额外规则，自动适配。

---

## 第 2 块 · TOC 目录 + 标题锚点

### 2.1 机制

文章页加载后，JS 扫描 `.post-content` 内 h2-h3，做三件事：

1. **给每个标题生成 slug 并设为 `id`**（中文标题做 URL 安全转写）
2. **生成 TOC 目录**（视口右侧 fixed 定位，列出 h2-h3）
3. **滚动高亮当前章节**（IntersectionObserver，复用入场动画同一套机制）

### 2.2 TOC 布局：视口外侧固定

**决策**：TOC 用 `position: fixed` 贴在视口右侧，位于 `.site-wrap`（940px 居中）之外。正文宽度零侵入。

**显示阈值**：视口宽度 ≥ 1220px 时显示（940px 正文 + 200px TOC + 80px 间距）。低于 1220px 的窗口（平板/小笔记本）TOC 隐藏，文章照常阅读。

```css
/* TOC 容器：视口右侧固定 */
.post-toc {
  position: fixed;
  top: 50%;
  right: 32px;
  transform: translateY(-50%);
  width: 200px;
  max-height: 60vh;
  overflow-y: auto;
  /* 仅大屏显示 */
  display: none;
}
@media (min-width: 1220px) {
  .post-toc.has-items {
    display: block;
  }
}
```

### 2.3 TOC 视觉设计

```css
.post-toc {
  padding: 16px 20px;
  background: var(--paper);
  border: 1px solid var(--line);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow);
  font-size: 13px;
  line-height: 1.7;
}
.post-toc-title {
  font-size: 11px;
  letter-spacing: 0.15em;
  text-transform: uppercase;
  color: var(--muted);
  font-weight: 600;
  margin-bottom: 10px;
}
.post-toc-list {
  list-style: none;
  margin: 0;
  padding: 0;
}
.post-toc-list li {
  margin: 0;
}
.post-toc-list a {
  display: block;
  padding: 3px 0 3px 12px;
  color: var(--muted);
  text-decoration: none;
  border-left: 2px solid transparent;
  transition: color var(--t-quick) var(--paper-soft),
              border-color var(--t-quick) var(--paper-soft);
}
/* h3 缩进 */
.post-toc-list .toc-level-3 a {
  padding-left: 24px;
  font-size: 12px;
}
/* 悬停 */
.post-toc-list a:hover {
  color: var(--ink);
  border-left-color: var(--line);
}
/* 当前章节高亮 */
.post-toc-list a.toc-active {
  color: var(--accent);
  font-weight: 600;
  border-left-color: var(--accent);
}
```

### 2.4 JS 逻辑（footer.php，纯原生）

```js
// TOC 目录 + 标题锚点 + 滚动高亮
(function () {
  var content = document.querySelector('article .post-content');
  if (!content) return;

  var headings = content.querySelectorAll('h2, h3');
  if (headings.length < 2) return;  // 少于 2 个标题不生成 TOC

  // 1. 给标题生成 slug + id
  var slugSet = {};
  headings.forEach(function (h) {
    var slug = makeSlug(h.textContent);
    // 去重：重复 slug 加序号
    if (slugSet[slug]) { slug = slug + '-' + (++slugSet[slug]); }
    else { slugSet[slug] = 1; }
    h.id = slug;
  });

  // 2. 构建 TOC DOM
  var toc = document.createElement('nav');
  toc.className = 'post-toc has-items';
  toc.setAttribute('aria-label', '文章目录');
  var html = '<p class="post-toc-title">目录</p><ul class="post-toc-list">';
  headings.forEach(function (h) {
    var level = h.tagName.toLowerCase();  // h2 / h3
    html += '<li class="toc-level-' + level + '">' +
            '<a href="#' + h.id + '" data-toc-target="' + h.id + '">' +
            escapeHtml(h.textContent.trim()) + '</a></li>';
  });
  html += '</ul>';
  toc.innerHTML = html;
  document.body.appendChild(toc);

  // 3. 滚动高亮（IntersectionObserver，复用入场动画机制）
  if ('IntersectionObserver' in window) {
    var links = toc.querySelectorAll('a');
    var linkByHref = {};
    links.forEach(function (a) { linkByHref[a.getAttribute('data-toc-target')] = a; });

    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        links.forEach(function (a) { a.classList.remove('toc-active'); });
        var active = linkByHref[entry.target.id];
        if (active) active.classList.add('toc-active');
      });
    }, { rootMargin: '-20% 0px -70% 0px' });  // 标题进入视口上 1/4 区域时高亮

    headings.forEach(function (h) { io.observe(h); });
  }

  // 4. 平滑滚动（点击 TOC 链接）
  toc.addEventListener('click', function (e) {
    if (e.target.tagName !== 'A') return;
    e.preventDefault();
    var target = document.getElementById(e.target.getAttribute('data-toc-target'));
    if (target) {
      target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      history.replaceState(null, '', '#' + target.id);
    }
  });

  // --- 工具函数 ---
  function makeSlug(text) {
    // 中文标题：URL 安全转写。中文保留，空格转连字符，去除标点
    return (text || '').trim()
      .toLowerCase()
      .replace(/[^\w\u4e00-\u9fa5\s-]/g, '')  // 保留中英文、数字、空格、连字符
      .replace(/[\s_]+/g, '-')                  // 空格/下划线转连字符
      .replace(/-+/g, '-')                      // 合并连续连字符
      .replace(/^-|-$/g, '');                   // 去首尾连字符
  }
  function escapeHtml(s) {
    return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
            .replace(/"/g,'&quot;').replace(/'/g,'&#39;');
  }
})();
```

### 2.5 关键设计决策

- **仅 h2-h3 进 TOC**：大多数长文两层足够，目录简洁不臃肿
- **少于 2 个标题不生成 TOC**：避免短文章出现只有一个条目的尴尬目录
- **中文 slug**：保留中文字符（URL 编码由浏览器处理），去除标点，可读性好
- **slug 去重**：相同标题自动加序号（`标题` → `标题`、`标题-2`）
- **滚动高亮 rootMargin `-20% 0 -70% 0`**：标题进入视口上方 1/4 区域时判定为"当前章节"，符合阅读视线焦点
- **平滑滚动 + replaceState**：点击跳转用 `scrollIntoView({behavior:'smooth'})`，并更新 URL hash（支持复制章节链接），不触发跳转

### 2.6 降级

- 视口 < 1220px：TOC 不显示（`display:none`），标题 id 仍存在（URL hash 跳转仍可用）
- 不支持 IntersectionObserver：TOC 正常显示但不滚动高亮
- `prefers-reduced-motion: reduce`：平滑滚动降级为瞬时（由现有全局规则覆盖），TOC 出现无过渡

---

## 实施约束

### 不改动清单（硬约束）

- `post.php` / `index.php` / `header.php` / `page.php` / `archive.php` / `comments.php` / `404.php`（PHP 模板结构）
- `Printerllm/` 插件、`inc/` 子系统、`functions.php`、`LLM.php`
- 现有 `.post-content` 已有样式（a/blockquote/pre/code/table/img/p）——本次只**新增**缺失元素的样式，不覆盖已有的

### 改动文件清单

| 文件 | 改动内容 |
|---|---|
| `css/style.css` | 第 1 块渲染样式（h2-h6/ul/ol/hr/del）+ 第 2 块 TOC 样式 |
| `footer.php` | 第 2 块 TOC 的 JS 逻辑（标题 id 生成、TOC DOM 构建、滚动高亮、平滑滚动）|

### 性能

- TOC JS 只在文章页（存在 `.post-content`）执行，列表页/首页不触发
- IntersectionObserver 只 observe h2-h3（通常 3-10 个），开销极低
- 所有过渡用 `transform`/`opacity`/`color`/`border-color`（GPU 友好）

### 无障碍

- TOC `<nav>` 带 `aria-label="文章目录"`
- TOC 链接有清晰悬停/高亮态（不依赖颜色，有 border-left 提示）
- 标题 id 支持键盘和读屏器跳转
- 焦点可见性（上一轮修复的 outline 方案）对 TOC 链接同样生效

### 回归风险

- TOC 注入 `document.body`，不影响 `.paper` 内部结构
- 标题加 id 不破坏现有样式（id 不影响 CSS）
- 新增 CSS 规则用 `.post-content` 前缀，不影响其它区域
