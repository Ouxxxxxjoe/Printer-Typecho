# Printer 主题动效优化设计：纸张轻盈感

**日期**：2026-07-12
**状态**：已确认，待实施
**范围**：全面档（纯原生，零依赖）
**风格方向**：纸张轻盈感——打印机最动人的瞬间不是机器轰鸣，而是纸吐出后轻轻落定那一秒

---

## 背景与问题

当前主题（Printer）的动效体系存在「生硬感」，根因经代码审计确认如下：

1. **入场动画硬绑定**：`.post-item:nth-child(1..10)` 写死 10 条等差延迟规则（`css/style.css:139-148`），用 `animation` 而非 `transition`，只在首次加载播一次，向下滚动到第 11 篇文章无动效。
2. **页面跳转硬切**：虽有 `@view-transition { navigation: auto }`（`css/style.css:2930`），但仅配置 0.26s 交叉淡入，无方向感。
3. **拟物按钮反馈发「弹」**：`:active` 态 `translateY(3px)` + `150ms ease-enter`，感觉像弹了一下而非柔和陷下。
4. **缓动曲线无性格**：仅 3 条 Material 标准曲线（`--ease-smooth/enter/exit`），安全但与复古打印主题气质不符。
5. **阅读进度条无平滑**：`footer.php:161` 直接 `bar.style.width = progress + '%'`，滚动时一卡一卡地跳。
6. **日夜切换无仪式感**：`footer.php:119-128` 仅做全局 250ms 颜色插值，像调亮度滑块。

## 设计原则

整个动效语言统一到「纸」上，由四条原则贯穿：

1. **轻** — 位移永远克制。入场位移统一压到 8–12px（当前 20–30px），缩放 0.985–0.99。
2. **慢释** — 用 ease-out-expo / ease-out-quint 做入场。纸飘到最后那一刻几乎静止地吻上桌面。
3. **错落** — 延迟用「前密后疏」（0/60/130/210/300ms...），而非等差列队。像真实叠纸节奏。
4. **介质感** — 页面切换是「纸的交接」，不是数据 swap。旧纸下滑收走、新纸从上落入。

## 硬约束（贯穿全程）

> **本次动效优化只改元素如何动起来**（缓动曲线、时长、位移幅度、延迟节奏、触发时机），**绝不改动任何排版结构**。
>
> 文章列表保持竖向单列、文章页保持现有版式、卡片/导航/侧栏布局全部原样不动。`.post-list` / `.post-item` / `.post-content` 等所有布局相关 CSS 属性（`display` / `grid` / `flex` / `width` / `padding` / `margin`）一行不碰。

只允许改动效相关属性：`transition` / `animation` / `transform` / `opacity` / `filter` / `timing-function` / `duration` / `delay`。

---

## 第 1 节 · 动效语言基础层

### 1.1 纸张感缓动系统（`css/style.css` `:root`）

新增 4 条纸张感曲线变量：

```css
/* 纸张感缓动系统 */
--paper-in:       cubic-bezier(0.16, 1, 0.3, 1);    /* ease-out-expo：纸落定 */
--paper-out:      cubic-bezier(0.7, 0, 0.84, 0);     /* ease-in-expo：纸被收走 */
--paper-soft:     cubic-bezier(0.32, 0.72, 0, 1);    /* 安静通用，按钮/控件 */
--paper-overshoot: cubic-bezier(0.34, 1.56, 0.64, 1); /* 轻微回弹，仅指示灯等小元素 */
```

### 1.2 现有曲线值替换（向后兼容）

**直接替换** `--ease-smooth` / `--ease-enter` / `--ease-exit` 的曲线值（保留变量名），使现有引用这些变量的规则（如 `theme-animating` 全局过渡）自动获得纸张感：

```css
--ease-smooth: cubic-bezier(0.32, 0.72, 0, 1);   /* 原 cubic-bezier(0.4,0,0.2,1) */
--ease-enter:  cubic-bezier(0.16, 1, 0.3, 1);    /* 原 cubic-bezier(0,0,0.2,1) */
--ease-exit:   cubic-bezier(0.7, 0, 0.84, 0);     /* 原 cubic-bezier(0.4,0,1,1) */
```

### 1.3 统一时长尺度

新增语义化时长变量，替代当前散乱的 150/180/200/250/300/500/600ms 混用：

```css
--t-tap:     180ms;   /* 按钮按压、点击反馈 */
--t-quick:   280ms;   /* 悬停、搜索框展开 */
--t-paper:   520ms;   /* 纸张入场（列表/标题/卡片） */
--t-page:    480ms;   /* 页面切换（View Transition） */
--t-ambient: 2.4s;    /* 指示灯呼吸等环境动效 */
```

---

## 第 2 节 · 入场动画系统

### 2.1 问题

`css/style.css:139-160` 用 `animation: fadeInUp/slideInLeft/fadeIn` 配 10 条 `nth-child` 等差延迟，硬绑定、只播一次、位移过大（20px）、方向不统一（slideInLeft 从左滑入）。

### 2.2 方案：IntersectionObserver + 纸张叠落

**机制**：每个内容元素默认 `opacity:0` + `translateY(10px)`；进入视口时 JS 加 `.in-view` 类触发 transition。

**CSS**：

```css
/* 默认隐藏态：纸还没落下来 */
.post-item,
.paper-title,
.paper-subtitle,
.paper-meta {
  opacity: 0;
  transform: translateY(10px);
  transition: opacity var(--t-paper) var(--paper-in),
              transform var(--t-paper) var(--paper-in);
}

/* 进入视口：纸落定 */
.post-item.in-view,
.paper-title.in-view,
.paper-subtitle.in-view,
.paper-meta.in-view {
  opacity: 1;
  transform: translateY(0);
}
```

**参数**：
- 位移 10px（从 20px 减半）
- 时长 `--t-paper` = 520ms（给 ease-out-expo 长尾巴留时间）
- 曲线 `--paper-in`（ease-out-expo）

**错落延迟（前密后疏）**：由 JS 在进入视口时动态赋予 `transition-delay`。延迟按**元素在其父级中的 DOM 位置**计算（而非 IntersectionObserver 回调里的 entries 序号，因为同批回调内 entries 顺序不稳定），取前密后疏序列 `[0, 60, 130, 210, 300, 400, 510, 630]ms` 中对应索引。

**JS（`footer.php`，纯原生）**：

```js
(function(){
  var items = document.querySelectorAll('.post-item, .paper-title, .paper-subtitle, .paper-meta');
  if (!('IntersectionObserver' in window) || !items.length) {
    items.forEach(function(el){ el.classList.add('in-view'); });
    return;
  }
  var delays = [0, 60, 130, 210, 300, 400, 510, 630];
  // 预计算每个元素在其父级 children 中的索引（DOM 顺序，稳定）
  var indexByEl = new WeakMap();
  items.forEach(function(el){
    if (!el.parentElement) return;
    var siblings = Array.prototype.indexOf.call(el.parentElement.children, el);
    indexByEl.set(el, siblings);
  });
  var io = new IntersectionObserver(function(entries){
    entries.forEach(function(entry){
      if (!entry.isIntersecting) return;
      var el = entry.target;
      var idx = indexByEl.get(el) || 0;
      el.style.transitionDelay = delays[Math.min(idx, delays.length - 1)] + 'ms';
      el.classList.add('in-view');
      io.unobserve(el);
    });
  }, { rootMargin: '0px 0px -8% 0px', threshold: 0.05 });
  items.forEach(function(el){ io.observe(el); });
})();
```

### 2.3 覆盖范围

| 元素 | 当前 | 新方案 |
|---|---|---|
| `.post-item`（文章列表项） | 10 条 nth-child animation | IO 触发纸张叠落 |
| `.paper-title`（归档页标题） | slideInLeft 0.6s（从左滑入） | IO 触发，**从下方 10px 飘入**（统一方向） |
| `.paper-subtitle`（共 N 篇） | fadeIn 0.5s delay 0.2s | IO 触发 |
| `.paper-meta`（活动/分类区） | fadeIn 0.5s delay 0.3s | IO 触发 |

### 2.4 删除项

- `css/style.css:139-160` 所有 `animation: fadeInUp/slideInLeft/fadeIn` 的 nth-child 规则
- `@keyframes slideInLeft`（`css/style.css:127-136`）——纸张感要求统一从下方飘入，删除从左滑入
- `@keyframes fadeInUp` 保留（其它地方可能复用），但入场不再用它

### 2.5 降级

- 不支持 IntersectionObserver → 直接加 `.in-view`，内容不丢
- `prefers-reduced-motion: reduce` → 现有全局规则（`css/style.css:2769`）把 transition 压到 0.01ms，纸张直接出现

---

## 第 3 节 · 页面切换（纸的交接 View Transition）

### 3.1 方案

替换当前 0.26s 交叉淡入，改为方向性纸面交接：

```css
@media (prefers-reduced-motion: no-preference) {
  ::view-transition-old(root) {
    animation: paperExit var(--t-page) var(--paper-out) both;
  }
  ::view-transition-new(root) {
    animation: paperEnter calc(var(--t-page) + 40ms) var(--paper-in) both;
  }

  @keyframes paperExit {
    0%   { opacity: 1; transform: translateY(0); filter: blur(0); }
    100% { opacity: 0; transform: translateY(24px); filter: blur(2px); }
  }
  @keyframes paperEnter {
    0%   { opacity: 0; transform: translateY(-14px); filter: blur(1px); }
    100% { opacity: 1; transform: translateY(0); filter: blur(0); }
  }
}
```

### 3.2 参数设计

- **旧纸下滑 24px + blur(2px)**：向下抽走 + 失焦，像被收走
- **新纸从 -14px 落入 + blur 清晰化**：从上方送上来
- **方向相反**：垂直交接轴，呼应打印机出纸方向
- **paper-out 用于旧纸**（ease-in-expo，被抽走）；**paper-in 用于新纸**（ease-out-expo，落定）
- **深入/返回统一方向**（纯 CSS，无需 JS 判断导航方向）

### 3.3 兼容性

- Chrome/Edge 126+、Android Chrome 支持 `@view-transition { navigation: auto }`
- Safari/Firefox 降级为普通跳转（无过渡，不报错）
- 阅读进度条跳转、返回顶部已用 `scrollTo`，不触发 VT，不受影响

---

## 第 4 节 · 控件反馈

### 4.1 拟物按钮按压（`.theme-toggle` / `.header-search-btn` / `.social-link`）

```css
.theme-toggle,
.header-search-btn,
.social-link {
  transition: transform var(--t-tap) var(--paper-soft),
              box-shadow var(--t-tap) var(--paper-soft);
}
.theme-toggle:active,
.header-search-btn:active,
.social-link:active {
  transform: translateY(4px);  /* 从 3px 增到 4px */
}
```

- 时长 150ms → 180ms（`--t-tap`）
- 曲线 ease-enter → paper-soft
- 位移 3px → 4px
- **不加内圈键帽缩放**（那是机械感做法，纸张感下按钮整块轻陷）
- **不碰 border-radius**（上一轮已修复圆形按钮问题，此处只改 timing）

### 4.2 返回顶部按钮（`.back-to-top`）

```css
.back-to-top {
  transition: opacity 0.32s var(--paper-soft),
              visibility 0.32s var(--paper-soft),
              transform 0.32s var(--paper-soft),
              box-shadow var(--t-tap) var(--paper-soft);
}
.back-to-top:not(.visible) {
  transform: translateY(14px);  /* 从 20px 收到 14px */
}
```

### 4.3 搜索框展开（`.header-search input`）

```css
.header-search input[type="search"] {
  transition: width 0.32s var(--paper-in),
              padding 0.32s var(--paper-in),
              opacity 0.24s var(--paper-soft);
}
```

- 时长 250ms → 320ms
- 曲线 ease-enter → paper-in

### 4.4 阅读进度条平滑插值（`#reading-progress`）

**问题**：`footer.php:161` 直接赋值 width，无平滑。

**方案**：`requestAnimationFrame` + lerp 0.18，进度条比实际滚动慢半拍追上：

```js
(function(){
  var bar = document.getElementById('reading-progress');
  if (!bar) return;
  var barWrap = document.getElementById('reading-progress-wrap');
  if (barWrap) barWrap.style.display = '';
  var target = 0, current = 0, running = false;
  function update() {
    var total = document.documentElement.scrollHeight - window.innerHeight;
    if (total <= 0) { bar.style.display = 'none'; return; }
    if (bar.style.display === 'none') bar.style.display = '';
    target = Math.min(100, Math.max(0, (window.scrollY / total) * 100));
    if (!running) { running = true; requestAnimationFrame(loop); }
  }
  function loop() {
    current += (target - current) * 0.18;
    if (Math.abs(target - current) < 0.1) {
      current = target;
      bar.style.width = current + '%';
      running = false;
      return;
    }
    bar.style.width = current + '%';
    requestAnimationFrame(loop);
  }
  window.addEventListener('scroll', update, { passive: true });
  update();
})();
```

**lerp 系数 0.18**：让进度条比实际滚动慢半拍，快速滚动有惯性追上，停下缓缓到位。太小（0.05）像延迟，太大（0.5）无平滑感。0.18 是手感甜区。

**降级**：`prefers-reduced-motion: reduce` 时不启用 lerp，直接赋值。

---

## 第 5 节 · 环境动效

### 5.1 日夜切换（仅精调现有淡变）

**决策**：不做圆形扩散等炫技仪式，只精调现有全局淡变的曲线和时长。

**改动点**（两处需同步）：

1. `css/style.css:67-98` 的 `html.theme-animating *` 全局过渡：
   - 曲线随第 1 节 `--ease-smooth` 值替换自动升级为纸张感（无需额外改）
   - 时长从硬编码 `250ms` 改为 `320ms`（让淡变更「稳」）
2. `footer.php:126-129` 的 setTimeout 延迟：从 `300`（250ms + 50ms 缓冲）同步改为 `370`（320ms + 50ms 缓冲），确保 JS 在过渡完成后才移除 `.theme-animating` 类

**不改动**：`theme-animating` 的类切换逻辑、localStorage 存储、aria 状态同步。

### 5.2 指示灯呼吸优化（`.power-dot`）

```css
@keyframes power-pulse {
  0%, 100% { opacity: 0.55; box-shadow: 0 0 8px rgba(134, 188, 142, 0.4); }
  50%      { opacity: 1;    box-shadow: 0 0 14px rgba(134, 188, 142, 0.7); }
}
@keyframes power-pulse-dark {
  0%, 100% { opacity: 0.55; box-shadow: 0 0 8px rgba(255, 77, 79, 0.4); }
  50%      { opacity: 1;    box-shadow: 0 0 14px rgba(255, 77, 79, 0.75); }
}
.power-dot { animation: power-pulse var(--t-ambient) ease-in-out infinite; }
```

- 保留 opacity 呼吸，增加轻微 box-shadow 光晕呼吸（光感更强）
- 时长 2.4s 不变
- 暗黑模式同步优化（红色指示灯）

### 5.3 idle 微飘：砍掉

文章卡片静止时保持静止，不做任何持续浮动。纸落定后是静止的，持续漂浮违背纸张物理且干扰阅读。只有指示灯保留呼吸（有「机器在工作」的语义）。

---

## 实施约束

### 性能

- 所有动效只用 `transform` / `opacity` / `filter`（GPU 加速属性）
- `will-change` 只在动效期间由 JS 动态添加，结束即移除，避免常驻 GPU 占用
- `filter: blur` 仅在页面切换（480ms）和指示灯（极小元素）使用，不在大面积元素常驻
- 所有 scroll 监听用 `{ passive: true }`

### 无障碍

- 所有动效包裹在 `@media (prefers-reduced-motion: no-preference)` 或由现有全局 reduce 规则覆盖
- 焦点可见性（上一轮修复的 outline 方案）保持不动

### 回归风险

- 不动任何布局属性（display/grid/flex/width/padding/margin）
- 不动 `border-radius`（上一轮已修复圆形按钮）
- 不动 PHP 模板结构（`index.php` / `post.php` / `header.php`）
- 只改 `css/style.css` 和 `footer.php`（JS 内联脚本）两个文件

---

## 改动文件清单

| 文件 | 改动内容 |
|---|---|
| `css/style.css` | 第 1-5 节全部 CSS：缓动变量、入场动画、View Transition、控件反馈、指示灯 |
| `footer.php` | 第 2 节 IntersectionObserver JS、第 4.4 节进度条 lerp JS |

## 不改动清单（硬约束）

- `index.php` / `post.php` / `page.php` / `header.php` / `archive.php` / `comments.php`（模板结构）
- 任何 `.post-list` / `.post-item` / `.post-content` 的布局属性
- `border-radius`（圆形按钮已修复）
- `Printerllm/` 插件
- `inc/` 子系统
